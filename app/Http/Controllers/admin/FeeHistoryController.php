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

            // $feesQuery = FeesCollection::with(['course'])
            //     ->orderByDesc('date')
            //     ->orderByDesc('id');
            $feesQuery = FeesCollection::with(['course', 'admission'])
                ->whereHas('registration', function ($q) {
                    $q->whereNotIn('status', ['Cancel', 'cancel']);
                })
                ->orderByDesc('date')
                ->orderByDesc('id');
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $feesQuery->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', '%' . $search . '%')
                        ->orWhere('student_name', 'LIKE', '%' . $search . '%');
                });
            }

            $paginatedData = $feesQuery->paginate(100);
            foreach ($paginatedData as $item) {

                $first  = $item->admission->last_name ?? '';
                $father = $item->admission->first_name ?? '';
                $last   = $item->admission->father_name ?? '';

                $item->student_name = trim("$father $first   $last ");
            }
            $groupedData = $paginatedData->getCollection()->groupBy(function ($item) {
                return $item->date ? Helper::convert_date($item->date, 'Y-m-d', 'd-m-Y') : 'No Date';
            });

            $paginatedData->setCollection($groupedData);

            return view($modules['folder_path'] . '.index', [
                'data' => $paginatedData,
                'modules' => $modules
            ]);
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
