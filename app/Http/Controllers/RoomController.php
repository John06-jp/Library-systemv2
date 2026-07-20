<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\AdminActivity;
use App\Services\AdminActivityLogger;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
        ]);

        $room = Room::create($request->only('name', 'description', 'capacity'));

        AdminActivityLogger::staff(
            AdminActivity::TYPE_ROOM,
            'Room created',
            $room->name,
            route('rooms.edit', $room->id),
            'room',
            $room,
        );

        return redirect()->route('rooms.index')->with('success', 'Room added successfully!');
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
        ]);

        $room->update($request->only('name', 'description', 'capacity'));

        AdminActivityLogger::staff(
            AdminActivity::TYPE_ROOM,
            'Room updated',
            $room->name,
            route('rooms.edit', $room->id),
            'room',
            $room,
        );

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully!');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $name = $room->name;
        $room->delete();

        AdminActivityLogger::staff(
            AdminActivity::TYPE_ROOM,
            'Room deleted',
            $name,
            route('rooms.index'),
            'room',
        );

        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully!');
    }
}
