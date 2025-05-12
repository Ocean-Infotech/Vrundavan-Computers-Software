<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Dashboard',
            'folder_path' => 'admin',
            'route' => '',
            'table_name' => '',
            'permisstion_prefix' => '',
        ];

        // $this->middleware('permission:permissions-list|permissions-create|permissions-edit|permissions-delete', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        // $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }

    public function dashboard(Request $request)
    {
        $modules = $this->modules;
        $modules['folder_path'] = 'dashboard';
        View::share('modules', $modules);
        return view('admin.module.' . $modules['folder_path'], compact('modules'));
        return view('admin.module.dashboard');
    }
}
