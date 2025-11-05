<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\LibraryIssuedBooks;

class IssueBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(Request $request): array
    {
        $id = $request->route('issue_book') ?? 0;

        return [
            'book_id' => [
                'nullable',
                // Rule::unique((new LibraryIssuedBooks())->getTable())
                //     ->where(function ($query) use ($request) {
                //         return $query->where('book_id', $request->book_id)
                //                      ->whereNull('return_date');
                //     })
                //     ->ignore($id),
            ],
            'student_id' => [
                'nullable',

            ],
            'issued_by' => [
                'nullable',

            ],
            'return_by' => [
                'nullable',

            ],
            'issued_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:issued_date',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'Please select a book.',
            'book_id.unique' => 'This book is already issued and not yet returned.',
            'student_id.required' => 'Please select a student.',
            'issued_date.required' => 'Issued date is required.',
            'issued_date.date' => 'Issued date must be a valid date.',
            'return_date.after_or_equal' => 'Return date must be after or equal to issued date.',
        ];
    }
}
