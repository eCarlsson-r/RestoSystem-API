<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController
{
    public function index(Request $request)
    {
        return response()->json(['err' => 0, 'msg' => 'Branches fetched successfully', 'data' => Branch::all()]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $branchId = $request->input('branch-code');
            
            $branch = Branch::updateOrCreate(
                ['id' => $branchId],
                [
                    'name' => $request->input('name'),
                    'address' => $request->input('address'),
                    'phone' => $request->input('phone'),
                    'floor_number' => $request->input('floor_number'),
                    'kitchen_no' => $request->input('kitchen_no'),
                    'bartender_no' => $request->input('bartender_no'),
                ]
            );

            // Legacy logic: Auto-generate tables if creating a new branch
            if ($request->has('table_number')) {
                $tableCount = $request->input('table_number');
                $floorCount = $request->input('floor_number');
                
                for ($f = 1; $f <= $floorCount; $f++) {
                    for ($t = 1; $t <= $tableCount; $t++) {
                        $col = ($t - 1) % 8;
                        $row = intdiv(($t - 1), 8);
                        
                        Table::updateOrCreate(
                            [
                                'table_number' => $t,
                                'floor_number' => $f,
                                'branch_id' => $branch->id
                            ],
                            [
                                'capacity' => 4,
                                'size' => 1,
                                'status' => 'available',
                                'position_x' => $col,
                                'position_y' => $row,
                                'direction' => 'H'
                            ]
                        );
                    }
                }
            }

            // Legacy logic for auto-creating accounts for kitchen/bartender
            $this->createServiceAccounts($branch);

            return response()->json(['err' => 0, 'msg' => 'Branch saved successfully', 'data' => $branch]);
        });
    }

    protected function createServiceAccounts($branch)
    {
        // Simple implementation of legacy account generation
        for ($i = 0; $i < $branch->kitchen_no; $i++) {
            $suffix = ($i == 0) ? '' : $i;
            $kitchenName = ($branch->kitchen_no > 1) ? 'Kitchen #'.$sufix.' ' . $branch->name : 'Kitchen ' . $branch->name;
            User::firstOrCreate(
                ['username' => $branch->id . '_KTCN' . $suffix],
                ['password' => bcrypt('1234'), 'type' => 'KITCHEN', 'name' => $kitchenName]
            );
        }

        for ($i = 0; $i < $branch->bartender_no; $i++) {
            $suffix = ($i == 0) ? '' : $i;
            $bartenderName = ($branch->bartender_no > 1) ? 'Bartender #'.$sufix.' ' . $branch->name : 'Bartender ' . $branch->name;
            User::firstOrCreate(
                ['username' => $branch->id . '_BART' . $suffix],
                ['password' => bcrypt('1234'), 'type' => 'KITCHEN', 'name' => $bartenderName]
            );
        }
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            Branch::destroy($id);
            Table::where('branch_id', $id)->delete();
            return response()->json(['err' => 0, 'msg' => 'Branch and its tables removed']);
        });
    }
}
