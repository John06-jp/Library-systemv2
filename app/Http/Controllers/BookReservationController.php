<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\Student;
use Illuminate\Http\Request;
use RuntimeException;

class BookReservationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string',
            'book_id' => 'required|integer|exists:books,id',
        ]);

        $student = Student::where('id_number', $request->student_id)->first();
        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Student ID not found.',
            ], 422);
        }

        $book = Book::findOrFail((int) $request->book_id);

        try {
            $reservation = BookReservation::reserveForStudent($student, $book);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $book->refresh();

        $studentLabel = "{$student->lastname}, {$student->firstname}";
        \App\Services\AdminActivityLogger::bookReservation(
            $reservation,
            $studentLabel,
            $book->title_statement ?? 'Untitled',
        );

        return response()->json([
            'success' => true,
            'message' => $reservation->status === BookReservation::STATUS_PENDING
                ? 'Copy reserved. You will be first in line when it is returned.'
                : 'Copy reserved and placed on hold for you.',
            'reservation' => [
                'book_id' => $book->id,
                'status' => $reservation->status,
                'availability' => $book->availability,
            ],
        ]);
    }
}
