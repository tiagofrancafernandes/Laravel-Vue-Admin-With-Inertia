<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Sale::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'total_amount' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.metadata' => 'nullable|array',
            'items' => 'nullable|array',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.subtotal' => 'required_with:items|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar soma dos pagamentos
            $totalPayments = collect($this->payments)->sum('amount');

            if (abs($totalPayments - $this->total_amount) > 0.01) {
                $validator->errors()->add(
                    'payments',
                    'A soma dos pagamentos deve ser igual ao total da venda.'
                );
            }

            // Validar soma dos itens se informados
            if ($this->items && count($this->items) > 0) {
                $totalItems = collect($this->items)->sum('subtotal');

                if (abs($totalItems - $this->total_amount) > 0.01) {
                    $validator->errors()->add(
                        'items',
                        'A soma dos itens deve ser igual ao total da venda.'
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'total_amount.required' => 'O valor total é obrigatório.',
            'total_amount.numeric' => 'O valor total deve ser um número.',
            'total_amount.min' => 'O valor total deve ser maior ou igual a zero.',
            'payments.required' => 'É necessário informar pelo menos um método de pagamento.',
            'payments.min' => 'É necessário informar pelo menos um método de pagamento.',
            'payments.*.payment_method_id.required' => 'O método de pagamento é obrigatório.',
            'payments.*.payment_method_id.exists' => 'Método de pagamento inválido.',
            'payments.*.amount.required' => 'O valor do pagamento é obrigatório.',
            'payments.*.amount.numeric' => 'O valor do pagamento deve ser um número.',
            'payments.*.amount.min' => 'O valor do pagamento deve ser maior ou igual a zero.',
        ];
    }
}
