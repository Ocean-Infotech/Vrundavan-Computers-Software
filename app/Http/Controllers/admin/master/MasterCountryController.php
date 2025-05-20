<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class MasterCountryController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Country',
            'folder_path' => 'software.module.master.country',
            'route' => 'country',
            'table_name' => (new MasterCountry())->getTable(),
            'permisstion_prefix' => 'country',
        ];

        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-list', ['only' => ['index','show']]);
        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-create', ['only' => ['create','store']]);
        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:'.$this->modules['permisstion_prefix'].'-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        try{
            $columns = [
                (object)[ 'data' => "name", 'name' => 'name', 'td_label' => 'Name' ],
                (object)[ 'data' => "short_name", 'name' => 'short_name', 'td_label' => 'Short Name' ],
                (object)[ 'data' => "code", 'name' => 'code', 'td_label' => 'Code' ],
                (object)[ 'data' => "status", 'name' => 'status', 'td_label' => 'Status','className' =>  'w-5 text-center' ],
                (object)[ 'data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' =>  'w-10 text-center' ],
			];
            View::share("columns", $columns);

            $data = [];

            if($request->ajax()){
                // dd($request->all());
                $data = MasterCountry::select('*');
                // $editPermisstion = (Auth::user()->can($modules["table_name"].'-edit')) ? true : false;
                // $deletePermisstion = (Auth::user()->can($modules["table_name"].'-delete')) ? true : false;
                $editPermisstion = true;
                $deletePermisstion = true;

                $returnData = Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search')) {
                        $query->where('name', 'like',"%".$request->search."%");
                        $query->orWhere('short_name', 'like',"%".$request->search."%");
                    }
                })
                ->editColumn('status', function ($record) {
                    $status = '';
                    if($record?->status == "active"){
                        $status = '<span class="badge bg-success-subtle text-success text-uppercase">Active</span>';
                    }
                    else if($record?->status == "inactive"){
                        $status = '<span class="badge bg-danger-subtle text-danger text-uppercase">InActive</span>';
                    }else{
                        $status = '-';
                    }
                    return $status;
                })
                ->addColumn('action', function($row) use ($modules,$editPermisstion, $deletePermisstion){
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
                ->rawColumns(['status','action'])
                ->make(true);
                return $returnData;
            }
            return view($modules['folder_path'].'.index',compact('data'));
        } catch(\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        return view($modules['folder_path'].'.form',compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        // return $request->all();
        $validated = $request->validate([
            'name' => 'required|unique:'.$modules['table_name'].',name|max:255',
            'short_name' => 'required|unique:'.$modules['table_name'].',short_name|max:255',
            'code' => 'nullable',
        ]);

        try{
            $validated['created_by'] = Auth::user()->id;
            MasterCountry::create($validated);
            return Redirect::route('country.index')->withSuccess($modules['title'].' create successfully');
        } catch(\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
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
        try{
            $modules = $this->modules;
            View::share('modules', $modules);

            $edit = MasterCountry::findOrFail($id);
            return view($modules['folder_path'].'.form',compact('modules','edit'));
        } catch(\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }


    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        // return $request->all();
        $request['id'] = $id;

        $validated = $request->validate([
            'id' => 'required|exists:'.$modules['table_name'].',id',
            'name' => 'required|unique:'.$modules['table_name'].',name,'.$id.'|max:255',
            'short_name' => 'required|unique:'.$modules['table_name'].',short_name,'.$id.'|max:255',
            'code' => 'nullable',
        ]);
        try{
            $validated['updated_by'] = Auth::user()->id;
            // return $validated;
            $updateData = MasterCountry::findOrFail($id);
            if($updateData){
                unset($validated['id']);
                $updateData->update($validated);

                return Redirect::route('country.index')->withSuccess($modules['title'].' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch(\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $modules = $this->modules;
        $dataDelete = MasterCountry::findOrFail($id);
        if($dataDelete){
            $validated['deleted_by'] = Auth::user()->id;
            $dataDelete->update($validated);

            if($dataDelete->delete()){
                // return redirect()->route($modules['route'].'.index');
                return true;
            }
        }
        return false;
    }

    public function country_sync(Request $request){
        try{
            $modules = $this->modules;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries.json');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if($response){
                if(gettype($response) == "string"){
                    $response = json_decode($response);
                }
            }

            if(count($response) > 0){
                $message = "Up to date";
                foreach($response as $record){
                    $masterCountry = MasterCountry::where('name',$record->name)->first();
                    if(empty($masterCountry)){
                        $masterCountry = new MasterCountry();
                        $masterCountry->id = $record->id;
                    }

                    if($masterCountry){
                        $masterCountry->name = $record->name;
                        if(isset($record->iso3)){ $masterCountry->short_name = $record->iso3; }
                        if(isset($record->numeric_code)){ $masterCountry->code = $record->numeric_code; }
                        if(isset($record->latitude)){ $masterCountry->latitude = $record->latitude; }
                        if(isset($record->longitude)){ $masterCountry->longitude = $record->longitude; }
                        $masterCountry->save();

                        if(count($masterCountry->getChanges())){
                            $message = "Some ".$modules['title']." data add / update";
                        }
                    }
                    // dd("L-193", $record, $masterCountry, count($masterCountry->getChanges()));
                }
            }
            return Redirect::route('country.index')->withSuccess($message);
        } catch(\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }
}
