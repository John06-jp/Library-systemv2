<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramYear;
use App\Models\ProgramCourse;
use App\Models\AdminActivity;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;

class ProspectusController extends Controller
{
    /**
     * Show all programs
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $programs = Program::with(['years' => fn ($q) => $q->orderBy('year_level'), 'years.courses'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('program_code', 'like', "%{$search}%")
                        ->orWhere('program_name', 'like', "%{$search}%")
                        ->orWhereHas('years.courses', function ($courseQuery) use ($search) {
                            $courseQuery->where('course_code', 'like', "%{$search}%")
                                ->orWhere('course_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('program_name')
            ->get();

        $stats = [
            'programs' => Program::count(),
            'courses' => ProgramCourse::count(),
        ];

        return view('prospectus.index', compact('programs', 'stats', 'search'));
    }

    /**
     * Store a new program and auto-generate year levels
     */
    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'program_code' => 'required|unique:programs,program_code',
            'program_name' => 'required',
            'total_years'  => 'required|integer|min:1|max:6',
        ]);

        $program = Program::create($data);

        // auto-generate year grids
        for ($i = 1; $i <= $program->total_years; $i++) {
            ProgramYear::create([
                'program_id' => $program->id,
                'year_level' => $i,
            ]);
        }
       

        AdminActivityLogger::staff(
            AdminActivity::TYPE_PROSPECTUS,
            'Program created',
            "{$program->program_code} — {$program->program_name}",
            route('prospectus.index'),
            'book',
            $program,
        );

        return redirect()->route('prospectus.index')->with('success', 'Program created successfully.');
    }

    /**
     * Get program years + courses (for AJAX)
     */
    public function getProgramYears($programId)
    {
        $program = Program::with('years.courses')->findOrFail($programId);
        return response()->json(['years' => $program->years]);
    }

    /**
     * Store a course under a specific year
     */
    public function storeCourse(Request $request, $yearId)
    {
        $data = $request->validate([
            'course_code' => 'required',
            'course_name' => 'required',
        ]);
    
        // save and capture the course
        $course = ProgramCourse::create([
            'program_year_id' => $yearId,
            'course_code'     => $data['course_code'],
            'course_name'     => $data['course_name'],
        ]);
        
        if ($request->ajax()) {
            AdminActivityLogger::staff(
                AdminActivity::TYPE_PROSPECTUS,
                'Course added',
                "{$course->course_code} — {$course->course_name}",
                route('prospectus.index'),
                'book',
                $course,
            );

            return view('prospectus.partials.course_item', compact('course'))->render();
        }

        AdminActivityLogger::staff(
            AdminActivity::TYPE_PROSPECTUS,
            'Course added',
            "{$course->course_code} — {$course->course_name}",
            route('prospectus.index'),
            'book',
            $course,
        );

        return redirect()
            ->route('prospectus.index')
            ->with('success', 'Course added successfully.');
    }


    // Update course
    public function updateCourse(Request $request, ProgramCourse $course)
    {
        $request->validate([
            'course_code' => 'required|string|max:50',
            'course_name' => 'required|string|max:255',
        ]);

        $course->update([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name,
        ]);

        AdminActivityLogger::staff(
            AdminActivity::TYPE_PROSPECTUS,
            'Course updated',
            "{$course->course_code} — {$course->course_name}",
            route('prospectus.index'),
            'book',
            $course,
        );
        
        if ($request->ajax()) {
            return view('prospectus.partials.course_item', compact('course'))->render();
        }

        return redirect()->back()->with('success', 'Course updated successfully.');
    }

    // Delete course
    public function destroyCourse(Request $request, ProgramCourse $course)
    {
        $label = "{$course->course_code} — {$course->course_name}";
        $course->delete();

        AdminActivityLogger::staff(
            AdminActivity::TYPE_PROSPECTUS,
            'Course deleted',
            $label,
            route('prospectus.index'),
            'book',
        );
        
        if ($request->ajax()) {
            // Return JSON so JS knows it succeeded
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Course deleted successfully.');
    }
    
    public function updateProgram(Request $request, Program $program)
    {
        $request->validate([
            'program_code' => 'required|string|max:50',
            'program_name' => 'required|string|max:255',
        ]);
    
        $program->update([
            'program_code' => $request->program_code,
            'program_name' => $request->program_name,
        ]);

        AdminActivityLogger::staff(
            AdminActivity::TYPE_PROSPECTUS,
            'Program updated',
            "{$program->program_code} — {$program->program_name}",
            route('prospectus.index'),
            'book',
            $program,
        );
    
        return response()->json([
            'id' => $program->id,
            'program_code' => $program->program_code,
            'program_name' => $program->program_name,
        ]);
    }
    
    public function destroyProgram(Program $program)
    {
        $label = "{$program->program_code} — {$program->program_name}";
        $program->delete();

        AdminActivityLogger::staff(
            AdminActivity::TYPE_PROSPECTUS,
            'Program deleted',
            $label,
            route('prospectus.index'),
            'book',
        );
    
        return response()->json([
            'success' => true,
            'id' => $program->id
        ]);
    }
}
