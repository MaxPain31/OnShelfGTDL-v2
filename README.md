# OnShelf

### A digital library and circulation platform for the school community

<p align="center">
	<img src="public/img/logo.png" alt="OnShelf logo" width="180">
</p>
<p align="center">
	<strong>Discover. Borrow. Read. Return.</strong><br>
	OnShelf brings physical library operations and digital reading into one focused web experience.
</p>
<p align="center">
	<img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
	<img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2 or newer">
	<img src="https://img.shields.io/badge/Tailwind%20CSS-4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
	<img src="https://img.shields.io/badge/Vite-7-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 7">
</p>
## Overview

OnShelf is a role-based library management system designed for students, teachers, and library administrators. It gives readers a searchable catalog of physical books and ebooks, while giving administrators the tools to manage inventory, circulation, reservations, users, attendance, library rules, notifications, and reports.
The system is built around a simple idea: the library should be easy to use for the person looking for a book and easy to operate for the people responsible for it.

## What the system does

| Area               | Capabilities                                                                                                               |
| ------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| Physical catalog   | Browse book details, categories, authors, shelf locations, ISBNs, descriptions, availability, view counts, and favorites.  |
| Digital library    | Browse ebooks, open their details, read them in the built-in reader, and save favorites.                                   |
| Circulation        | Borrow available books, track due dates, view active or overdue loans, and submit returns.                                 |
| Reservations       | Reserve books, track claim deadlines, and view pending, claimed, or voided reservations.                                   |
| Personal shelf     | Keep a reader-focused view of favorite books and ebooks.                                                                   |
| Notifications      | View notifications, mark individual notifications as read, or clear unread status in bulk.                                 |
| Library operations | Manage books, ebooks, borrowing records, reservations, students, teachers, attendance, and configurable rules.             |
| Reporting          | Review library activity and export supported book, ebook, user, attendance, and report data to PDF or spreadsheet formats. |
| Account access     | Register as a student or teacher, log in with an LRN or employee number, recover a password, and complete profile details. |

## User roles

### Student

- Register with student information, LRN, grade, section, adviser, and address details.
- Browse and view physical books and ebooks.
- Favorite books and ebooks from their catalog pages.
- Borrow available books, reserve books, request returns, and monitor due dates.
- Review their borrowed books, reservations, rules, notifications, profile, and personal shelf.

### Teacher

- Register with teacher information and an employee number.
- Use the same discovery, ebook, favorite, borrowing, reservation, return, and notification workflows as students.
- Manage student accounts within the teacher workspace.
- View teacher-specific dashboard, profile, shelf, borrowing history, reservations, and rules.

### Administrator

- View the operational dashboard and manage student and teacher records.
- Create, update, delete, and export physical book and ebook records.
- Review borrowing activity and process book returns.
- Verify or void reservations.
- Record, edit, delete, and export library attendance.
- Create, update, delete, and activate or deactivate library rules.
- Review reports and export supported data for administrative use.

## Core workflows

### Borrowing a physical book

1. A student or teacher opens the physical book catalog.
2. The reader reviews the book's availability and details.
3. The reader borrows the book when stock is available.
4. OnShelf records the borrow date, due date, user, book, and current status.
5. The reader can return the book from their borrowed-books page, while an administrator can process the return from circulation management.

Borrow records use `borrowed`, `returned`, and `overdue` states. The system also exposes active and overdue loan queries for dashboards and account views.

### Reserving a book

Reservations are recorded with a requested reserve date, due date, claim deadline, and status. A reservation can be `pending`, `claimed`, or `voided`. Expired pending reservations are automatically voided by the scheduled reservation command.

### Reading an ebook

Readers can open an ebook detail page, launch the in-app reader, and access the stored ebook file through the student or teacher ebook workflow. Ebook favorites and view activity are tracked separately from physical book circulation.

## Notifications and scheduled tasks

OnShelf includes both in-app notifications and email templates for important library events, including:

- Book borrowed
- Book reserved
- Book returned
- Approaching due dates
- Password resets
- Student and teacher account creation

The application schedules two maintenance tasks in `routes/console.php`:
| Schedule | Purpose |
| --- | --- |
| Daily at 12:00 AM | Void reservations that have passed their claim deadline. |
| Daily at 9:00 AM | Check approaching due dates and reservation claim deadlines for notifications. |

## Technology stack

- **Backend:** PHP 8.2+, Laravel 12, Eloquent ORM
- **Frontend:** Blade templates, Tailwind CSS 4, Vite 7, Axios
- **Database:** Laravel migrations with configurable database connection
- **Exports:** `maatwebsite/excel` for spreadsheet exports and `barryvdh/laravel-dompdf` for PDF exports
- **Authentication:** Session-based Laravel authentication with role-aware redirects
- **Testing:** PHPUnit through Laravel's `artisan test` command
- **Development:** Laravel Artisan, Composer scripts, npm scripts, and optional Docker configuration

## Project structure

```text
app/
	Http/Controllers/      Role-specific pages and actions
	Http/Repositories/     Focused data access classes
	Http/Services/          Authentication and application services
	Mail/                   Notification email classes
	Models/                 Users, books, ebooks, loans, reservations, and more
database/
	migrations/             Database schema history
	seeders/                Roles and initial administrator setup
resources/views/          Blade pages for auth, students, teachers, and admins
routes/
	web.php                 Web routes grouped by authentication and role
	console.php             Scheduled maintenance commands
public/                   Public assets and uploaded-file entry points
tests/                    Feature and unit test suites
```

## Local installation

### Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- A configured database supported by Laravel
- A mail service if email notifications are enabled

### Setup

```bash
git clone <your-repository-url>
cd onshelf-web
composer run setup
```

The setup script installs PHP and JavaScript dependencies, creates `.env` when needed, generates the application key, runs migrations, and builds frontend assets.
Configure the database, mail settings, filesystem settings, and application URL in `.env` before using the application.

### Run the development environment

```bash
composer run dev
```

This starts the Laravel server, queue listener, and Vite development server together. For a production-style asset build, run:

```bash
npm run build
```

### Run tests

```bash
composer run test
```

## Initial administrator account

The database seeders create the `Administrator`, `Teacher`, and `Student` roles and seed an initial administrator record. Change the seeded credentials and use environment-specific secrets before deploying the application to a shared or production environment.

## Design principles

- **One library experience:** physical and digital resources are discoverable from role-specific workspaces.
- **Clear accountability:** every borrow, reservation, return, and attendance record is linked to the relevant user or recorder.
- **Role-aware access:** student, teacher, and administrator routes are separated while sharing the same authentication foundation.
- **Operational visibility:** dashboards, reports, exports, notifications, and scheduled checks support day-to-day library work.

## Project status

OnShelf is an actively developed Laravel application. The current codebase contains the primary catalog, ebook, circulation, reservation, account, notification, attendance, rules, reporting, and export workflows described above. Test coverage and deployment configuration can continue to grow as the system moves toward production use.

## License

This project is built with the [Laravel framework](https://laravel.com), which is open-sourced under the [MIT License](https://opensource.org/licenses/MIT). Add the project's own license here when one has been selected for OnShelf.
