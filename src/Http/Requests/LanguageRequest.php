<?php

namespace Arm092\Translation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Arm092\Translation\Rules\LanguageNotExists;

class LanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string'],
            'locale' => ['required', 'string', 'max:35', 'regex:/\A[A-Za-z0-9]+(?:[-_][A-Za-z0-9]+)*\z/D', new LanguageNotExists],
        ];
    }
}
