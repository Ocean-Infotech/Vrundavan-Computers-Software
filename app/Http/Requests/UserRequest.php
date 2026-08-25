<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request)
    {
        // dd('rules 25', $request->all(), Route::currentRouteName(), Route::current()->uri);
        $id = !empty($request->id) ? $request->id : 0;
        $id = (!$id && !empty($request->route('user'))) ? $request->route('user') : 0;
        if(!$id){
            $id = !empty($request->route('user-management')) ? $request->route('user-management') : 0;
            if(!$id && !empty($request?->id)){
                $id = $request?->id;
            }
            // dd('L-27', $id, $request->route('user-management'));
        }
        // dd($id);
        $password_rule = 'required|min:3';
        if($id){
            $password_rule = 'nullable|min:3';
        }
        $rule = [];

        if(in_array(Route::currentRouteName(), ["users.store" , "users.update", "system-user.store", "system-user.update"])){
            $rule = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id . ',id,deleted_at,NULL,status,active',
                'phone' => 'nullable|max:255',
                'password' => $password_rule,
                'role' => 'required|exists:roles,name|max:255',
                'status' => 'required|in:active,inactive',
                'biometric_id' => 'nullable|max:255',
                'date_of_birth' => 'nullable|date',
            ];
        }
        else if(in_array(Route::currentRouteName(), ["application-user.store" , "application-user.update"])){
            $rule = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:'.((new User())->getTable()).',email,' . $id . ',id,deleted_at,NULL,status,active',
                'phone' => 'nullable|max:255',
                'password' => $password_rule,
                'role' => 'required|exists:'.((new Role())->getTable()).',name|max:255',
                'status' => 'required|in:active,inactive',
                'biometric_id' => 'nullable|max:255',
                'date_of_birth' => 'nullable|date',
            ];
        }
        else if(in_array(Route::currentRouteName(), ["customer.store" , "customer.update"])){
            $rule = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id . ',id,deleted_at,NULL,status,active',
                'phone' => 'nullable|max:255',
                'password' => $password_rule,
                'role' => 'required|exists:roles,name|max:255',
                'status' => 'required|in:active,inactive',
                'biometric_id' => 'nullable|max:255',
                'date_of_birth' => 'nullable|date',
            ];
        }
        // dd('rules 74', $request->all(), Route::currentRouteName(), $rule);
        return $rule;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [

        ];
    }
}
