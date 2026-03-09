<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
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
                    'name' => $request->input('branch-name'),
                    'address' => $request->input('branch-address'),
                    'phone' => $request->input('branch-phone'),
                    'floor_number' => $request->input('branch-floor-no'),
                    'kitchen_no' => $request->input('branch-kitchen-no'),
                    'bartender_no' => $request->input('branch-bartender-no'),
                ]
            );

            // Legacy logic: Auto-generate tables if creating a new branch
            if ($request->has('branch-table-no')) {
                $tableCount = $request->input('branch-table-no');
                $floorCount = $request->input('branch-floor-no');
                
                for ($f = 1; $f <= $floorCount; $f++) {
                    for ($t = 1; $t <= $tableCount; $t++) {
                        $col = 5 + ((($t - 1) % 8) * 100);
                        $row = 5 + (intdiv(($t - 1), 8) * 100);
                        
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
            User::firstOrCreate(
                ['username' => $branch->id . '_KTCN' . $suffix],
                ['password' => bcrypt('1234'), 'type' => 'KITCHEN', 'name' => 'Kitchen ' . $branch->name]
            );
        }

        for ($i = 0; $i < $branch->bartender_no; $i++) {
            $suffix = ($i == 0) ? '' : $i;
            User::firstOrCreate(
                ['username' => $branch->id . '_BART' . $suffix],
                ['password' => bcrypt('1234'), 'type' => 'KITCHEN', 'name' => 'Bartender ' . $branch->name]
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
