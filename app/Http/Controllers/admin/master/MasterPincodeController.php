<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterPincode;
use App\Models\Master\MasterState;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class MasterPincodeController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Pincode',
            'folder_path' => 'software.module.master.pincode',
            'route' => 'pincode',
            'table_name' => (new MasterPincode())->getTable(),
            'permisstion_prefix' => 'pincode',
        ];
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:' . $this->modules['permisstion_prefix'] . '-delete', ['only' => ['destroy']]);

        $countries = MasterCountry::get();
        View::share('countries', $countries);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try {
            $columns = [
                (object)['data' => "country_name", 'name' => 'country_id', 'td_label' => 'Country'],
                (object)['data' => "state_name", 'name' => 'state_id', 'td_label' => 'State'],
                (object)['data' => "city_name", 'name' => 'city_id', 'td_label' => 'City'],
                (object)['data' => "pincode", 'name' => 'pincode', 'td_label' => 'Pincode'],
                // (object)[ 'data' => "short_name", 'name' => 'short_name', 'td_label' => 'Short Name' ],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            // dd(MasterCity::get());
            if ($request->ajax()) {
                // dd($request->all());
                $data = MasterPincode::select('*');
                $editPermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-delete')) ? true : false;
                // $editPermisstion = true; $deletePermisstion = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        // dd($request->search_by_country);
                        if ($request->has('search_by_country') && $request->search_by_country) {
                            $query->where('country_id', $request->search_by_country);
                        }
                        if ($request->has('search_by_state') && $request->search_by_state) {
                            $query->where('state_id', $request->search_by_state);
                        }
                        if ($request->has('search_by_city') && $request->search_by_city) {
                            $query->where('city_id', $request->search_by_city);
                        }
                        if ($request->has('search') && $request->search) {
                            $query->where('pincode', 'like', "%" . $request->search . "%");
                        }
                    })
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
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
                return $returnData;
            }
            return view($modules['folder_path'] . '.index', compact('data'));
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

    public function store(Request $request)
    {
        $modules = $this->modules;
        // return $request->all();
        $validated = $request->validate([
            'country_id' => 'required|exists:' . (new MasterCountry())->getTable() . ',id',
            'state_id' => 'required|min:1|exists:' . (new MasterState())->getTable() . ',id',
            'city_id' => 'required|min:1|exists:' . (new MasterCity())->getTable() . ',id',
            'pincode' => [
                'required',
                'digits:6', // Ensures it is exactly 6 digits long (for India)
                'numeric', // Ensures only numeric values are entered
                Rule::unique($modules['table_name'])->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id)
                        ->where('state_id', $request->state_id)
                        ->where('city_id', $request->city_id)
                        ->whereNull('deleted_at'); // Only check non-deleted records
                }),
            ],
            'status' => 'required|in:active,inactive',
        ]);
        try {

            $validated['created_by'] = Auth::user()->id;
            // return $validated;
            MasterPincode::create($validated);
            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        try {

            $modules = $this->modules;
            $edit = MasterPincode::findOrFail($id);

            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validate([
            'id' => 'required|exists:' . $modules['table_name'] . ',id',
            'country_id' => 'required|exists:' . (new MasterCountry())->getTable() . ',id',
            'state_id' => 'required|min:1|exists:' . (new MasterState())->getTable() . ',id',
            'city_id' => 'required|min:1|exists:' . (new MasterCity())->getTable() . ',id',
            'pincode' => [
                'required',
                'digits:6', // Ensures it is exactly 6 digits long (for India)
                'numeric', // Ensures only numeric values are entered
                Rule::unique($modules['table_name'])->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id)
                        ->where('state_id', $request->state_id)
                        ->where('city_id', $request->city_id)
                        ->whereNull('deleted_at'); // Only check non-deleted records
                })->ignore($id),
            ],
            'status' => 'required|in:active,inactive',
        ]);
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterPincode::findOrFail($id);
            if ($updateData) {
                unset($validated['id']);
                $updateData->update($validated);

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {

        try {
            $modules = $this->modules;
            $dataDelete = MasterPincode::findOrFail($id);
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
