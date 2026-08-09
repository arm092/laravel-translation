<?php

namespace JoeDixon\Translation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslationRequest extends FormRequest
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
            'language' => ['required', 'string', 'max:35', 'regex:/\A[A-Za-z0-9]+(?:[-_][A-Za-z0-9]+)*\z/D'],
            'namespace' => ['nullable', 'string', 'max:100', 'regex:/\A[A-Za-z0-9_-]+\z/D'],
            'group' => ['nullable', 'string', 'max:100', 'regex:/\A[A-Za-z0-9_-]+(?:::[A-Za-z0-9_-]+)?\z/D'],
            'key' => ['required', 'string'],
            'value' => ['present', 'nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['language' => $this->route('language')]);
    }
}
