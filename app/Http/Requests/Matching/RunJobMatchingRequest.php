<?php

namespace App\Http\Requests\Matching;

use Illuminate\Foundation\Http\FormRequest;

class RunJobMatchingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('company') !== null;
    }

    public function rules(): array
    {
        return [];
    }
}
