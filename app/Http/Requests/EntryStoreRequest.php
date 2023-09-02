<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class EntryStoreRequest extends FormRequest
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
            'title'           => ['string', 'required', 'unique:entries'],
            'subtitle'        => ['string'],
            'body'            => ['string', 'required'],
            'parent_entry_id' => ['integer', 'required'],
            'post_user_id'    => ['integer', 'required'],
            'post_at'         => ['date_format:Y-m-d H:i:s', 'required'],
            'is_publish'      => ['boolean', 'required'],
        ];
    }
}
