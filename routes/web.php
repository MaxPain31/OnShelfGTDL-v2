<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Administrator\AdminPanelController;
use App\Http\Controllers\Administrator\AdminProfileController;
use App\Http\Controllers\Administrator\BorrowManagementController;
use App\Http\Controllers\Administrator\ReportsController;
use App\Http\Controllers\Administrator\ReservationManagementController;
use App\Http\Controllers\Administrator\RulesManagementController;
use App\Http\Controllers\Administrator\TeacherManagementController;
use App\Http\Controllers\Administrator\BookManagementController;
use App\Http\Controllers\Administrator\EbookManagementController;
use App\Http\Controllers\Administrator\AttendanceController;
use App\Http\Controllers\Student\StudentBorrowedBooksController;
use App\Http\Controllers\Student\StudentBooksController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentEbooksController;
use App\Http\Controllers\Student\StudentMyShelfController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\StudentReservedBooksController;
use App\Http\Controllers\Student\StudentRulesController;
use App\Http\Controllers\Teacher\StudentManagementController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use App\Http\Controllers\Teacher\TeacherBooksController;
use App\Http\Controllers\Teacher\TeacherMyShelfController;
use App\Http\Controllers\Teacher\TeacherEbooksController;
use App\Http\Controllers\Teacher\TeacherBorrowedBooksController;
use App\Http\Controllers\Teacher\TeacherReservedBooksController;
use App\Http\Controllers\Teacher\TeacherRulesController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StudentAuthController::class, 'home'])->name('home');
Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('login');
Route::view('/register', 'authentication.register')->name('register');
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');

// Password Reset Routes
Route::get('/forgot-password', [StudentAuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [StudentAuthController::class, 'sendPasswordResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [StudentAuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [StudentAuthController::class, 'resetPassword'])->name('password.update');


Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'showAll'])->name('notifications');
    Route::get('/dashboard', [AdminPanelController::class, 'dashboard'])->name('dashboard');
    Route::get('/manage-students', [AdminPanelController::class, 'manageStudents'])->name('manage-students');
    Route::get('/manage-students/export/{format}', [AdminPanelController::class, 'exportStudents'])->name('manage-students.export');
    Route::get('/manage-books', [BookManagementController::class, 'index'])->name('manage-books');
    Route::post('/manage-books', [BookManagementController::class, 'store'])->name('manage-books.store');
    Route::post('/manage-books/{book}', [BookManagementController::class, 'update'])->name('manage-books.update');
    Route::delete('/manage-books/{book}', [BookManagementController::class, 'destroy'])->name('manage-books.destroy');
    Route::get('/manage-books/export/{format}', [BookManagementController::class, 'export'])->name('manage-books.export');

    Route::get('/manage-ebooks', [EbookManagementController::class, 'index'])->name('manage-ebooks');
    Route::post('/manage-ebooks', [EbookManagementController::class, 'store'])->name('manage-ebooks.store');
    Route::post('/manage-ebooks/{ebook}', [EbookManagementController::class, 'update'])->name('manage-ebooks.update');
    Route::delete('/manage-ebooks/{ebook}', [EbookManagementController::class, 'destroy'])->name('manage-ebooks.destroy');
    Route::get('/manage-ebooks/export/{format}', [EbookManagementController::class, 'export'])->name('manage-ebooks.export');

    Route::get('/manage-borrows', [BorrowManagementController::class, 'index'])->name('manage-borrows');
    Route::post('/manage-borrows/{borrow}/return', [BorrowManagementController::class, 'return'])->name('manage-borrows.return');
    Route::get('/manage-reservations', [ReservationManagementController::class, 'index'])->name('manage-reservations');
    Route::post('/manage-reservations/{reservation}/verify', [ReservationManagementController::class, 'verify'])->name('manage-reservations.verify');
    Route::post('/manage-reservations/{reservation}/void', [ReservationManagementController::class, 'void'])->name('manage-reservations.void');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/export/{type}/{format}', [ReportsController::class, 'export'])->name('reports.export');

    Route::get('/manage-attendance', [AttendanceController::class, 'index'])->name('manage-attendance');
    Route::post('/manage-attendance', [AttendanceController::class, 'store'])->name('manage-attendance.store');
    Route::put('/manage-attendance/{attendance}', [AttendanceController::class, 'update'])->name('manage-attendance.update');
    Route::delete('/manage-attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('manage-attendance.destroy');
    Route::get('/manage-attendance/export/{format}', [AttendanceController::class, 'export'])->name('manage-attendance.export');

    Route::get('/manage-rules', [RulesManagementController::class, 'index'])->name('manage-rules');
    Route::post('/manage-rules', [RulesManagementController::class, 'store'])->name('manage-rules.store');
    Route::put('/manage-rules/{rule}', [RulesManagementController::class, 'update'])->name('manage-rules.update');
    Route::delete('/manage-rules/{rule}', [RulesManagementController::class, 'destroy'])->name('manage-rules.destroy');
    Route::post('/manage-rules/{rule}/toggle-status', [RulesManagementController::class, 'toggleStatus'])->name('manage-rules.toggle-status');

    Route::controller(TeacherManagementController::class)->group(function () {
        Route::get('/manage-teachers', 'index')->name('manage-teachers');
        Route::post('/manage-teachers', 'store')->name('manage-teachers.store');
        Route::get('/manage-teachers/{teacher}/edit', 'edit')->name('manage-teachers.edit');
        Route::put('/manage-teachers/{teacher}', 'update')->name('manage-teachers.update');
        Route::delete('/manage-teachers/{teacher}', 'destroy')->name('manage-teachers.destroy');
        Route::patch('/manage-teachers/{teacher}/status', 'toggleStatus')->name('manage-teachers.status');
        Route::get('/manage-teachers/export/{format}', 'export')->name('manage-teachers.export');
    });

    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
    Route::post('/profile/change-password', [AdminProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::get('/profile/setup', [AdminAuthController::class, 'showProfileSetup'])->name('profile.setup');
    Route::post('/profile/setup', [AdminAuthController::class, 'saveProfileSetup'])->name('profile.setup.save');
});

Route::middleware('auth')->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'showAll'])->name('notifications');
    Route::get('/dashboard', [TeacherDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/my-shelf', [TeacherMyShelfController::class, 'index'])->name('my-shelf');
    Route::get('/books', [TeacherBooksController::class, 'index'])->name('books');
    Route::get('/books/{book}', [TeacherBooksController::class, 'show'])->name('books.show');
    Route::post('/books/{book}/favorite', [TeacherBooksController::class, 'toggleFavorite'])->name('books.favorite');
    Route::post('/books/{book}/borrow', [TeacherBooksController::class, 'borrow'])->name('books.borrow');
    Route::post('/books/{book}/reserve', [TeacherBooksController::class, 'reserve'])->name('books.reserve');
    Route::get('/ebooks', [TeacherEbooksController::class, 'index'])->name('ebooks');
    Route::get('/ebooks/{ebook}', [TeacherEbooksController::class, 'show'])->name('ebooks.show');
    Route::get('/ebooks/{ebook}/read', [TeacherEbooksController::class, 'read'])->name('ebooks.read');
    Route::get('/ebooks/{ebook}/file', [TeacherEbooksController::class, 'file'])->name('ebooks.file');
    Route::post('/ebooks/{ebook}/favorite', [TeacherEbooksController::class, 'toggleFavorite'])->name('ebooks.favorite');
    Route::get('/borrowed-books', [TeacherBorrowedBooksController::class, 'index'])->name('borrowed-books');
    Route::post('/borrowed-books/{borrow}/return', [TeacherBorrowedBooksController::class, 'return'])->name('borrowed-books.return');
    Route::get('/reserved-books', [TeacherReservedBooksController::class, 'index'])->name('reserved-books');
    Route::get('/rules', [TeacherRulesController::class, 'index'])->name('rules');

    Route::controller(StudentManagementController::class)->group(function () {
        Route::get('/manage-students', 'index')->name('manage-students');
        Route::post('/manage-students', 'store')->name('manage-students.store');
        Route::get('/manage-students/{student}/edit', 'edit')->name('manage-students.edit');
        Route::put('/manage-students/{student}', 'update')->name('manage-students.update');
        Route::delete('/manage-students/{student}', 'destroy')->name('manage-students.destroy');
        Route::patch('/manage-students/{student}/status', 'toggleStatus')->name('manage-students.status');
    });

    Route::get('/profile', [TeacherProfileController::class, 'show'])->name('profile');
    Route::get('/profile/setup', [AdminAuthController::class, 'showProfileSetup'])->name('profile.setup');
    Route::post('/profile/setup', [AdminAuthController::class, 'saveProfileSetup'])->name('profile.setup.save');
});

