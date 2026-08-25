<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class ApplicationUserController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Application Users',
            'folder_path' => 'software.modules.application-user',
            'route' => 'application-user',
            'table_name' => (new User())->getTable(),
            'permisstion_prefix' => 'application-user',
            // 'public_folder' => public_path(Redemption::$folderPath),
            'public_folder' => public_path(),
        ];

        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-delete', ['only' => ['destroy']]);


        $roles = Role::where('guard_name', 'api')->get();
        $roles = Helper::getApplicationUserRoles();
        View::share('roles', $roles);
    }

    public function index(Request $request)
    {

        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $data = [];

            $login_user_role = Helper::getLoginUserRole();
            // return Role::where('guard_name', 'api')->get()->pluck('name')->toArray();

            if ($request->ajax()) {

                $editPermisstion = (Auth::user()->hasDirectPermission($modules["permisstion_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->hasDirectPermission($modules["permisstion_prefix"] . '-delete')) ? true : false;

                $data = User::with(['roles']);
                /*
                if ($login_user_role == "super-admin") {
                    $data = $data->whereHas("roles", function ($q) {
                        $q->whereNotIn("name", ["developer", "super-admin"]);
                    });
                } else if ($login_user_role != "developer") {
                    $data = $data->whereHas("roles", function ($q) {
                        $q->whereNotIn("name", ["developer"]);
                    });
                }
                */
                $data = $data->whereHas("roles", function ($q) {
                    $q->whereIn("name", Helper::getApplicationUserRoles()->pluck('name')->toArray());
                });
                if (strtolower($login_user_role) != "developer") {
                    $data = $data->whereNull('deleted_by')->whereNull('deleted_at');
                }
                $data = $data->orderBy('id', 'DESC');
                // return $data;

                return DataTables::of($data)
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('name', 'like', "%" . $request->search . "%");
                            // $query->orWhere('email', 'like',"%".$request->search."%");
                        }
                        if ($request->has('status') && !empty($request->status)) {
                            $query->where('status', $request->status);
                        }
                        if ($request->has('role') && !empty($request->role)) {
                            $search_role = $request->role;
                            $query->whereHas("roles", function ($q) use ($search_role) {
                                return $q->where('id', $search_role);
                            });
                        }
                    })
                    ->addIndexColumn()
                    ->editColumn('status', function ($record) {
                        $status = '';
                        if ($record?->status == "active") {
                            $status = '<span class="badge bg-success-subtle text-success text-uppercase">Active</span>';
                        } else if ($record?->status == "inactive") {
                            $status = '<span class="badge bg-danger-subtle text-danger text-uppercase">InActive</span>';
                        } else {
                            $status = '-';
                        }
                        return $status;
                    })
                    ->addColumn('role_name', function ($record) {
                        return ucfirst($record?->roles?->first()?->name);
                    })
                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        // $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                        if ($editPermisstion || $deletePermisstion) {

                            if ($editPermisstion) {
                                $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                            }
                            if ($deletePermisstion) {
                                $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></i></a></div>';
                            }
                        } else {
                            $btn .= "-";
                        }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            $columns = [
                (object)['data' => "name", 'name' => 'name'],
                (object)['data' => "email", 'name' => 'email'],
                (object)['data' => "phone", 'name' => 'phone'],
                (object)['data' => "upi", 'name' => 'upi'],
                (object)['data' => "role_name", 'name' => 'role', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
                (object)['data' => "status", 'name' => 'status'],
                (object)['data' => "action", 'name' => 'action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];

            $columns = array_values($columns);
            View::share("columns", $columns);

            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
            //throw $th;
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $roles = Helper::getApplicationUserRoles();
            View::share('roles', $roles);

            return view($modules['folder_path'] . '.form');
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }


    public function store(UserRequest $request)
    {
        try {
            $modules = $this->modules;

            $input = $request->all();
            $input['sp'] = Str::random(4) . $input['password'];
            $input['password'] = Hash::make($input['password']);
            $input['created_by'] = Auth::user()->id;

            // dd($request->all(),$input);
            $user = User::create($input);
            $user->assignRole($request->input('role'));

            return Redirect::route($modules['route'] . '.index')->with('success', $modules['title'] . ' create successfully.');
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
        return Redirect::back()->withErrors('Something went wrong')->withInput();
    }


    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $edit = User::with('roles')->findOrFail($id);


            $roles = Helper::getApplicationUserRoles();
            View::share('roles', $roles);
            $selectedRole = "";
            if (count($edit->roles) > 0) {
                $selectedRole = $edit->roles->pluck('id')->first();
            }
            // return $edit->roles->pluck('id')->first();
            return view($modules['folder_path'] . '.form', compact('edit', 'selectedRole'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $modules = $this->modules;
            $updateData = $request->all();

            unset($updateData['password']);

            // return $request->all();
            // $user = User::findOrFail($id);
            $user = User::findOrFail($id);
            if ($user) {
                $user->name = $request?->name;
                $user->email = $request?->email;
                $user->phone = $request?->phone;
                $user->biometric_id = $request?->biometric_id;
                $user->date_of_birth = $request?->date_of_birth;

                if ($request->password && !empty($request->password)) {
                    $user->password = Hash::make($request->password);
                    $user->sp = Str::random(4) . $request?->password;
                }
                $user->upi = $request?->upi;
                $user->status = $request?->status;
                $user->updated_by = Auth::user()->id;
                $user->save();

                $user->syncRoles($request->input('role'));

                if ($request->directPermission) {
                    $user->syncPermissions($request->directPermission);
                }

                DB::commit();

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $modules = $this->modules;
            $dataDelete = User::findOrFail($id);
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
            return Redirect::route('dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
