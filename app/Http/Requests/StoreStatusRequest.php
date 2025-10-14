<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStatusRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'student_id' => ['required','integer','min:1'],
            'course_id'  => ['required','integer','min:1'],
            'present'    => ['required','boolean'],
            'occurred_at'=> ['required','date'] // ISO-8601
        ];
    }
}

