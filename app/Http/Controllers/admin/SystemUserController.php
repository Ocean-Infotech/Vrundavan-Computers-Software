<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class SystemUserController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'System Users',
            'folder_path' => 'software.module.system-user',
            'route' => 'system-user',
            'table_name' => (new User())->getTable(),
            'permission_prefix' => 'system-user',
            // 'public_folder' => public_path(Redemption::$folderPath),
            'public_folder' => public_path(),
        ];


        $roles = Role::where('guard_name', 'api')->get();
        // $roles = Helper::getApplicationUserRoles();
        View::share('roles', $roles);
    }

    public function index(Request $request)
    {

        $roles = Helper::getAfterAuthRole();
        View::share('roles', $roles);

        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_list']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => "name", 'name' => 'name'],
                (object)['data' => "email", 'name' => 'email'],
                (object)['data' => "phone", 'name' => 'phone'],
                (object)['data' => "date_of_birth", 'name' => 'birth date'],
                (object)['data' => "role_name", 'name' => 'role', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
                (object)['data' => "status", 'name' => 'status'],
                (object)['data' => "action", 'name' => 'action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            if (!$modules['permission_edit'] && !$modules['permission_delete']) {
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'action';
                });
            }
            // $columns = array_values($columns);
            View::share("columns", $columns);

            $data = [];

            $login_user_role = Helper::getLoginUserRole();
            $login_user_role = strtolower($login_user_role);

            if ($request->ajax()) {

                $data = User::with(['roles']);

                if ($login_user_role != "developer") {

                    $data = $data->whereNull('deleted_by')->whereNull('deleted_at');

                    $data = $data->whereHas("roles", function ($q) use ($login_user_role) {
                        if (count(Helper::getAfterAuthRole()) > 0) {
                            $q->whereIn(DB::raw('LOWER(name)'), Helper::getAfterAuthRole()->pluck('name')->toArray());
                        }
                        $q->where("name", "!=", 'developer');
                        $q->where("name", "!=", $login_user_role);
                    });
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
                    ->editColumn('date_of_birth', function ($record) {
                        return $record->date_of_birth ? \Carbon\Carbon::parse($record->date_of_birth)->format('d-m-Y') : '-';
                    })
                    ->addColumn('role_name', function ($record) {
                        return ucfirst($record?->roles?->first()?->name);
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '<div class="d-flex gap-2 justify-content-center">';
                        // $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                        // if ($editPermisstion || $deletePermisstion) {

                        if ($modules['permission_edit'] && !$row->deleted_at) {
                            $btn .= '<div class="edit"><a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-info btn-icon mr-2"><i class="bx bx-edit-alt"></i></a></div>';
                        }
                        if ($modules['permission_delete'] && !$row->deleted_at) {
                            $btn .= '<div class="remove"><a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton"><i class="bx bx-trash"></i></i></a></div>';
                        }
                        // } else {
                        // $btn .= "-";
                        // }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
            return view($modules['folder_path'] . '.index', compact('data'));
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
            //throw $th;
        }
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        try {
            $roles = Helper::getAfterAuthRole();
            View::share('roles', $roles);

            return view($modules['folder_path'] . '.form');
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }


    public function store(UserRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        try {

            $input = $request->all();
            $input['sp'] = Str::random(4) . $input['password'];
            $input['password'] = Hash::make($input['password']);
            $input['created_by'] = Auth::user()->id;

            // dd($request->all(),$input);
            $user = User::create($input);
            $user->assignRole($request->input('role'));

            // Save only selected permissions for this user (if any are chosen)
            if ($request->directPermission) {
                $user->syncPermissions($request->directPermission);
            }

            return Redirect::route($modules['route'] . '.index')->with('success', $modules['title'] . ' create successfully.');
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage())->withInput();
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
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        try {
            $roles = Helper::getAfterAuthRole();
            View::share('roles', $roles);

            $edit = User::with('roles')->findOrFail($id);



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
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        DB::beginTransaction();
        try {
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
            return Redirect::route('software.dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        try {
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
            return Redirect::route('software.dashboard')->withErrors($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
