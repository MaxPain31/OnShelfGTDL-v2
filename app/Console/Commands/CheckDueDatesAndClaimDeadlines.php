<?php

namespace App\Console\Commands;

use App\Mail\DueDateApproachingMail;
use App\Models\BookBorrow;
use App\Models\BookReservation;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckDueDatesAndClaimDeadlines extends Command
{
    protected $signature = 'notifications:check-due-dates';
    protected $description = 'Check for approaching due dates and claim deadlines, and create notifications.';

    public function handle()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        
        // Check for books due tomorrow (due date approaching)
        $borrowsDueTomorrow = BookBorrow::where('status', 'borrowed')
            ->whereDate('due_date', $tomorrow)
            ->with(['book', 'user'])
            ->get();
        
        $dueDateNotifications = 0;
        $adminDueDateNotifications = 0;
        
        // Get admin role and admins
        $adminRole = Role::where('name', 'Administrator')->first();
        $admins = $adminRole ? User::where('role_id', $adminRole->id)->get() : collect();
        
        foreach ($borrowsDueTomorrow as $borrow) {
            // Get borrower name and type
            $borrowerName = $borrow->user->userInfo->full_name ?? $borrow->user->name ?? $borrow->user->email;
            $borrowerType = $borrow->user->isStudent() ? 'Student' : ($borrow->user->isTeacher() ? 'Teacher' : 'User');
            
            // Check if notification already exists for this borrow (user)
            $existingNotification = Notification::where('user_id', $borrow->user_id)
                ->where('type', 'due_date_approaching')
                ->where('related_id', $borrow->id)
                ->where('related_type', BookBorrow::class)
                ->whereDate('created_at', $today)
                ->exists();
            
            if (!$existingNotification) {
                Notification::create([
                    'user_id' => $borrow->user_id,
                    'type' => 'due_date_approaching',
                    'title' => 'Book Due Date Approaching',
                    'message' => "Your borrowed book \"{$borrow->book->book_name}\" is due tomorrow ({$tomorrow->format('M d, Y')}). Please return it on time.",
                    'related_id' => $borrow->id,
                    'related_type' => BookBorrow::class,
                    'data' => [
                        'book_name' => $borrow->book->book_name,
                        'book_id' => $borrow->book->id,
                        'due_date' => $borrow->due_date->format('Y-m-d'),
                        'due_date_formatted' => $borrow->due_date->format('M d, Y'),
                    ],
                ]);

                // Send email notification
                $borrowerName = $borrow->user->userInfo->full_name ?? $borrow->user->name ?? $borrow->user->email;
                $bookImage = $borrow->book->image_path ? config('app.url') . '/storage/' . $borrow->book->image_path : null;
                Mail::to($borrow->user->email)->send(new DueDateApproachingMail(
                    $borrowerName,
                    $borrow->book->book_name,
                    $borrow->due_date->format('M d, Y'),
                    $bookImage
                ));

                $dueDateNotifications++;
            }
            
            // Notify all admins about approaching due dates
            foreach ($admins as $admin) {
                $existingAdminNotification = Notification::where('user_id', $admin->id)
                    ->where('type', 'admin_due_date_approaching')
                    ->where('related_id', $borrow->id)
                    ->where('related_type', BookBorrow::class)
                    ->whereDate('created_at', $today)
                    ->exists();
                
                if (!$existingAdminNotification) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'admin_due_date_approaching',
                        'title' => 'Book Due Date Approaching',
                        'message' => "{$borrowerName} ({$borrowerType}) has a book \"{$borrow->book->book_name}\" due tomorrow ({$tomorrow->format('M d, Y')}).",
                        'related_id' => $borrow->id,
                        'related_type' => BookBorrow::class,
                        'data' => [
                            'book_name' => $borrow->book->book_name,
                            'book_id' => $borrow->book->id,
                            'borrower_name' => $borrowerName,
                            'borrower_type' => $borrowerType,
                            'due_date' => $borrow->due_date->format('Y-m-d'),
                            'due_date_formatted' => $borrow->due_date->format('M d, Y'),
                        ],
                    ]);
                    $adminDueDateNotifications++;
                }
            }
        }
        
        // Check for reservations that need to be claimed soon (1 day before claim deadline)
        $reservationsToClaim = BookReservation::where('status', 'pending')
            ->whereDate('claim_deadline', $tomorrow)
            ->with(['book', 'user'])
            ->get();
        
        $claimDeadlineNotifications = 0;
        foreach ($reservationsToClaim as $reservation) {
            // Check if notification already exists for this reservation
            $existingNotification = Notification::where('user_id', $reservation->user_id)
                ->where('type', 'claim_deadline_approaching')
                ->where('related_id', $reservation->id)
                ->where('related_type', BookReservation::class)
                ->whereDate('created_at', $today)
                ->exists();
            
            if (!$existingNotification) {
                Notification::create([
                    'user_id' => $reservation->user_id,
                    'type' => 'claim_deadline_approaching',
                    'title' => 'Reservation Claim Deadline Approaching',
                    'message' => "Your reserved book \"{$reservation->book->book_name}\" must be claimed by tomorrow ({$tomorrow->format('M d, Y')}). Please claim it before the deadline.",
                    'related_id' => $reservation->id,
                    'related_type' => BookReservation::class,
                    'data' => [
                        'book_name' => $reservation->book->book_name,
                        'book_id' => $reservation->book->id,
                        'claim_deadline' => $reservation->claim_deadline->format('Y-m-d'),
                        'claim_deadline_formatted' => $reservation->claim_deadline->format('M d, Y'),
                    ],
                ]);
                $claimDeadlineNotifications++;
            }
        }
        
        $this->info("Created {$dueDateNotifications} due date approaching notifications for users.");
        $this->info("Created {$adminDueDateNotifications} due date approaching notifications for admins.");
        $this->info("Created {$claimDeadlineNotifications} claim deadline approaching notifications.");
        
        return 0;
    }
}
