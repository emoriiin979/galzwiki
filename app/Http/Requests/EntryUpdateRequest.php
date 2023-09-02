<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EntryUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title'           => ['string', 'required', Rule::unique('entries')->ignore($this->id)],
            'subtitle'        => ['string'],
            'body'            => ['string', 'required'],
            'is_publish'      => ['boolean', 'required'],
            'updated_at'      => ['date_format:Y-m-d H:i:s', 'required'],
        ];
    }
}
