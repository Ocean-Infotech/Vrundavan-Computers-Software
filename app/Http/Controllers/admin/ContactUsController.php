<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ContactUsBranch;
use App\Models\ContactUsInfo;
use App\Models\ContactUsNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class ContactUsController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Contact Us',
            'folder_path' => 'software.module.contact-us',
            'route' => 'contact-us',
            'table_name' => (new ContactUsBranch())->getTable(),
            'permission_prefix' => 'contact-us',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object) ['data' => 'name', 'name' => 'name', 'td_label' => 'Branch Name'],
                (object) ['data' => 'address', 'name' => 'address', 'td_label' => 'Address'],
                (object) ['data' => 'email', 'name' => 'email', 'td_label' => 'Email'],
                (object) ['data' => 'status', 'name' => 'status', 'td_label' => 'Status'],
                (object) ['data' => 'sort_order', 'name' => 'sort_order', 'td_label' => 'Order'],
                (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share('columns', $columns);

            if ($request->ajax()) {
                $data = ContactUsBranch::query()->orderBy('sort_order')->orderByDesc('id');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        $search = $request->input('search');
                        if (is_array($search)) {
                            $search = $search['value'] ?? '';
                        }
                        $search = trim((string) $search);
                        if ($search === '') {
                            return;
                        }
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('status', 'like', "%{$search}%");
                        });
                    }, true)
                    ->editColumn('address', fn ($row) => $row->address ?: '-')
                    ->editColumn('email', fn ($row) => $row->email ?: '-')
                    ->editColumn('status', function ($row) {
                        if ($row->status === 'active') {
                            return '<span class="badge bg-success">Active</span>';
                        }

                        return '<span class="badge bg-secondary">Inactive</span>';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if ($modules['permission_edit']) {
                            $btn .= '<a href="' . route($modules['route'] . '.edit', [$row->id]) . '" class="btn btn-light btn-icon mx-1" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules['route'] . '.destroy', [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1" title="Delete"><i class="fa-solid fa-trash"></i></a>';
                        }

                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            $info = ContactUsInfo::current();
            $numbers = ContactUsNumber::query()->orderBy('sort_order')->orderBy('id')->get();

            return view($modules['folder_path'] . '.index', compact('modules', 'info', 'numbers'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function updateInfo(Request $request)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }

        $validated = $request->validate([
            'group_title' => ['required', 'string', 'max:255'],
        ]);

        $info = ContactUsInfo::current();
        $validated['updated_by'] = Auth::id();
        $info->update($validated);

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Group title updated successfully.');
    }

    public function createNumber()
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-create')) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        return view($modules['folder_path'] . '.number-form', compact('modules'));
    }

    public function storeNumber(Request $request)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-create')) {
            abort(403, 'User does not have the right permissions.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['created_by'] = Auth::id();
        ContactUsNumber::query()->create($validated);

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Number added successfully.');
    }

    public function editNumber(string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $edit = ContactUsNumber::query()->findOrFail($id);

        return view($modules['folder_path'] . '.number-form', compact('modules', 'edit'));
    }

    public function updateNumber(Request $request, string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $number = ContactUsNumber::query()->findOrFail($id);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['updated_by'] = Auth::id();
        $number->update($validated);

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Number updated successfully.');
    }

    public function destroyNumber(Request $request, string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-delete')) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        $number = ContactUsNumber::query()->findOrFail($id);
        $number->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Number deleted successfully.',
            ]);
        }

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Number deleted successfully.');
    }

    public function create()
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-create')) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-create')) {
            abort(403, 'User does not have the right permissions.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['created_by'] = Auth::id();
        ContactUsBranch::query()->create($validated);

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $edit = ContactUsBranch::query()->findOrFail($id);

        return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $branch = ContactUsBranch::query()->findOrFail($id);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['updated_by'] = Auth::id();
        $branch->update($validated);

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-delete')) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        $branch = ContactUsBranch::query()->findOrFail($id);
        $branch->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully.',
            ]);
        }

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
