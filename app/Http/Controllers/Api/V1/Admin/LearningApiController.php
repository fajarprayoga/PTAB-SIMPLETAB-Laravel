<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Absent;
use App\Bill;
use App\Http\Controllers\Controller;
use App\Schedule;
use App\Schedule_subject;
use App\Student;
use App\Subject;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class LearningApiController extends Controller
{

    public function scheduleshow($id)
    {
        $datas = Schedule::with('periode')
            ->with('semester')
            ->with('grade')->get()->find($id);

        //Check if datas found or not.
        if (is_null($datas)) {
            $message = 'Data not found.';
            $status = false;
            return response()->json([
                'success' => $status,
                'message' => $message,
                'data' => $datas,
            ]);
        }
        $message = 'Data retrieved successfully.';
        $status = true;

        //Call function for response data
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $datas,
        ]);
    }

    public function paid($student_id, $periode)
    {
        $bill = Bill::where('student_id', $student_id)
            ->where('periode', $periode)
            ->first();
        if (empty($bill)) {
            $bill['status'] = 'unpaid';
            $bill['register'] = date("Y-m-d");
            $bill['code'] = '';
            $bill['amount'] = 0;
            $bill = (object) $bill;
        }

        if (!empty($bill)) {
            return response()->json([
                'success' => true,
                'data' => $bill,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Data',
            ], 401);
        }
    }

    public function paidProcess(Request $request)
    {
        //get status checkbox
        $status = $request->input('student_id');
        //get student class
        $student = Student::find($request->input('student_id'));
        //check if data not exist
        $bill = Bill::where('student_id', $request->input('student_id'))
            ->where('periode', $request->input('periode'))
            ->first();
        if (empty($bill)) {
            //create
            $data = ['register' => $request->input('register'), 'code' => $request->input('code'), 'periode' => $request->input('periode'), 'student_id' => $request->input('student_id'), 'amount' => $request->input('amount'), 'status' => $status];

            try {
                $bill = Bill::create($data);
                return response()->json([
                    'success' => true,
                    'data' => $bill,
                ]);
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save data.',
                ], 401);
            }
        } else {
            //update
            $bill->status = $status;
            $bill->register = $request->input('register');
            $bill->code = $request->input('code');
            $bill->amount = $request->input('amount');

            try {
                $bill->save();
                return response()->json([
                    'success' => true,
                    'data' => $bill,
                ]);
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save data.',
                ], 401);
            }
        }

        return redirect(route("admin.bills.index") . "?period=" . $request->input('periode') . "&status-filter=" . "&grade=" . $student->grade_id);
    }

    public function bills(Request $request)
    {
        //get input
        $status = $request->status;
        $period = $request->period;
        //set query
        $bill = Student::selectRaw('students.*,bills.code,bills.register,bills.status,bills.periode,bills.amount')
            ->leftJoinSub(Bill::selectRaw('*')
                    ->where(function ($query) use ($period) {
                        if ($period != "") {
                            $query->where('bills.periode', '=', $period);
                        }
                    }),
                'bills',
                function ($join) {
                    $join->on('students.id', '=', 'bills.student_id');
                }
            )
            ->where(function ($query) use ($status) {
                if ($status == "unpaid") {
                    $query->where('bills.status', '=', $status)
                        ->orWhere('bills.status', null);
                } else if ($status == "paid") {
                    $query->where('bills.status', '=', $status);
                }
            })
            ->FilterGrade()
            ->get();
        if (!empty($bill)) {
            return response()->json([
                'success' => true,
                'data' => $bill,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Data',
            ], 401);
        }
    }

    public function bill($student_id, $register, $schedule_subject_id)
    {
        $absent = Absent::where('student_id', $student_id)
            ->where('register', $register)
            ->where('schedule_subject_id', $schedule_subject_id)
            ->with('student')
            ->with('schedulesubject')
            ->first();
        if (empty($absent)) {
            $student = Student::find($student_id);
            $Schedule_subject = Schedule_subject::find($schedule_subject_id);
            $subject = Subject::find($Schedule_subject->subject_id);
            $absent['student']['name'] = $student->name;
            $absent['schedulesubject']['subjects']['name'] = $subject->name;
            $absent['bill'] = 'unpaid';
            $absent['amount'] = 0;
            $absent['register'] = $register;
            $absent['student_id'] = $student_id;
            $absent['schedule_subject_id'] = $schedule_subject_id;
            $absent['description'] = '';
            //$absent = (object) $absent;
        }
        $absent = json_decode(json_encode($absent), false);
        if (!empty($absent)) {
            return response()->json([
                'success' => true,
                'data' => $absent,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Data',
            ], 401);
        }

    }

    public function billProcess(Request $request)
    {
        $absent = Absent::where('student_id', $request->input('student_id_hidden'))
            ->where('register', $request->input('register'))
            ->where('schedule_subject_id', $request->input('schedule_subject_id_hidden'))
            ->first();
        if (empty($absent)) {
            //create
            $data = ['register' => $request->input('register'), 'student_id' => $request->input('student_id_hidden'), 'schedule_subject_id' => $request->input('schedule_subject_id_hidden'), 'presence' => 'alpha', 'bill' => $request->input('bill'), 'amount' => $request->input('amount')];
            try {
                $absent = Absent::create($data);
                return response()->json([
                    'success' => true,
                    'data' => $absent,
                ]);
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save data.',
                ], 401);
            }
        } else {
            //update
            $absent->bill = $request->input('bill');
            $absent->amount = $request->input('amount');
            try {
                $absent->save();
                return response()->json([
                    'success' => true,
                    'data' => $absent,
                ]);
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save data.',
                ], 401);
            }
        }
    }

    public function presence($student_id, $register, $schedule_subject_id)
    {
        $absent = Absent::where('student_id', $student_id)
            ->where('register', $register)
            ->where('schedule_subject_id', $schedule_subject_id)
            ->with('student')
            ->with('schedulesubject')
            ->first();
        if (empty($absent)) {
            $student = Student::find($student_id);
            $Schedule_subject = Schedule_subject::find($schedule_subject_id);
            $subject = Subject::find($Schedule_subject->subject_id);
            $absent['student']['name'] = $student->name;
            $absent['schedulesubject']['subjects']['name'] = $subject->name;
            $absent['presence'] = 'alpha';
            $absent['register'] = $register;
            $absent['student_id'] = $student_id;
            $absent['schedule_subject_id'] = $schedule_subject_id;
            $absent['description'] = '';
            //$absent = (object) $absent;
        }
        $absent = json_decode(json_encode($absent), false);
        if (!empty($absent)) {
            return response()->json([
                'success' => true,
                'data' => $absent,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Data',
            ], 401);
        }
    }

    public function presenceProcess(Request $request)
    {
        $absent = Absent::where('student_id', $request->input('student_id_hidden'))
            ->where('register', $request->input('register'))
            ->where('schedule_subject_id', $request->input('schedule_subject_id_hidden'))
            ->first();
        if (empty($absent)) {
            //create
            $data = ['register' => $request->input('register'), 'student_id' => $request->input('student_id_hidden'), 'schedule_subject_id' => $request->input('schedule_subject_id_hidden'), 'presence' => $request->input('presence'), 'description' => $request->input('description'), 'bill' => 'unpaid', 'amount' => 0];

            try {
                $absent = Absent::create($data);
                return response()->json([
                    'success' => true,
                    'data' => $absent,
                ]);
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save data.',
                ], 401);
            }

        } else {
            //update
            $absent->presence = $request->input('presence');
            $absent->description = $request->input('description');

            try {
                $absent->save();
                return response()->json([
                    'success' => true,
                    'data' => $absent,
                ]);
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save data.',
                ], 401);
            }

        }
    }

    public function absents($id, $gid, Request $request)
    {
        //get input
        $presence = $request->presence;
        $register = $request->register;
        $schedule_subject = Schedule_subject::find($id);
        $schedule = Schedule::with('subjects')->get()->find($schedule_subject->schedule_id);
        //return $schedule->subjects[0]['name'];

        //default view
        $absents = Student::selectRaw('students.*,absents.code,absents.register,absents.presence,absents.description,absents.schedule_subject_id,absents.amount')
            ->leftJoinSub(Absent::selectRaw('*')
                    ->where(function ($query) use ($register, $id) {
                        if ($register != "") {
                            $query->where('absents.register', '=', $register);
                        }
                        $query->where('absents.schedule_subject_id', $id);
                    }),
                'absents',
                function ($join) {
                    $join->on('students.id', '=', 'absents.student_id');
                }
            )
            ->where(function ($query) use ($presence) {
                if ($presence == "alpha") {
                    $query->where('absents.presence', '=', $presence)
                        ->orWhere('absents.presence', null);
                } else if ($presence == "sakit" || $presence == "ijin" || $presence == "masuk") {
                    $query->where('absents.presence', '=', $presence);
                }
            })
            ->get();

        if (!empty($absents)) {
            return response()->json([
                'success' => true,
                'data' => $absents,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Data',
            ], 401);
        }
    }

    public function schedules(Request $request)
    {
        $schedules = Schedule_subject::selectRaw("schedule_subjects.schedule_id as id,schedule_subjects.id as schedule_subject_id,schedule_subjects.teacher_id,schedule_subjects.subject_id,schedules.code as schedule_code,schedules.register as schedule_register,periodes.name as periode_name,semesters.name as semester_name,grades.id as grade_id,grades.name as grade_name")
            ->leftjoin('schedules', 'schedule_subjects.schedule_id', '=', 'schedules.id')
            ->join('periodes', 'schedules.periode_id', '=', 'periodes.id')
            ->join('semesters', 'schedules.semester_id', '=', 'semesters.id')
            ->join('grades', 'schedules.grade_id', '=', 'grades.id')
            ->with('teachers')
            ->with('subjects')
            ->FilterPeriode()
            ->FilterSemester()
            ->FilterGrade()
            ->FilterRegister()
            ->get();

        if (!empty($schedules)) {
            return response()->json([
                'success' => true,
                'data' => $schedules,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Data',
            ], 401);
        }
    }

}
