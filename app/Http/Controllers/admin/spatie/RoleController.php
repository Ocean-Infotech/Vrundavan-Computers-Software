<?php

namespace App\Http\Controllers\admin\spatie;

use App\Helpers\Helper;
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

class RoleController extends Controller
{
    public $modules = [];
    public function __construct()
    {

        $this->modules = [
            'title' => 'Role',
            'folder_path' => 'software.module.spatie.role',
            'table_name' => (new Role())->getTable(),
            'route' => 'roles',
            'permisstion_prefix' => 'roles',
        ];

        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);

        View::share("permissions", Permission::get());
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try{

            $columns = [
                (object)[ 'data' => "name", 'name' => 'name' ],
                (object)[ 'data' => "users_count", 'name' => 'users count', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center' ],
                (object)[ 'data' => "action", 'name' => 'action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center' ],
			];
            View::share("columns", $columns);

            if($request->ajax()){
                $data = Role::withCount('users');

                $login_user_role = Helper::getLoginUserRole();
                if($login_user_role != "developer"){
                    $notInRole = ["developer"];

                    if($login_user_role == "super-admin"){
                        $notInRole[] = $login_user_role;
                    }
                    $data = $data->whereNotIn("name", $notInRole);
                }
                $editPermisstion = (Auth::user()->can($modules["permisstion_prefix"].'-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->can($modules["permisstion_prefix"].'-delete')) ? true : false;

                $returnData = Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search')) {
                        $query->where('name', 'like',"%".$request->search."%");
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
                ->rawColumns(['user_count','action'])
                ->make(true);
                // dd($returnData);
                return $returnData;
            }

            return view($modules['folder_path'].'.index', compact('modules'));
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'].'.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function create()
    {
        $modules = $this->modules;

        return view($modules['folder_path'].'.form', compact('modules'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            $modules = $this->modules;
            // return $request->all();
            $validated = $request->validate([
                'name' => 'required|unique:'.$modules['table_name'].'|max:255',
                'permission' => 'nullable',
            ]);

            $validated['created_by'] = Auth::user()?->id;
            $role = Role::create($validated);

            $getPermissions = Permission::whereIn('id', $request->permission)->get();
            if(count($getPermissions) > 0){
                $getPermissions = $getPermissions->pluck('name');
            }
            // return $getPermissions;
            $role->syncPermissions($getPermissions);
            // $role->syncPermissions($request->input('permission'));
            DB::commit();
            return Redirect::route($modules['route'].'.index')->withSuccess($modules['title'].' create successfully');
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        }
        catch(\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'].'.index')->withErrors($e->getMessage());
        }
    }

    public function show(string $id)
    {
        $modules = $this->modules;
        return Redirect::route($modules['route'].'.index');
    }

    /**
    * Show the form for editing the specified resource.
    */
    public function edit(string $id)
    {
        $modules = $this->modules;

        $edit = Role::findOrFail($id);
        // return Permission::get();
        // return
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
        ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
        ->all();

        return view($modules['folder_path'].'.form', compact('modules','edit','rolePermissions'));
    }


    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try{
            $modules = $this->modules;
            $request['id'] = $id;

            $validated = $request->validate([
                'id' => 'required|exists:'.$modules['table_name'].',id',
                'name' => 'required|unique:'.$modules['table_name'].',name,'.$id,
                'permission' => 'nullable',
                ]
            );

            $role = Role::find($id);
            $role->name = $request->input('name');
            $role->updated_by = Auth::user()?->id;
            $role->save();

            $getPermissions = Permission::whereIn('id', $request->permission)->get();
            if(count($getPermissions) > 0){
                $getPermissions = $getPermissions->pluck('name');
            }
            // return $getPermissions;
            $role->syncPermissions($getPermissions);
            // $role->syncPermissions($request->input('permission'));

            DB::commit();
            return Redirect::route($modules['route'].'.index')->withSuccess($modules['title'].' update successfully');

            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        }
        catch(\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'].'.index')->withErrors($e->getMessage());
        }

    }

    public function destroy(Request $request, string $id)
    {
        try {
            $modules = $this->modules;
            $dataDelete = Role::findOrFail($id);
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
            DB::rollBack();
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
