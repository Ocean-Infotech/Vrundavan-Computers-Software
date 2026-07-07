<?php

namespace App\Http\Controllers\admin\spatie;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;


class PermissionController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Permission',
            'folder_path' => 'software.module.spatie.permission',
            'route' => 'permissions',
            'table_name' => 'permissions',
            'permisstion_prefix' => 'permissions',
        ];

        // $this->middleware('permission:permissions-list|permissions-create|permissions-edit|permissions-delete', ['only' => ['index','show']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;

        View::share('modules', $modules);
        try {
            $columns = [
                (object)[ 'data' => "id", 'name' => 'id' ],
                (object)[ 'data' => "group", 'name' => 'group' ],
                (object)[ 'data' => "name", 'name' => 'name' ],
                (object)[ 'data' => "action", 'name' => 'action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center' ],
			];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $editPermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-delete')) ? true : false;

                // dd($request->all());

                $data = Permission::query();
                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        $searchValue = $request->input('search.value', $request->search);
                        if (is_array($searchValue)) {
                            $searchValue = $searchValue['value'] ?? '';
                        }
                        $searchValue = trim((string) $searchValue);

                        if ($searchValue !== '') {
                            $query->where(function ($subQuery) use ($searchValue) {
                                $subQuery->where('name', 'like', '%' . $searchValue . '%')
                                    ->orWhere('group', 'like', '%' . $searchValue . '%');
                            });
                        }
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        // $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                        if ($editPermisstion) {
                            $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        }
                        if ($deletePermisstion) {
                            $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></i></a></div>';
                        }
                        $btn .= '</div>';
                        return $btn;


                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
                return $returnData;
            }

            return view($modules['folder_path'] . '.index', compact('modules'));
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function create()
    {
        $modules = $this->modules;
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        // return $request->all();
        $validated = $request->validate([
            'group' => 'required|max:255',
            'name' => 'required|unique:' . $modules['table_name'] . '|max:255',
        ]);

        // Permission::create($validated);

        $newPermison = new Permission();
        $newPermison->group = $request?->group ?? '';
        $newPermison->name = $request?->name;
        $newPermison->guard_name = $request?->guard_name ?? 'web';
        $newPermison->created_by = Auth::user()?->id;
        $newPermison->save();

        $role = Role::where('name', 'Developer')->first();
        if ($role) {
            $permissions = Permission::pluck('id', 'id')->all();

            $role->syncPermissions($permissions);
        }
        return redirect()->route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        return $validated;
    }

    public function show(string $id)
    {
        $modules = $this->modules;
        return redirect()->route($modules['route'] . '.index');
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $edit = Permission::findOrFail($id);
        return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
    }


    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        $request['id'] = $id;
        // return $request->all();
        $validated = $request->validate([
            'id' => 'required|exists:' . $modules['table_name'] . ',id',
            'group' => 'required|max:255',
            'name' => 'required|unique:' . $modules['table_name'] . ',name,' . $id . '|max:255',
        ]);
        $update = Permission::findOrFail($id);
        if ($update) {
            unset($validated['id']);
            $validated['updated_by'] = Auth::user()?->id;
            $update->update($validated);
            return redirect()->route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
        }
        return redirect()->back()->withErrors('something went wrong please try again later')->withInput();
        return $validated;
    }

    public function destroy(Request $request, string $id)
    {

        $modules = $this->modules;
        try {
            $dataDelete = Permission::findOrFail($id);
            if ($dataDelete) {
                $dataDelete->deleted_by = Auth::user()?->id;
                $dataDelete->deleted_at = Carbon::now();
                $dataDelete->save();
                if ($request->ajax()) {
                    return true;
                    if ($dataDelete->delete()) {
                        // return Redirect::route($modules['route'].'.index');
                        return true;
                    }
                    return false;
                }
                if ($dataDelete->delete()) {
                    return Redirect::route($modules['route'] . '.index');
                }
            }
            return Redirect::back()->withErrors('something went wrong please try again later');

        } catch (\InvalidArgumentException $e) {
            // DB::rollBack();
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            // DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
