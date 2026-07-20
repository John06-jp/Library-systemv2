<?php

namespace App\Http\Controllers;

use App\Models\PendingStudent;
use App\Models\Student;
use App\Models\Program;
use App\Models\PendingEmployee;
use App\Models\Role;
use App\Support\MiddleInitial;
use App\Support\PerPage;
use App\Support\RespondsWithHydratablePartial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PendingStudentController extends Controller
{
    use RespondsWithHydratablePartial;

    public function index(Request $request)
    {
        $search = $request->input('search');
        $programs = Cache::remember('pending.program_list', 600, fn () =>
            Program::orderBy('program_name')->get()
        );
        $defaultTab = $request->input('tab', 'students');
        $backRoute = $defaultTab === 'employees'
            ? route('employees.index')
            : route('students.index');
        $perPage = PerPage::resolve($request, 15);

        $pendingStudents = PendingStudent::with('role')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('course', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage, ['*'], 'students_page')
            ->withQueryString();

        $pendingEmployees = PendingEmployee::with('role')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('program', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage, ['*'], 'employees_page')
            ->withQueryString();

        return $this->hydratableResponse(
            $request,
            'pending.index',
            'pending.partials.students-table',
            compact(
                'pendingStudents',
                'pendingEmployees',
                'programs',
                'defaultTab',
                'backRoute',
                'search',
            ),
            fn (Request $request) => $request->input('tab', 'students') === 'employees'
                ? 'pending.partials.employees-table'
                : 'pending.partials.students-table',
        );
    }

    public function create()
    {
        $roles = Role::all();
        $programs = Program::orderBy('program_name')->get();
        $workStartYears = range((int) date('Y'), 1980);

        return view('pending.register', compact('roles', 'programs', 'workStartYears'));
    }

    public function store(Request $request)
    {
        MiddleInitial::mergeIntoRequest($request);

        $validated = $request->validate([
            'id_number'        => 'required|string|max:255',
            'firstname'        => 'required|string|max:255',
            'lastname'         => 'required|string|max:255',
            'middle_initial'   => MiddleInitial::validationRule(),
            'birthday'         => 'nullable|date',
            'course'           => 'required|string|max:255',
            'year'             => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'emergency_person' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:255',
            'emergency_number' => 'nullable|string|max:20',
            'emergency_address' => 'nullable|string',
            'profile_picture'  => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'student_signature'=> 'nullable|string', // base64
        ]);

        // Profile picture
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_profile_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $dest = public_path('images/profile_pictures');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, $filename);
            $validated['profile_picture'] = 'images/profile_pictures/' . $filename;
        }

        // Signature (base64)
        if (!empty($validated['student_signature']) && str_starts_with($validated['student_signature'], 'data:')) {

            [$meta, $contents] = explode(',', $validated['student_signature'], 2);
            $ext = str_contains($meta, 'jpeg') || str_contains($meta, 'jpg') ? 'jpg' : 'png';

            $sigDest = public_path('images/student_signatures');
            if (!file_exists($sigDest)) {
                mkdir($sigDest, 0755, true);
            }

            $sigName = time() . '_sig.' . $ext;
            file_put_contents(
                $sigDest . DIRECTORY_SEPARATOR . $sigName,
                base64_decode($contents)
            );

            $validated['student_signature'] = 'images/student_signatures/' . $sigName;
        }

        PendingStudent::create($validated);

        \App\Services\AdminActivityLogger::patronRegistration(
            'student',
            "{$validated['lastname']}, {$validated['firstname']}",
            $validated['id_number'],
        );

        return back()->with('success', 'Registration submitted. Awaiting admin approval.');
    }

    /**
     * APPROVE pending student (QR GENERATED HERE)
     */
    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            // Lock students table for safe QR generation
            $lastQr = Student::lockForUpdate()
                ->orderBy('id', 'desc')
                ->value('qrcode');

            $nextNumber = 1;

            if ($lastQr && str_starts_with($lastQr, 'S-')) {
                $nextNumber = intval(Str::after($lastQr, 'S-')) + 1;
            }

            $newQr = 'S-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

            $pending = PendingStudent::findOrFail($id);

            Student::create([
                'id_number'        => $pending->id_number,
                'firstname'        => $pending->firstname,
                'lastname'         => $pending->lastname,
                'middle_initial'   => $pending->middle_initial,
                'birthday'         => $pending->birthday,
                'course'           => $pending->course,
                'year'             => $pending->year,
                'mobile_number' => $pending->mobile_number,
                'address' => $pending->address,
                'emergency_person' => $pending->emergency_person,
                'emergency_relationship' => $pending->emergency_relationship,
                'emergency_number' => $pending->emergency_number,
                'emergency_address' => $pending->emergency_address,
                'profile_picture'  => $pending->profile_picture,
                'student_signature'=> $pending->student_signature,
                'qrcode'           => $newQr,
            ]);

            $pending->delete();
        });

        return back()->with('success', 'Student approved and QR generated.');
    }

}
