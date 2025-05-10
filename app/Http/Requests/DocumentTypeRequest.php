<?php

namespace App\Http\Requests;

use App\Models\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class DocumentTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(Request $request)
    {
        // dd("RedemptionRequest 20", $request->all());
        $id = !empty($request->route('document-type')) ? $request->route('document-type') : 0;
        if (!$id) {
            $id = $request?->id;
        }
        $rule = [
            'name' => ['required', 'string', 'unique:' . (new DocumentType())->getTable() . ',name,' . $id . ',id,deleted_at,NULL,status,active'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
        // dd("RedemptionRequest 30", $request->all(), $rule);
        return $rule;
    }
}
