<?php
namespace App\Http\Controllers;

use App\Support\PerPage;
use App\Support\RespondsWithHydratablePartial;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminActivity;
use App\Services\AdminActivityLogger;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use RespondsWithHydratablePartial;

    public function create()
    {
        // Show the createuser form
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'lname' => 'required|string|max:255',
            'fname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,staff,faculty,student',
        ]);

        // Create user
        $user = User::create([
            'lname' => $validated['lname'],
            'fname' => $validated['fname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        AdminActivityLogger::staff(
            AdminActivity::TYPE_USER,
            'User account created',
            "{$user->fullName()} ({$user->role})",
            route('users.edit', $user->id),
            'patron',
            $user,
        );

        return redirect()->route('users.create')->with('success', 'User account created successfully!');
    }
    
    // show user
   public function index(Request $request)
    {
        $users = User::orderBy('lname')->orderBy('fname')
            ->paginate(PerPage::resolve($request, 25))
            ->withQueryString();

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return $this->hydratableResponse(
            $request,
            'accounts.index',
            'accounts.partials.list-table',
            compact('users', 'roleCounts'),
        );
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('accounts.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fname' => 'required|string',
            'lname' => 'required|string',
            'email' => 'required|email',
            'role' => 'required|in:admin,staff,faculty,student',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['fname', 'lname', 'email', 'role']));

        AdminActivityLogger::staff(
            AdminActivity::TYPE_USER,
            'User account updated',
            "{$user->fullName()} ({$user->role})",
            route('users.edit', $user->id),
            'patron',
            $user,
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $label = "{$user->fullName()} ({$user->email})";
        $user->delete();

        AdminActivityLogger::staff(
            AdminActivity::TYPE_USER,
            'User account deleted',
            $label,
            route('users.index'),
            'patron',
        );

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
