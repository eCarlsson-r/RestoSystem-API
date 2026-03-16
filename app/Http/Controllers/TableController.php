<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableController
{
    public function index(Request $request)
    {
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => Table::with('sales')->where('floor_number', $request->input('floor'))->get()
        ]);
    }

    public function floorIndex(Request $request)
    {
        $tables = Table::where('branch_id', $request->branch_id)
            ->where('floor_number', $request->floor_number)
            ->get();
            
        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $tables
        ]);
    }

    public function store(Request $request)
    {
        $table = Table::updateOrCreate(
            [
                'table_number' => $request->table_number,
                'floor_number' => $request->floor_number,
                'branch_id' => $request->branch_id,
            ],
            [
                'capacity' => $request->table_capacity,
                'size' => $request->table_size,
                'status' => $request->table_status ?? 'available',
                'position_x' => $request->table_column, // Mapping legacy 'column'
                'position_y' => $request->table_row,    // Mapping legacy 'row'
                'direction' => $request->table_direction ?? 'H',
            ]
        );

        return response()->json(['err' => 0, 'msg' => 'Table saved', 'data' => $table]);
    }

    public function useTable(Request $request)
    {
        $table = Table::find($request->table_id);
        
        $table->update(['status' => 'occupied']);

        $sales = Sale::where([
                'table_id' => $table->id,
                'branch_id' => $table->branch_id,
            ])->first();

        if (!$sales) {
            $sales = Sale::create([
                'branch_id' => $request->branch_id,
                'table_id' => $table->id,
                'employee_id' => $request->user()->employee->id,
                'customer_id' => $request->input('customer_id', 1),
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'status' => 'O',
            ]);
        }

        return response()->json(['err' => 0, 'msg' => 'Table marked as occupied', 'sales_id' => $sales->id]);
    }

    public function releaseTable(Request $request)
    {
        $pendingSales = Sale::where([
                'table_number' => $request->table_number,
                'branch_id' => $request->branch_id,
            ])
            ->whereNotIn('status', ['D', 'X']) // Paid or Cancelled
            ->count();

        if ($pendingSales > 0) {
            return response()->json(['err' => 1, 'msg' => 'There are still payment pending on this table.']);
        }

        Table::where([
            'table_number' => $request->table_number,
            'branch_id' => $request->branch_id,
        ])->update(['status' => 'available']);

        return response()->json(['err' => 0, 'msg' => 'Table released']);
    }

    public function shiftTable(Request $request)
    {
        $occupied = Table::where([
                'branch_id' => $request->branch_id,
                'floor_number' => $request->floor_number,
                'position_x' => $request->position_x,
                'position_y' => $request->position_y,
            ])->exists();

        if ($occupied) {
            return response()->json(['err' => 1, 'msg' => 'Cannot move the table to the occupied new position.']);
        }

        Table::find($request->table_id)->update([
            'position_x' => $request->position_x,
            'position_y' => $request->position_y,
        ]);

        return response()->json(['err' => 0, 'msg' => 'Table moved']);
    }

    public function mergeTable(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $t1 = $request->table1;
            $t2 = $request->table2;
            
            $table1 = Table::find($t1);
            $table2 = Table::find($t2);

            if (!$table1 || !$table2) {
                return response()->json(['err' => 1, 'msg' => 'Tables not found']);
            }

            $newSize = $table1->size + $table2->size;
            $direction = 'H';
            
            // Layout logic from legacy
            if ($table1->position_x > $table2->position_x) {
                $direction = 'H';
            } elseif ($table1->position_y > $table2->position_y) {
                $direction = 'V';
            }

            $table1->update([
                'size' => $newSize,
                'capacity' => (2 * $newSize + 2),
                'direction' => $direction
            ]);

            $table2->delete();

            return response()->json(['err' => 0, 'msg' => 'Tables merged successfully']);
        });
    }

    public function splitTable(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $tableId = $request->input('table_id');
            $table = Table::find($tableId);

            if (!$table) {
                return response()->json(['err' => 1, 'msg' => 'Please select the table to split.']);
            }

            $originalSize = $table->size;
            $currentTableNumber = (int)$table->table_number;
            $floorNumber = $table->floor_number;
            $branchId = $table->branch_id;

            if ($table->direction == "H") {
                for ($h = 0; $h < $originalSize; $h++) {
                    if ($h == 0) {
                        $table->update([
                            'size' => 1,
                            'capacity' => 4,
                            'status' => 'available'
                        ]);
                    } else {
                        Table::create([
                            'table_number' => $currentTableNumber + $h,
                            'floor_number' => $floorNumber,
                            'branch_id' => $branchId,
                            'size' => 1,
                            'capacity' => 4,
                            'status' => 'available',
                            'position_x' => $table->position_x + $h,
                            'position_y' => $table->position_y,
                            'direction' => 'H'
                        ]);
                    }
                }
            } elseif ($table->direction == "V") {
                // Determine the "row increment" for table numbering
                // Legacy: based on number of tables per row/floor
                $tablesInThisRow = Table::where('floor_number', $floorNumber)
                    ->where('position_y', $table->position_y)
                    ->where('branch_id', $branchId)
                    ->count();

                for ($v = 0; $v < $originalSize; $v++) {
                    if ($v == 0) {
                        $table->update([
                            'size' => 1,
                            'capacity' => 4,
                            'status' => 'available'
                        ]);
                    } else {
                        Table::create([
                            'table_number' => $currentTableNumber + ($v * $tablesInThisRow),
                            'floor_number' => $floorNumber,
                            'branch_id' => $branchId,
                            'size' => 1,
                            'capacity' => 4,
                            'status' => 'available',
                            'position_x' => $table->position_x,
                            'position_y' => $table->position_y + $v,
                            'direction' => 'V'
                        ]);
                    }
                }
            }

            return response()->json(['err' => 0, 'msg' => 'Table split successfully']);
        });
    }

    public function destroy(Request $request)
    {
        Table::where([
            'table_number' => $request->table_number,
            'branch_id' => $request->branch_id,
        ])->delete();

        return response()->json(['err' => 0, 'msg' => 'Table removed']);
    }
}
