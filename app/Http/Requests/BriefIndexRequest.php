<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class BriefIndexRequest extends FormRequest
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
            'keywords' => ['array'],
            'operator' => ['string', 'required', 'regex:/^(and|or)$/'],
            'page' => ['integer'],
        ];
    }

    /**
     * バリデーションメッセージ
     */
    public function messages(): array
    {
        return [
            'operator' => 'operatorには「and」か「or」のいずれかを指定してください。',
        ];
    }

    /**
     * バリデーション前のデータ成型
     */
    protected function prepareForValidation(): void
    {
        $operator = $this->operator ? strtolower($this->operator) : 'and';
        $this->merge(['operator' => $operator]);
    }
}
