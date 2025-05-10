<?php

namespace App\Http\Requests;

use App\Models\Documents;
use App\Models\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(Request $request)
    {
        // dd("DocumentRequest 18", $request->all());
        $id = !empty($request->route('documents')) ? $request->route('documents') : 0;
        if (!$id) {
            $id = $request?->id;
        }

        $image = ['required', 'file', 'mimes:jpeg,png,jpg,pdf,doc,docx', 'max:5120'];
        if ($id) {
            $image = ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf,doc,docx', 'max:5120'];
        }
        $rule = [
            'document_type_id' => ['required', 'numeric', 'exists:' . (new DocumentType())->getTable() . ',id,deleted_at,NULL'],
            'name' => ['required', 'string', 'unique:' . (new Documents())->getTable() . ',name,' . $id . ',id,deleted_at,NULL,status,active'],
            'status' => ['nullable', 'in:active,inactive'],
            'document_file' => $image,
        ];
        // dd("DocumentRequest 34", $request->all(), $rule);
        return $rule;
    }

    public function messages(): array
    {
        return [
            'document_type_id.required' => 'Please select a document type.',
            'document_type_id.numeric' => 'Invalid document type format.',
            'document_type_id.exists' => 'Selected document type does not exist.',

            'name.required' => 'The document name is required.',
            'name.string' => 'The document name must be a valid text.',
            'name.unique' => 'A document with this name already exists.',

            'status.in' => 'The status must be either Active or Inactive.',

            'document_file.required' => 'Please attach a document file.',
            'document_file.file' => 'The uploaded file must be valid.',
            'document_file.mimes' => 'Only JPG, PNG, PDF, and Word files (DOC, DOCX) are allowed.',
            'document_file.max' => 'The document file size must not exceed 5MB.',
        ];
    }
}
