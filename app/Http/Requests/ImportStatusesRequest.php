<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportStatusesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }
    

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],

            'items.*.student_id'  => ['required', 'integer'], // eventueel: 'exists:students,id'
            'items.*.course_id'   => ['required', 'integer'],
            'items.*.present'     => ['required', 'boolean'],
            'items.*.occurred_at' => ['required', 'date'], // ISO 8601 is prima
        ];
    }
}
