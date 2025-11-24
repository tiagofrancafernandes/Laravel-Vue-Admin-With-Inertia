<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitClientProofRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated client users can submit proofs
        return auth()->check() && auth()->user()->isClient();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:deposit,payment',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'file' => 'required|file|mimes:jpeg,png,pdf|max:5120',
            'sale_id' => 'nullable|exists:sales,id',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'O tipo do comprovante é obrigatório.',
            'type.in' => 'O tipo deve ser "depósito" ou "pagamento".',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'amount.max' => 'O valor é muito grande.',
            'file.required' => 'O arquivo é obrigatório.',
            'file.file' => 'Deve ser um arquivo válido.',
            'file.mimes' => 'O arquivo deve ser em formato JPEG, PNG ou PDF.',
            'file.max' => 'O arquivo não pode ser maior que 5MB.',
            'sale_id.exists' => 'A venda selecionada não existe.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
        ];
    }
}
