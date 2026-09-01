<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'issued_on' => now()->toDateString(),
            'required_by' => now()->addDays(7)->toDateString(),
            'currency' => 'AED',
            'subtotal' => 5000,
            'tax_amount' => 250,
            'total' => 5250,
            'status' => 'draft',
        ];
    }
}
