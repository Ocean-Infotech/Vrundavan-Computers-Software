<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class SettingController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Setting',
            'folder_path' => 'setting',
            'route' => 'setting',
            'table_name' => (new Setting())->getTable(),
            'permisstion_prefix' => 'setting',
        ];

        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-list', ['only' => ['index', 'show']]);
        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-delete', ['only' => ['destroy']]);
    }

    public function setPointToRupees(Request $request)
    {
        $modules = $this->modules;
        $modules['title'] = 'Set point to rupees';
        $modules['key'] = 'point-to-rupees';
        View::share('modules', $modules);
        if ($request?->isMethod('post')) {
            $validated = $request->validate([
                'key' => 'required|in:point-to-rupees',
                'point' => 'required|numeric|min:1',
                'rupees' => 'required|numeric|min:0',
            ]);
        }
        try {
            $setting = Setting::where('key', $modules['key'])->first();
            if ($request?->isMethod('post')) {
                if (!$setting) {
                    $setting =  new Setting();
                    $setting->key = $modules['key'];
                    $setting->created_by = Auth::user()->id;
                } else {
                    $setting->updated_by = Auth::user()->id;
                }
                $values = [];
                $values['point'] = $request->point;
                $values['rupees'] = $request->rupees;
                $setting->value = json_encode($values);
                // return [$request->all(), $setting];
                $setting->save();
                Session::flash("success", "Set point to rupees value successfully.");
                return Redirect::route('set-point-to-rupees');
            }

            View::share('edit', $setting);
            // return $modules;
            // return $request->all();
            return view('admin.module.' . $modules['folder_path'] . '.setPointToRupees');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function SetMinWithdrawRequestAmount(Request $request)
    {
        $modules = $this->modules;
        $modules['title'] = 'Set Min Withdraw Request Amount';
        $modules['key'] = 'min-withdraw-request-amount';
        View::share('modules', $modules);
        if ($request?->isMethod('post')) {
            $validated = $request->validate([
                'key' => 'required|in:min-withdraw-request-amount',
                'min_amount' => 'required|numeric|min:1',
                'max_amount' => 'required|numeric|min:0',
            ]);
        }
        try {
            $setting = Setting::where('key', $modules['key'])->first();
            if ($request?->isMethod('post')) {
                if (!$setting) {
                    $setting =  new Setting();
                    $setting->key = $modules['key'];
                    $setting->created_by = Auth::user()->id;
                } else {
                    $setting->updated_by = Auth::user()->id;
                }
                $values = [];
                $values['min_amount'] = $request->min_amount;
                $values['max_amount'] = $request->max_amount;
                $setting->value = json_encode($values);
                // return [$request->all(), $setting];
                $setting->save();
                Session::flash("success", "Set point to rupees value successfully.");
                return Redirect::route('set-min-withdraw-request-amount');
            }

            View::share('edit', $setting);
            // return $modules;
            // return $request->all();
            return view('admin.module.' . $modules['folder_path'] . '.SetMinWithdrawRequestAmount');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
}
