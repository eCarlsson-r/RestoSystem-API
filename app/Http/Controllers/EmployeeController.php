<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        return response()->json(['err' => 0, 'msg' => 'Employees fetched successfully', 'data' => Employee::with('user')->get()]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $empCode = $request->input('emp-code');
            
            // Handle User account
            $user = null;
            if ($request->has('acc_username')) {
                $user = User::updateOrCreate(
                    ['id' => $request->input('emp-account')],
                    [
                        'username' => $request->input('acc_username'),
                        'password' => bcrypt($request->input('acc_password', '1234')),
                        'type' => $request->input('emp-job-type'),
                        'name' => $request->input('emp-name'),
                    ]
                );
            }

            $employee = Employee::updateOrCreate(
                ['id' => $empCode],
                [
                    'name' => $request->input('emp-name'),
                    'branch_id' => $request->input('emp-branch-code'),
                    'job_type' => $request->input('emp-job-type'),
                    'gender' => $request->input('emp-gender'),
                    'status' => $request->input('emp-status'),
                    'join_date' => $request->input('emp-join-date'),
                    'quit_date' => $request->input('emp-quit-date'),
                    'home_address' => $request->input('emp-address'),
                    'phone' => $request->input('emp-phone'),
                    'mobile' => $request->input('emp-mobile'),
                    'email' => $request->input('emp-email'),
                    'account_id' => $user ? $user->id : null,
                ]
            );

            return response()->json(['err' => 0, 'msg' => 'Employee saved', 'data' => $employee]);
        });
    }

    public function getAttendance($id)
    {
        // Legacy get_att_record_employee logic
        return response()->json(['err' => 0, 'data' => []]);
    }

    public function getPayroll($id)
    {
        // Legacy get_emp_gaji logic
        return response()->json(['err' => 0, 'data' => []]);
    }

    public function destroy($id)
    {
        Employee::destroy($id);
        return response()->json(['err' => 0, 'msg' => 'Employee removed']);
    }
}
