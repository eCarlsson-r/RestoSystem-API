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
        $table = Table::where([
            'table_number' => $request->table_number,
            'floor_number' => $request->floor_number,
            'branch_id' => $request->branch_id,
        ]);
        
        $table->update(['status' => 'occupied']);

        $sales = Sale::create([
            'branch_id' => $request->branch_id,
            'table_id' => $table->id,
            'employee_id' => $request->user()->id,
            'customer_id' => $request->input('customer_id', 1),
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'status' => 'O',
        ]);

        return response()->json(['err' => 0, 'msg' => 'Table marked as occupied', 'data' => [$sales->id]]);
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
                'position_x' => $request->table_column,
                'position_y' => $request->table_row,
            ])->exists();

        if ($occupied) {
            return response()->json(['err' => 1, 'msg' => 'Cannot move the table to the occupied new position.']);
        }

        Table::where([
            'table_number' => $request->table_number,
            'floor_number' => $request->floor_number,
            'branch_id' => $request->branch_id,
        ])->update([
            'position_x' => $request->table_column,
            'position_y' => $request->table_row,
        ]);

        return response()->json(['err' => 0, 'msg' => 'Table moved']);
    }

    public function mergeTable(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $t1 = $request->table1_number;
            $t2 = $request->table2_number;
            
            $table1 = Table::where(['table_number' => $t1, 'branch_id' => $request->branch_id, 'floor_number' => $request->floor_number])->first();
            $table2 = Table::where(['table_number' => $t2, 'branch_id' => $request->branch_id, 'floor_number' => $request->floor_number])->first();

            if (!$table1 || !$table2) {
                return response()->json(['err' => 1, 'msg' => 'Tables not found']);
            }

            $newSize = $table1->size + $table2->size;
            $direction = 'H';
            
            // Layout logic from legacy
            if (abs($table1->position_x - $table2->position_x) == 100) {
                $direction = 'H';
            } elseif (abs($table1->position_y - $table2->position_y) == 100) {
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

    public function destroy(Request $request)
    {
        Table::where([
            'table_number' => $request->table_number,
            'branch_id' => $request->branch_id,
        ])->delete();

        return response()->json(['err' => 0, 'msg' => 'Table removed']);
    }
}
