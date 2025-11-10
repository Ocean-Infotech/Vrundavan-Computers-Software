<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MasterCityController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'City',
            'folder_path' => 'software.module.master.city',
            'route' => 'city',
            'table_name' => (new MasterCity())->getTable(),
            'permisstion_prefix' => 'city',
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
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Name'],
                // (object)[ 'data' => "short_name", 'name' => 'short_name', 'td_label' => 'Short Name' ],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            // dd(MasterCity::get());
            if ($request->ajax()) {
                // dd($request->all());
                $data = MasterCity::select('*');
                $editPermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-delete')) ? true : false;
                // $editPermisstion = true; $deletePermisstion = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        // dd($request->search_by_country);
                        if ($request->has('search_by_country') && $request->search_by_country) {
                            $query->where('country_id', $request->search_by_country);
                            // $query->where('country.name', 'like',"%".$request->search_by_country."%");
                        }
                        if ($request->has('search_by_state') && $request->search_by_state) {
                            $query->where('state_id', $request->search_by_state);
                            // $query->where('name', 'like',"%".$request->search_by_state."%");
                        }
                        if ($request->has('search_by_city') && $request->search_by_city) {
                            $query->where('name', 'like', "%" . $request->search_by_city . "%");
                        }
                        if ($request->has('search') && $request->search) {
                            $query->where('name', 'like', "%" . $request->search . "%");
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
        $validated = $request->validate([
            'country_id' => 'required|min:1',
            'state_id' => 'required|min:1',
            'name' => [
                'required',
                'max:255',
                Rule::unique($modules['table_name'])->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id)
                        ->whereNull('deleted_at'); // Only check non-deleted records
                }),
            ],
            'status' => 'required',
            // 'name' => 'required|unique:'.$modules['table_name'].',name|max:255',
        ]);
        try {

            return $validated;
            $validated['created_by'] = Auth::user()->id;
            MasterCity::create($validated);
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
            $edit = MasterCity::findOrFail($id);

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
            'country_id' => 'required|min:1',
            'state_id' => 'required|min:1',
            'name' => [
                'required',
                'max:255',
                Rule::unique($modules['table_name'])->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id)
                        ->whereNull('deleted_at'); // Only check non-deleted records
                })
                    ->ignore($id),
            ],
            'status' => 'required',
            // 'name' => 'required|unique:' . $modules['table_name'] . ',name,' . $id . '|max:255',
        ]);
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterCity::findOrFail($id);
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

    public function destroy(string $id)
    {
        $modules = $this->modules;
        $dataDelete = MasterCity::findOrFail($id);
        if ($dataDelete) {
            $validated['deleted_by'] = Auth::user()->id;
            $dataDelete->update($validated);

            if ($dataDelete->delete()) {
                // return Redirect::route($modules['route'].'.index');
                return true;
            }
        }
        return false;
    }

    public function auto_complete_city(Request $request)
    {
        $filter = [];
        if ($request->has('country_id')) {
            $filter['country_id'] = $request->country_id;
        }
        if ($request->has('state_id')) {
            $filter['state_id'] = $request->state_id;
        }
        if ($request->has('auto_complete_value')) {
            $filter['filter_by_name'] = $request->auto_complete_value;
        }

        $getCity = getCities($filter);
        return response()->json([
            'status' => true,
            'message' => 'State to city',
            'data' => $getCity,
        ]);
        return $getCity;
        return $request->all();
    }

    public function city_sync(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            set_time_limit(0);
            // ini_set('max_execution_time', 300); //3 minutes

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/cities.json');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                if (gettype($response) == "string") {
                    $response = json_decode($response);
                }
            }

            dd("NR-232", $response);
            if (count($response) > 0) {
                $message = "Up to date";
                foreach ($response as $record) {
                    if (isset($record->name) && isset($record->country_id) && isset($record->state_id)) {
                        $masterCity = MasterCity::where('name', $record->name)->where('country_id', $record->country_id)->where('state_id', $record->state_id)->first();

                        if (empty($masterCity)) {
                            $masterCity = new MasterCity();
                        }

                        if ($masterCity) {
                            $masterCity->name = $record->name;
                            $masterCity->short_name = makeSlug($record->name);
                            $masterCity->country_id = $record->country_id;
                            $masterCity->state_id = $record->state_id;

                            if (isset($record->latitude)) {
                                $masterCity->latitude = $record->latitude;
                            }
                            if (isset($record->longitude)) {
                                $masterCity->longitude = $record->longitude;
                            }
                            // dd("NR-232", $record, $masterCity);
                            $masterCity->save();
                        }

                        if (count($masterCity->getChanges())) {
                            $message = "Some " . $modules['title'] . " data add / update";
                        }
                    }
                    // dd("NR-249", $record, $masterCity, count($masterCity->getChanges()));
                }
            }
            return Redirect::route($modules['route'] . '.index')->withSuccess($message);
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
