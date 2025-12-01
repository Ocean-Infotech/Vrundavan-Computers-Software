<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueBookRequest;
use App\Models\LibraryIssuedBooks;
use App\Models\LibraryMasterBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class IssueBookController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Issue Book',
            'folder_path' => 'software.module.master.issue_book',
            'route' => 'issue-book',
            'table_name' => (new LibraryIssuedBooks())->getTable(),
            'permission_prefix' => 'issue-book',
        ];

        View::share('modules', $this->modules);
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
            // ✅ Updated columns for Issue Book List
            $columns = [
                // (object)['data' => "book_name", 'name' => 'book_name', 'td_label' => 'Book Name'],
                // (object)['data' => "book_number", 'name' => 'book_number', 'td_label' => 'Book No'],
                (object)['data' => "book_info", 'name' => 'book_info', 'td_label' => 'Book Detail'],
                (object)['data' => "student_id", 'name' => 'student_id', 'td_label' => 'Student Id'],
                (object)['data' => "issue_return_dates", 'name' => 'issue_return_dates', 'td_label' => 'Date Detail'],
                (object)['data' => "issue_return_by", 'name' => 'issue_return_by', 'td_label' => 'User Detail'],
                // (object)['data' => "issue_date", 'name' => 'issue_date', 'td_label' => 'Issue Date'],
                // (object)['data' => "issue_by_name", 'name' => 'issue_by_name', 'td_label' => 'Issue By Name'],
                // (object)['data' => "return_date", 'name' => 'return_date', 'td_label' => 'Return Date'],
                // (object)['data' => "return_by_name", 'name' => 'return_by_name', 'td_label' => 'Return By Name'],
                // (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];

            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = LibraryIssuedBooks::with(['book', 'student'])
                    ->where(function ($query) {
                        $query->whereNull('return_date')
                            ->orWhere('return_date', '');
                    });

                // 🔹 Filter by Book
                if ($request->book_id) {
                    $bookInput = trim($request->book_id);

                    // Split text like "The Great Gatsby - LBM001"
                    $bookParts = explode(' - ', $bookInput);
                    $bookName = trim($bookParts[0] ?? '');
                    $bookCode = trim($bookParts[1] ?? '');

                    $data->whereHas('book', function ($q) use ($bookName, $bookCode, $bookInput) {
                        $q->where(function ($query) use ($bookName, $bookCode, $bookInput) {
                            if (!empty($bookName)) {
                                $query->where('book_name', 'like', '%' . $bookName . '%');
                            }

                            if (!empty($bookCode)) {
                                $query->orWhere('library_book_no', 'like', '%' . $bookCode . '%');
                            }

                            // Match combined display text (optional)
                            $query->orWhereRaw("CONCAT(book_name, ' - ', library_book_no) LIKE ?", ['%' . $bookInput . '%']);
                        });
                    });
                }

                // 🔹 Filter by Student
                if ($request->student_id) {
                    $data->whereHas('student', function ($q) use ($request) {
                        $q->where(\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $request->student_id . '%')
                            ->orWhere('id', 'like', '%' . $request->student_id . '%');
                    });
                }
                // 🔹 Filter by Date Range
                if ($request->start_date && $request->end_date) {
                    try {
                        $start = \Carbon\Carbon::createFromFormat('d/m/Y', trim($request->start_date))->startOfDay();
                        $end = \Carbon\Carbon::createFromFormat('d/m/Y', trim($request->end_date))->endOfDay();

                        $data->whereBetween('issued_date', [$start, $end]);
                    } catch (\Exception $e) {
                        // Invalid date format, ignore filter
                    }
                }



                return DataTables::of($data)
                    ->addIndexColumn()

                    ->addColumn('book_info', function ($row) {
                        $bookNo = $row->book?->library_book_no ?? '';
                        $bookName = $row->book?->book_name ?? '';
                        return $bookNo && $bookName ? "({$bookNo}) {$bookName}" : ($bookName ?: '-');
                    })

                    ->addColumn('issue_return_dates', function ($row) {
                        $issueDate = !empty($row->issued_date)
                            ? \Carbon\Carbon::parse($row->issued_date)->format('d-m-Y')
                            : '-';

                        $returnDate = !empty($row->return_date)
                            ? \Carbon\Carbon::parse($row->return_date)->format('d-m-Y')
                            : '-';

                        // return "<b>Issue:</b> {$issueDate}<br><b>Return:</b> {$returnDate}";
                        return "<b>Issue:</b> <span style='color:#333;'>{$issueDate}</span><br><b>Return:</b> <span style='color:#333;'>{$returnDate}</span>";
                    })

                    ->addColumn('issue_return_by', function ($row) {
                        $issueBy = $row->issuedByUser?->name ?? '-';
                        $returnBy = $row->returnedByUser?->name ?? '-';

                        return "<b>Issue by:</b> {$issueBy}<br><b>Return by:</b> {$returnBy}";
                    })



                    // ✅ Separate Book Number
                    ->addColumn('book_number', function ($row) {
                        return $row->book?->library_book_no ?? '-';
                    })
                    ->editColumn('library_book_no', function ($row) {
                        return $row->book?->library_book_no ?? '-';
                    })
                    ->editColumn('issue_by_name', function ($row) {

                        return $row->issuedByUser?->name ?? '-';
                    })
                    ->editColumn('return_by_name', function ($row) {
                        // dd($row->returnedByUser->name);
                        return $row->returnedByUser?->name;
                    })


                    ->editColumn('student_id', function ($row) {

                        $last   = $row->student?->last_name ?? '';
                        $first  = $row->student?->first_name ?? '';
                        $father = $row->student?->father_name ?? '';
                        $id     = $row->student?->id ?? '';

                        // Final formatted name: Last First Father
                        $fullName = trim("{$last} {$first} {$father}");

                        return $fullName ? "({$id}) {$fullName}" : '-';
                    })


                    ->editColumn('issue_date', function ($row) {
                        // dd($row->issued_date);
                        return !empty($row->issued_date)
                            ? \Carbon\Carbon::parse($row->issued_date)->format('d-m-Y')
                            : '';
                    })

                    ->editColumn('issue_by_name', function ($row) {
                        return $row->issuedBy?->name ?? '-';
                    })

                    ->editColumn('return_date', function ($row) {
                        return !empty($row->return_date)
                            ? \Carbon\Carbon::parse($row->return_date)->format('d-m-Y')
                            : '-';
                    })



                    ->editColumn('status', function ($row) {
                        return ucfirst($row->status ?? '-');
                    })

                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';

                        if (!$row?->deleted_at) {
                            // ✏️ Edit Button
                            if (Helper::directCan($modules['permission_prefix'] . '-edit')) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '"
                        class="btn btn-light btn-icon mx-1" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>';
                            }

                            // 🗑️ Delete Button
                            if (Helper::directCan($modules['permission_prefix'] . '-delete')) {
                                $btn .= '<a href="javascript:void(0)"
                        data-id="' . $row->id . '"
                        data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '"
                        class="btn btn-danger btn-icon deletebutton mx-1" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </a>';
                            }

                            // dd($modules['permission_prefix']);
                            // 🔄 Return Button
                            if (Helper::directCan($modules['permission_prefix'] . '-return')) {
                                $btn .= '<a href="' . route("issue-book.return", [$row["id"]]) . '"
                        class="btn btn-success btn-icon mx-1" title="Return Book">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>';
                            }
                        } else {
                            // ♻️ Restore Button
                            $btn .= '<a href="javascript:void(0)"
                    data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '"
                    class="btn btn-light mx-1 record-restore" title="Restore Record">
                    <i class="ti ti-history"></i> Restore
                </a>';
                        }

                        return $btn ?: '-';
                    })


                    ->rawColumns(['status', 'action', 'issue_return_dates', 'issue_return_by'])
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

    public function store(IssueBookRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        View::share('modules', $modules);

        try {
            $validated = $request->validated();
            $validated['created_by'] = Auth::user()->id;
            $validated['issued_by'] = Auth::user()->id;
            if (!empty($validated['book_id'])) {
                preg_match('/PVM-(\d+)/', $validated['book_id'], $match);
                if (!empty($match[1])) {
                    $book = LibraryMasterBook::where('library_book_no', 'PVM-' . $match[1])->first();
                    if ($book) {
                        $validated['book_id'] = $book->id;
                        $validated['book_name'] = $book->book_name;
                        $validated['library_book_no'] = $book->library_book_no;
                    }
                }
            }

            if (!empty($validated['student_id'])) {
                // Extract ID from "13 - NAME"
                preg_match('/^(\d+)/', $validated['student_id'], $match);
                if (!empty($match[1])) {
                    $validated['student_id'] = $match[1];
                }
            }

            // dd($request->all(),$validated);
            if ($request->has('issued_date')) {
                $validated['issued_date'] = date('Y-m-d', strtotime($request->issued_date));
            } elseif ($request->has('date')) {
                $validated['date'] = date('Y-m-d', strtotime($request->date));
            }
            LibraryIssuedBooks::create($validated);


            return redirect()->route($this->modules['route'] . '.index')->withSuccess('Library Book Created Successfully!');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $modules = $this->modules;
            View::share('modules', $modules);
            $edit = LibraryIssuedBooks::with(['book', 'student'])->findOrFail($id);
            if (!empty($edit->issued_date)) {
                $edit->issued_date = \Carbon\Carbon::parse($edit->issued_date)->format('Y-m-d');
            }
            // dd($edit);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(IssueBookRequest $request, $id)
    {
        try {
            $update = LibraryIssuedBooks::findOrFail($id);

            $validated = $request->validated();

            if (!empty($validated['book_id'])) {
                preg_match('/PVM-(\d+)/', $validated['book_id'], $match);
                if (!empty($match[1])) {
                    $book = LibraryMasterBook::where('library_book_no', 'PVM-' . $match[1])->first();
                    if ($book) {
                        $validated['book_id'] = $book->id;
                        $validated['book_name'] = $book->book_name;
                        $validated['library_book_no'] = $book->library_book_no;
                    }
                }
            }


            if (!empty($validated['student_id'])) {
                // Extract ID from "13 - NAME"
                preg_match('/^(\d+)/', $validated['student_id'], $match);
                if (!empty($match[1])) {
                    $validated['student_id'] = $match[1];
                }
            }

            // dd($request->all(),$validated);
            if ($request->has('issued_date')) {
                $validated['issued_date'] = date('Y-m-d', strtotime($request->issued_date));
            } elseif ($request->has('date')) {
                $validated['date'] = date('Y-m-d', strtotime($request->date));
            }
            // dd($request->all(),$validated);
            $validated['updated_by'] = Auth::user()->id;



            // dd($request->all(),$validated);
            if ($request->has('issued_date')) {
                $validated['issued_date'] = date('Y-m-d', strtotime($request->issued_date));
            } elseif ($request->has('date')) {
                $validated['date'] = date('Y-m-d', strtotime($request->date));
            }


            // Step 4: Update record safely
            $update->update($validated);

            // Step 5: Redirect with success message
            return redirect()->route($this->modules['route'] . '.index')->withSuccess('Library Book Created Successfully!');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')
                ->withErrors($e->getMessage());
        }
    }


    // public function status_update(Request $request)
    // {
    //     // Fetch all possible statuses dynamically from the model
    //     $validStatuses = array_keys(LibraryIssuedBooks::getStatusOptions());

    //     $validator = Validator::make($request->all(), [
    //         'id' => ['required', Rule::exists('library_book_master', 'id')],
    //         'update_status' => ['required', Rule::in($validStatuses)],
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
    //     }

    //     try {
    //         $book = LibraryIssuedBooks::withTrashed()->findOrFail($request->id);
    //         $book->status = $request->update_status;
    //         $book->save();

    //         return $this->sendResponse($book, 'Status Updated Successfully.');
    //     } catch (\Exception $e) {
    //         return $this->sendError($e->getMessage(), [], [], 401);
    //     }
    // }
    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $isAjax = $request->ajax();

        try {
            $dataDelete = LibraryIssuedBooks::findOrFail($id);

            // Soft delete logic
            $dataDelete->deleted_by = Auth::user()?->id;
            $dataDelete->save();

            if ($dataDelete->delete()) {
                if ($isAjax) {
                    return response()->json([
                        'success' => true,
                        'message' => $modules['title'] . ' deleted successfully.',
                    ]);
                }

                return redirect()->route($modules['route'] . '.index')
                    ->withSuccess($modules['title'] . ' deleted successfully.');
            }

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong, please try again later.',
                ]);
            }

            return redirect()->route($modules['route'] . '.index')
                ->withErrors('Something went wrong, please try again later.');
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $restore = LibraryIssuedBooks::withTrashed()->findOrFail($id);
            $restore->restore();

            return Redirect::back()->withSuccess('Library Book Restored Successfully!');
        } catch (\Exception $e) {
            return Redirect::route('library-book-master.index')->withErrors($e->getMessage());
        }
    }
    public function returnBook($id, Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        View::share('modules', $modules);
        try {
            $book = LibraryIssuedBooks::findOrFail($id);



            // $book->status = 'returned';
            $book->return_date = now();
            $book->return_by = Auth::id();
            $book->save();

            return redirect()->back()->with('success', 'Book returned successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
