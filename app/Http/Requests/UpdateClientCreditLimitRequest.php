<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientCreditLimitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $client = $this->route('client');

        return $this->user()->can('manageCreditLimit', $client);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'credit_limit' => 'required|numeric|min:0|max:999999.99',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'credit_limit.required' => 'O limite de crédito é obrigatório.',
            'credit_limit.numeric' => 'O limite de crédito deve ser um número.',
            'credit_limit.min' => 'O limite de crédito não pode ser negativo.',
            'credit_limit.max' => 'O limite de crédito não pode exceder 999.999,99.',
        ];
    }
}
