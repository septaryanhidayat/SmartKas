<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $classroomId = $request->query('classroom_id') ? (int) $request->query('classroom_id') : null;
        $search = $request->query('search');

        $units = Unit::where('is_active', true)->where('level', '!=', 'foundation')->get();
        $classrooms = Classroom::query();
        if ($unitId) {
            $classrooms->where('unit_id', $unitId);
        }
        $classrooms = $classrooms->get();

        $studentsQuery = Student::with(['unit', 'classroom', 'studentDiscounts.discount'])
            ->orderBy('name', 'asc');

        if ($unitId) {
            $studentsQuery->where('unit_id', $unitId);
        }

        if ($classroomId) {
            $studentsQuery->where('classroom_id', $classroomId);
        }

        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%")
                    ->orWhere('parent_whatsapp', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->paginate(15)->withQueryString();

        return view('students.index', compact('students', 'units', 'classrooms', 'unitId', 'classroomId', 'search'));
    }

    public function show(Student $student): View
    {
        $student->load([
            'unit',
            'classroom',
            'studentDiscounts.discount',
            'invoices.feeComponent',
            'payments.items',
        ]);

        return view('students.show', compact('student'));
    }
}
