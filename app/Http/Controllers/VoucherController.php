<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController
{
    public function index()
    {
        return response()->json(['err' => 0, 'data' => Voucher::all()]);
    }

    public function show($id)
    {
        $voucher = Voucher::where('code', $id)->first();

        if (!$voucher) {
            return response()->json(['err' => 1, 'msg' => 'Voucher code is not valid.']);
        }

        if ($voucher->status === 'SOLD') {
            return response()->json(['err' => 1, 'msg' => 'Voucher code has been used.']);
        }

        return response()->json(['err' => 0, 'msg' => 'Voucher is valid.', 'data' => $voucher]);
    }

    public function store(Request $request)
    {
        $voucher = Voucher::create([
            'code' => $request->input('voucher-code'),
            'value' => $request->input('voucher-value'),
            'status' => 'ACTIVE',
            'expiry_date' => $request->input('expiry_date'),
        ]);

        return response()->json(['err' => 0, 'msg' => 'Voucher registered', 'data' => $voucher]);
    }
}
