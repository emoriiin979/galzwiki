<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class BriefStoreRequest extends FormRequest
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
            'title'           => ['string', 'required', 'unique:briefs'],
            'note'            => ['string'],
            'abstract'        => ['string', 'required'],
            'hands_on'        => ['string'],
            'parent_brief_id' => ['integer', 'required'],
            'entry_at'        => ['date_format:Y-m-d H:i:s', 'required'],
            'entry_user_id'   => ['integer', 'required'],
            'is_publish'      => ['boolean', 'required'],
        ];
    }
}
