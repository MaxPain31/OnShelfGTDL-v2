<?php

namespace App\Console\Commands;

use App\Models\BookReservation;
use Illuminate\Console\Command;

class VoidExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:void-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Void all unclaimed reservations that have passed their claim deadline (3 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCount = BookReservation::where('status', 'pending')
            ->where('claim_deadline', '<', now()->startOfDay())
            ->update(['status' => 'voided']);

        if ($expiredCount > 0) {
            $this->info("Successfully voided {$expiredCount} expired reservation(s).");
        } else {
            $this->info('No expired reservations to void.');
        }

        return Command::SUCCESS;
    }
}
