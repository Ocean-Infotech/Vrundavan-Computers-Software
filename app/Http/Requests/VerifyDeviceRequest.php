<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'hardware_id' => 'required|string|size:64',
            'motherboard_serial' => 'required|string',
            'bios_serial' => 'required|string',
            'cpu_id' => 'required|string',
            'timestamp' => 'required|integer',
            'nonce' => 'required|string|min:8',
            'signature' => 'required|string|size:64',
            'pc_name' => 'nullable|string|max:100',
        ];
    }
}
