<?php

namespace App\Mail;

use App\Models\BookReservation;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookReservationReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public BookReservation $reservation;

    public int $holdDays;

    public function __construct(BookReservation $reservation)
    {
        $this->reservation = $reservation->loadMissing(['student', 'book']);
        $this->holdDays = Setting::reservationHoldDays();
    }

    public function build()
    {
        $title = $this->reservation->book?->title_statement ?? 'your reserved book';
        $studentName = trim($this->reservation->student?->firstname.' '.$this->reservation->student?->lastname);

        return $this->subject("Your reserved book is ready — {$title}")
            ->view('emails.book_reservations.ready', [
                'reservation' => $this->reservation,
                'holdDays' => $this->holdDays,
                'studentName' => $studentName,
                'expiresAt' => $this->reservation->expiresAt(),
            ]);
    }
}
