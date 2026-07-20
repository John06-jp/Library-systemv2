<?php

namespace App\Services;

use App\Models\AdminActivity;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminActivityLogger
{
    public static function log(
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        string $icon = 'info',
        ?Model $subject = null,
        ?int $userId = null,
    ): AdminActivity {
        return AdminActivity::create([
            'user_id' => $userId ?? self::currentStaffId(),
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'icon' => $icon,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
        ]);
    }

    public static function staff(
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        string $icon = 'staff',
        ?Model $subject = null,
    ): ?AdminActivity {
        if (! self::currentStaffId()) {
            return null;
        }

        return self::log($type, $title, $body, $actionUrl, $icon, $subject);
    }

    public static function catalog(string $action, Book $book, ?string $detail = null): void
    {
        $title = match ($action) {
            'created' => 'Book cataloged',
            'updated' => 'Book updated',
            'deleted' => 'Book deleted',
            'archived' => 'Book archived',
            'unarchived' => 'Book unarchived',
            'restored' => 'Book restored from trash',
            'force_deleted' => 'Book permanently deleted',
            'imported' => 'Books imported',
            default => 'Catalog action',
        };

        $body = $detail
            ? "{$detail} — «{$book->title_statement}»"
            : "«{$book->title_statement}»";

        self::staff(
            AdminActivity::TYPE_CATALOG,
            $title,
            $body,
            route('book.edit', $book->id),
            'book',
            $book,
        );
    }

    public static function catalogBulk(string $title, string $body, ?string $url = null): void
    {
        self::staff(
            AdminActivity::TYPE_CATALOG,
            $title,
            $body,
            $url ?? route('book.index'),
            'book',
        );
    }

    public static function roomReservationPending(Model $reservation, string $roomName, string $date): void
    {
        self::log(
            AdminActivity::TYPE_ROOM_RESERVATION,
            'New room reservation',
            "{$roomName} on {$date} — pending approval",
            route('rooms.pending'),
            'room',
            $reservation,
        );
    }

    public static function bookReservation(Model $reservation, string $studentLabel, string $bookTitle): void
    {
        self::log(
            AdminActivity::TYPE_BOOK_RESERVATION,
            'OPAC book reservation',
            "{$studentLabel} reserved «{$bookTitle}»",
            route('logs.index'),
            'book',
            $reservation,
        );
    }

    public static function patronRegistration(string $kind, string $name, string $idNumber): void
    {
        $route = $kind === 'employee'
            ? route('pending.index', ['tab' => 'employees'])
            : route('pending.index');

        self::log(
            AdminActivity::TYPE_PATRON_REGISTRATION,
            'New '.$kind.' registration',
            "{$name} ({$idNumber}) awaiting approval",
            $route,
            'patron',
        );
    }

    public static function patronEditRequest(Model $request, string $studentLabel): void
    {
        self::log(
            AdminActivity::TYPE_PATRON_EDIT_REQUEST,
            'Patron profile edit request',
            "{$studentLabel} submitted changes for review",
            route('students.pending.requests'),
            'patron',
            $request,
        );
    }

    public static function feedback(string $messagePreview): void
    {
        self::log(
            AdminActivity::TYPE_FEEDBACK,
            'New OPAC feedback',
            $messagePreview,
            route('feedback.index'),
            'feedback',
        );
    }

    public static function circulation(string $title, string $body, ?string $url = null): void
    {
        self::staff(
            AdminActivity::TYPE_CIRCULATION,
            $title,
            $body,
            $url ?? route('logs.index'),
            'circulation',
        );
    }

    public static function selfCheckout(string $studentLabel, int $bookCount): void
    {
        self::log(
            AdminActivity::TYPE_SELF_CHECKOUT,
            'Self check-out',
            "{$studentLabel} checked out {$bookCount} ".($bookCount === 1 ? 'book' : 'books'),
            route('logs.index'),
            'circulation',
        );
    }

    private static function currentStaffId(): ?int
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return in_array($user->role, ['admin', 'staff'], true) ? (int) $user->id : null;
    }
}
