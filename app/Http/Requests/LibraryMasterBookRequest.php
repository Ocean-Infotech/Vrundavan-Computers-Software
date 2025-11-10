<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryMasterBookRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        $id = $request->route('library_master_book') ?? 0;

        return [
            'book_name' => [
                'required',
                'string',
                'max:255',
            ],
            'library_book_no' => [
                'required',
                'string',

            ],
            'author_name' => [
                'required',
                'string',
                'max:255',
            ],
            'publisher_name' => [
                'required',
                'string',
                'max:255',
            ],
            'total_number_of_page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'price' => [
                'required',
                'numeric',
            ],
            'purchase_date'=>[
                'required',
                'date',
            ]
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'library_book_no.required' => 'Please enter the Library Book No.',
            'library_book_no.unique'   => 'This Library Book No. already exists.',
            'book_name.required'       => 'Please enter the Book Name.',
            'author_name.required'     => 'Please enter the Author Name.',
            'publisher_name.required'  => 'Please enter the Publisher Name.',
            'total_pages.required'     => 'Please enter the total number of pages.',
            'total_pages.integer'      => 'Total number of pages must be an integer.',
            'price.required'           => 'Please enter the price.',
            'price.numeric'            => 'Price must be a number.',
        ];
    }
}
