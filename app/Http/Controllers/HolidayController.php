<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\AdminActivity;
use App\Services\AdminActivityLogger;

class HolidayController extends Controller
{
    
    public function all()
    {
        return response()->json(
            Holiday::orderBy('holiday_date')->get()
        );
    }

    public function list()
    {
        return response()->json(Holiday::all());
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'nullable|string|max:255',
        ]);

        $date = $validated['date'];

        $holiday = Holiday::where('holiday_date', $date)->first();
    
        if ($holiday) {
            $holiday->delete();

            AdminActivityLogger::staff(
                AdminActivity::TYPE_SETTINGS,
                'Holiday removed',
                $date,
                route('book.index'),
                'staff',
            );
    
            return response()->json([
                'status' => 'removed'
            ]);
        }
    
        $holiday = Holiday::create([
            'holiday_date' => $date,
            'name' => $validated['name'] ?? null,
        ]);

        AdminActivityLogger::staff(
            AdminActivity::TYPE_SETTINGS,
            'Holiday added',
            ($holiday->name ?: $date),
            route('book.index'),
            'staff',
            $holiday,
        );
    
        return response()->json([
            'status' => 'added',
            'name' => $holiday->name
        ]);
    }
}