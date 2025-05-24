<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MasterStateController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'State',
            'folder_path' => 'software.module.master.state',
            'route' => 'state',
            'table_name' => (new MasterState())->getTable(),
            'permisstion_prefix' => 'state',
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
                (object)['data' => "master_state_name", 'name' => 'master_state_name', 'td_label' => 'Name'],
                // (object)[ 'data' => "short_name", 'name' => 'short_name', 'td_label' => 'Short Name' ],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' =>  'w-5 text-center'],
                (object)['data' => "action", 'name' => 'action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center'],
            ];
            View::share("columns", $columns);

            $data = [];

            // return MasterState::first();
            if ($request->ajax()) {
                // dd($request->all());
                // $data = MasterState::with(['country:id as country_id,name as country_name'])->select('*');
                $data = MasterState::with(['country'])->select('master_states.*', 'master_states.name as master_state_name');

                $editPermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-edit')) ? true : false;
                $deletePermisstion = (Auth::user()->can($modules["permisstion_prefix"] . '-delete')) ? true : false;
                // $editPermisstion = true; $deletePermisstion = true;

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search) {
                            $query->where('master_states.name', 'like', "%" . $request->search . "%");
                            // $query->orWhere('short_name', 'like',"%".$request->search."%");
                        } else {
                            // dd($request->search_by_country);
                            if ($request->has('search_by_country') && $request->search_by_country) {
                                $query->where('country_id', $request->search_by_country);
                                // $query->where('master_country.name', 'like',"%".$request->search_by_country."%");
                            }
                            if ($request->has('search_by_state') && $request->search_by_state) {
                                $query->where('master_states.name', 'like', "%" . $request->search_by_state . "%");
                            }
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
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        // return $request->all();

        $validated = $request->validate([
            'country_id' => 'required|min:1|exists:' . (new MasterCountry())->getTable() . ',id',
            'name' => [
                'required',
                'max:255',
                Rule::unique($modules['table_name'])->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id)
                        ->whereNull('deleted_at'); // Only check non-deleted records
                }),
            ],
            'short_name' => 'nullable|string',
            'status' => 'required',
        ]);
        // 'unique:'.$modules['table_name'].',name'],
        try {
            $validated['created_by'] = Auth::user()->id;
            MasterState::create($validated);
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
            $edit = MasterState::findOrFail($id);
            $countries = MasterCountry::get();
            return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'countries'));
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
            'name' => 'required|unique:' . $modules['table_name'] . ',name,' . $id . '|max:255',
            'country_id' => 'required|min:1|exists:' . (new MasterCountry())->getTable() . ',id',
            'name' => [
                'required',
                'max:255',
                Rule::unique($modules['table_name'])
                    ->where(function ($query) use ($request) {
                        return $query->where('country_id', $request->country_id)
                            ->whereNull('deleted_at'); // Only check non-deleted records
                    })
                    ->ignore($id) // Ignore current record during update
                ,
            ],
            'short_name' => 'nullable|string',
            'status' => 'required',
        ]);
        try {
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterState::findOrFail($id);
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
        $dataDelete = MasterState::findOrFail($id);
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

    public function getStateByCountryId(Request $request)
    {
        $getState = getStates(['country_id' => $request->country_id]);
        return response()->json([
            'status' => true,
            'message' => 'Country to state',
            'data' => $getState,
        ]);
        return $getState;
        return $request->all();
    }

    public function state_sync(Request $request)
    {
        try {
            $modules = $this->modules;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/states.json');
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
            // dd("NR-208", $response);
            if (count($response) > 0) {
                $message = "Up to date";
                foreach ($response as $record) {
                    $masterState = MasterState::where('name', $record->name)->where('country_id', $record->country_id)->first();

                    if (empty($masterState)) {
                        $masterState = new MasterState();
                        $masterState->id = $record->id;
                    }

                    if ($masterState) {
                        if (isset($record->name)) {
                            $masterState->name = $record->name;
                        }
                        if (isset($record->country_id)) {
                            $masterState->country_id = $record->country_id;
                        }
                        if (isset($record->state_code)) {
                            $masterState->short_name = $record->state_code;
                        }
                        if (isset($record->latitude)) {
                            $masterState->latitude = $record->latitude;
                        }
                        if (isset($record->longitude)) {
                            $masterState->longitude = $record->longitude;
                        }
                        $masterState->save();
                    }

                    if (count($masterState->getChanges())) {
                        $message = "Some " . $modules['title'] . " data add / update";
                    }
                    // dd("L-193", $record, $masterState, count($masterState->getChanges()));
                }
            }
            return Redirect::route($modules['route'] . '.index')->withSuccess($message);
        } catch (Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }
}
