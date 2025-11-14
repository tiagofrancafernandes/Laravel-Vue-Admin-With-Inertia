<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Dinheiro',
                'code' => 'cash',
                'is_active' => true,
                'display_order' => 1,
                'description' => 'Pagamento em dinheiro',
            ],
            [
                'name' => 'PIX',
                'code' => 'pix',
                'is_active' => true,
                'display_order' => 2,
                'description' => 'Pagamento via PIX',
            ],
            [
                'name' => 'Cartão de Débito',
                'code' => 'debit_card',
                'is_active' => true,
                'display_order' => 3,
                'description' => 'Pagamento com cartão de débito',
            ],
            [
                'name' => 'Cartão de Crédito',
                'code' => 'credit_card',
                'is_active' => true,
                'display_order' => 4,
                'description' => 'Pagamento com cartão de crédito',
            ],
            [
                'name' => 'Saldo',
                'code' => 'balance',
                'is_active' => true,
                'display_order' => 5,
                'description' => 'Pagamento com saldo pré-pago do cliente',
            ],
            [
                'name' => 'Caderneta (Fiado)',
                'code' => 'account',
                'is_active' => true,
                'display_order' => 6,
                'description' => 'Compra na caderneta (compra à prazo)',
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }

        $this->command->info('Payment methods seeded successfully!');
    }
}
