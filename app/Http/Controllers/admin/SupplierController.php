<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Supplier',
            'folder_path' => 'software.module.supplier',
            'route' => 'supplier',
            'table_name' => (new Supplier())->getTable(),
            'permission_prefix' => 'supplier',
            'public_folder' => public_path(),
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $data = [];
            if ($request->ajax()) {
                $data = Supplier::select('*');

                $editPermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-edit');
                $deletePermission = Auth::user()->hasDirectPermission($modules["permission_prefix"] . '-delete');

                $returnData = DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && !empty($request->search['value'])) {
                            $searchValue = $request->search['value'];
                            $query->where(function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%")
                                  ->orWhere('category', 'like', "%{$searchValue}%")
                                  ->orWhere('city', 'like', "%{$searchValue}%")
                                  ->orWhere('mobile', 'like', "%{$searchValue}%");
                            });
                        }
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermission, $deletePermission) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        if ($editPermission) {
                            $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        }
                        if ($deletePermission) {
                            $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></a></div>';
                        }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);

                return $returnData;
            }

            $columns = [
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Name'],
                (object)['data' => "category", 'name' => 'category', 'td_label' => 'Category'],
                (object)['data' => "city", 'name' => 'city', 'td_label' => 'City'],
                (object)['data' => "mobile", 'name' => 'mobile', 'td_label' => 'Mobile'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'mobile' => 'nullable|digits:10',
        ]);

        DB::beginTransaction();
        try {
            Supplier::create([
                'name' => $request->name,
                'category' => $request->category,
                'city' => $request->city,
                'mobile' => $request->mobile,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $edit = Supplier::findOrFail($id);

            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'mobile' => 'nullable|digits:10',
        ]);

        try {
            $supplier = Supplier::findOrFail($id);

            $supplier->update([
                'name' => $request->name,
                'category' => $request->category,
                'city' => $request->city,
                'mobile' => $request->mobile,
                'updated_by' => Auth::id(),
            ]);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $supplier = Supplier::findOrFail($id);
            if ($supplier) {
                $supplier->deleted_by = Auth::id();
                $supplier->save();
                if ($supplier->delete()) {
                    return response()->json(true);
                }
            }
            return response()->json(false);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
