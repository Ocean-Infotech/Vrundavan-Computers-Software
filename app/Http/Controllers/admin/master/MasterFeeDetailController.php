<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterFeeDetail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class MasterFeeDetailController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Fee',
            'folder_path' => 'software.module.master.fee',
            'route' => 'fees',
            'table_name' => (new MasterFeeDetail())->getTable(),
            'permission_prefix' => 'fee',
        ];
    }

    public function index()
    {
        $modules = $this->modules;
        $fees = MasterFeeDetail::all();
        View::share('modules', $modules);

        return view($this->modules['folder_path'] . '.index', compact('fees'));
    }

    // Store new fee
    public function store(Request $request)
    {
        $request->validate([
            'fee_name' => 'required|string',
            'amount'   => 'required|numeric',
        ]);

        try {
            MasterFeeDetail::create([
                'fee_name'   => $request->fee_name,
                'amount'     => $request->amount,
                'created_by' => Auth::id(),
                'status'     => 'active',
            ]);

            $fees = MasterFeeDetail::orderBy('id', 'desc')->get();


            if ($request->ajax()) {
                return response()->json(['success' => true, 'fees' => $fees]);
            }

            return Redirect::route('fees.index')->withSuccess('Fee created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return Redirect::route('fees.index')->withErrors($e->getMessage());
        }
    }

    // Update existing fee
    public function update(Request $request, $id)
    {
        $request->validate([
            'fee_name' => 'required|string',
            'amount'   => 'required|numeric',
        ]);

        try {
            $fee = MasterFeeDetail::findOrFail($id);
            $fee->update([
                'fee_name' => $request->fee_name,
                'amount'   => $request->amount,
            ]);

            $fees = MasterFeeDetail::orderBy('id', 'desc')->get();


            return response()->json(['success' => true, 'fees' => $fees]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    // Delete fee
    public function destroy($id, Request $request)
    {
        try {
            $fee = MasterFeeDetail::findOrFail($id);
            $fee->delete();

            $fees = MasterFeeDetail::orderBy('id', 'desc')->get();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'fees' => $fees]);
            }

            return redirect()->route('fees.index')->withSuccess('Fee deleted successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return redirect()->route('fees.index')->withErrors($e->getMessage());
        }
    }
}
