<?php

namespace Arm092\Translation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Arm092\Translation\Support\TranslationInputRules;

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
        return TranslationInputRules::get();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['language' => $this->route('language')]);
    }
}
