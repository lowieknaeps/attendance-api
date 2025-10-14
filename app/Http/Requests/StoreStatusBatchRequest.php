<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStatusBatchRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'items' => ['required','array','min:1'],
            'items.*.student_id' => ['required','integer','min:1'],
            'items.*.course_id'  => ['required','integer','min:1'],
            'items.*.present'    => ['required','boolean'],
            'items.*.occurred_at'=> ['required','date'],
        ];
    }
}