Route::middleware('auth')->prefix('student')->name('student.')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'showAll'])->name('notifications');
    Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/my-shelf', [StudentMyShelfController::class, 'index'])->name('my-shelf');
    Route::get('/books', [StudentBooksController::class, 'index'])->name('books');
    Route::get('/books/{book}', [StudentBooksController::class, 'show'])->name('books.show');
    Route::post('/books/{book}/favorite', [StudentBooksController::class, 'toggleFavorite'])->name('books.favorite');
    Route::post('/books/{book}/borrow', [StudentBooksController::class, 'borrow'])->name('books.borrow');
    Route::post('/books/{book}/reserve', [StudentBooksController::class, 'reserve'])->name('books.reserve');
    Route::get('/ebooks', [StudentEbooksController::class, 'index'])->name('ebooks');
    Route::get('/ebooks/{ebook}', [StudentEbooksController::class, 'show'])->name('ebooks.show');
    Route::get('/ebooks/{ebook}/read', [StudentEbooksController::class, 'read'])->name('ebooks.read');
    Route::get('/ebooks/{ebook}/file', [StudentEbooksController::class, 'file'])->name('ebooks.file');
    Route::post('/ebooks/{ebook}/favorite', [StudentEbooksController::class, 'toggleFavorite'])->name('ebooks.favorite');
    Route::get('/borrowed-books', [StudentBorrowedBooksController::class, 'index'])->name('borrowed-books');
    Route::post('/borrowed-books/{borrow}/return', [StudentBorrowedBooksController::class, 'return'])->name('borrowed-books.return');
    Route::get('/reserved-books', [StudentReservedBooksController::class, 'index'])->name('reserved-books');
    Route::get('/rules', [StudentRulesController::class, 'index'])->name('rules');

    Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile');
    Route::get('/profile/setup', [StudentAuthController::class, 'showProfileSetup'])->name('profile.setup');
    Route::post('/profile/setup', [StudentAuthController::class, 'saveProfileSetup'])->name('profile.setup.save');
});

Route::post('/login', [StudentAuthController::class, 'login'])->name('login.submit');
Route::post('/register', [StudentAuthController::class, 'register'])->name('register.submit');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Notifications (accessible to all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/all', [NotificationController::class, 'showAll'])->name('notifications.all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});
