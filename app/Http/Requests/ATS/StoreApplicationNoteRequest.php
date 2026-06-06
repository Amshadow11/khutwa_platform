<?php

namespace App\Http\Requests\ATS;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('company')?->can('addNote', $this->route('application')) ?? false;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:3000']];
    }
}
