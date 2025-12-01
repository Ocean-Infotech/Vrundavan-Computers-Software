<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LibraryMasterBookRequest;
use App\Models\LibraryIssuedBooks;
use App\Models\LibraryMasterBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LibraryMasterBookController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Library Book Master',
            'folder_path' => 'software.module.master.library_book_master',
            'route' => 'library-book-master',
            'table_name' => (new LibraryMasterBook())->getTable(),
            'permission_prefix' => 'library-book-master',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have permission.', [], [], 403);
            }
            abort(403, 'User does not have permission.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => "book_info", 'name' => 'book_info', 'td_label' => 'Book Details'],
                (object)['data' => "library_book_no", 'name' => 'library_book_no', 'td_label' => 'Library Book No'],
                (object)['data' => "purchase_date", 'name' => 'purchase_date', 'td_label' => 'Purchase Date'],
                (object)['data' => "price", 'name' => 'price', 'td_label' => 'Price'],
                (object)['data' => "created_by", 'name' => 'created_by', 'td_label' => 'Entry Add By'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];


            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = LibraryMasterBook::withTrashed('createdByUser');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->editColumn('status', function ($row) use ($modules) {
                        $statusList = LibraryMasterBook::getStatusOptions();
                        $status = '';
                        $dropdown = '<ul class="dropdown-menu">';

                        // Button color logic
                        $btnClass = match ($row->status) {
                            'active' => 'btn-success',
                            'destroy' => 'btn-danger',
                            'not available' => 'btn-warning',
                            default => 'btn-secondary',
                        };

                        // Current Status Button
                        $status .= '<button type="button" class="btn ' . $btnClass . ' btn-sm dropdown-toggle" data-bs-toggle="dropdown">'
                            . ucfirst($row->status) . '</button>';

                        // Dynamic dropdown statuses
                        foreach ($statusList as $key => $value) {
                            $dropdown .= '
            <li>
                <a href="javascript:void(0)" class="dropdown-item update-status"
                    data-url="' . route($modules["route"] . '.status-update') . '"
                    data-id="' . $row["id"] . '" data-update_status="' . $key . '">' . $value . '</a>
            </li>';
                        }

                        $dropdown .= '</ul>';
                        $status .= $dropdown;

                        return $status;
                    })
                    ->editColumn('created_by', function ($row) {

                        return $row->createdByUser?->name ?? '';
                    })
                    ->addColumn('book_info', function ($row) {
                        $html = '<strong>' . e($row->book_name) . '</strong><br>';
                        $html .= '<small>Author: ' . e($row->author_name) . '</small><br>';
                        $html .= '<small>Publisher: ' . e($row->publisher_name) . '</small>';
                        return $html;
                    })


                    ->editColumn('purchase_date', function ($row) {
                        return !empty($row->purchase_date) ? \Carbon\Carbon::parse($row->purchase_date)->format('d-m-Y') : '-';
                    })

                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            if (Helper::directCan($modules['permission_prefix'] . '-edit')) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if (Helper::directCan($modules['permission_prefix'] . '-delete')) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action', 'book_info'])
                    ->make(true);
            }
            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(LibraryMasterBookRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        View::share('modules', $modules);

        try {
            $validated = $request->validated();
            $validated['created_by'] = Auth::user()->id;
            // dd($request->all(),$validated);
            if ($request->has('purchase_date')) {
                $validated['purchase_date'] = date('Y-m-d', strtotime($request->purchase_date));
            } elseif ($request->has('date')) {
                $validated['date'] = date('Y-m-d', strtotime($request->date));
            }
            LibraryMasterBook::create($validated);


            return redirect()->route('library-book-master.index')->withSuccess('Library Book Created Successfully!');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);
            $edit = LibraryMasterBook::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(LibraryMasterBookRequest $request, $id)
    {
        try {
            $update = LibraryMasterBook::findOrFail($id);

            $validated = $request->validated();
            // dd($request->all(),$validated);
            $validated['updated_by'] = Auth::user()->id;

            if ($request->has('purchase_date')) {
                $validated['purchase_date'] = date('Y-m-d', strtotime($request->purchase_date));
            } elseif ($request->has('date')) {
                $validated['date'] = date('Y-m-d', strtotime($request->date));
            }

            // Step 4: Update record safely
            $update->update($validated);

            // Step 5: Redirect with success message
            return redirect()
                ->route('library-book-master.index')
                ->withSuccess('Library Book Updated Successfully!');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')
                ->withErrors($e->getMessage());
        }
    }


    public function status_update(Request $request)
    {
        // Fetch all possible statuses dynamically from the model
        $validStatuses = array_keys(LibraryMasterBook::getStatusOptions());

        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('library_book_master', 'id')],
            'update_status' => ['required', Rule::in($validStatuses)],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $book = LibraryMasterBook::withTrashed()->findOrFail($request->id);
            $book->status = $request->update_status;
            $book->save();

            return $this->sendResponse($book, 'Status Updated Successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 401);
        }
    }
    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_delete']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have the right permissions.'
                ], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        $isAjax = $request->ajax();

        try {
            $course = LibraryMasterBook::findOrFail($id);

            // ✅ Use correct column names for all related modules
            $isUsed =


                LibraryIssuedBooks::where('book_id', $course->id)->exists();  // corrected




            if ($isUsed) {
                $message = 'This Books cannot be deleted because it is already assigned in others Data.';
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->route($modules['route'] . '.index')->withErrors($message);
            }

            // Soft delete
            $course->deleted_by = Auth::id();
            $course->save();
            $course->delete();

            $successMsg = $modules['title'] . ' deleted successfully.';
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => $successMsg]);
            }
            return redirect()->route($modules['route'] . '.index')->withSuccess($successMsg);
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $course = LibraryMasterBook::withTrashed()->findOrFail($id);


            $isUsed =
            

                LibraryIssuedBooks::where('book_id', $course->id)->exists();  // corrected


            if ($isUsed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This Books cannot be restored because it is already assigned in others data.'
                ], 400);
            }


            $course->restore();

            return response()->json([
                'status' => 'success',
                'message' => 'Books restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
