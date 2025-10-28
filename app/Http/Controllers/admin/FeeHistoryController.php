<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\FeesCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class FeeHistoryController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Fee History',
            'folder_path' => 'software.module.fee-history',
            'route' => 'fee-history',
            'table_name' => (new FeesCollection())->getTable(),
            'permission_prefix' => 'fee-history',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have permission.'], 403);
            }
            abort(403, 'User does not have permission.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => "student_id", 'name' => 'student_id', 'td_label' => 'GR No'],
                (object)['data' => "student_name", 'name' => 'student_name', 'td_label' => 'Student Name'],
                (object)['data' => "course.course_name", 'name' => 'course_id', 'td_label' => 'Course Name'],
                (object)['data' => "fees", 'name' => 'fees', 'td_label' => 'Course Fees'],
                // (object)['data' => "mode", 'name' => 'mode', 'td_label' => 'Mode'],
                (object)['data' => "id", 'name' => 'id', 'td_label' => 'Receipt No'],
                (object)['data' => "checkbox", 'name' => 'checkbox', 'td_label' => 'Check Box'],

                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
            ];

            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = FeesCollection::with(['course'])
                    ->select('*')
                    ->withTrashed()
                    ->orderByDesc('id');

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->editColumn('date', function ($row) {
                        return $row->date ? Helper::convert_date($row->date, 'Y-m-d', 'd-m-Y') : '-';
                    })

                    ->addColumn('checkbox', function ($row) {
                        $checked = $row->checked_status == 1 ? 'checked' : '';
                        return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '" ' . $checked . '>';
                    })


                    ->rawColumns(['mode', 'checkbox'])
                    ->make(true);

                return $returnData;
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
   public function updateCheckedStatus(Request $request)
{
    try {
        $id = $request->id;
        $status = $request->status;

        $fee = FeesCollection::find($id);

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }

        $fee->checked_status = $status;
        $fee->save();

        $message = $status == 1 ? 'Fee record marked as checked successfully' : 'Fee record unmarked successfully';

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}
}
