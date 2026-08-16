<?php

namespace Database\Factories;

use App\Enums\BookOrderStatus;
use App\Models\BookOrder;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookOrder>
 */
class BookOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->state(['slug' => 'livre', 'price_cents' => 3700]),
            'customer_first_name' => fake()->firstName(),
            'customer_last_name' => fake()->lastName(),
            /**
             * Domaine réellement résolvable : la réservation du coaching
             * revalide l'email avec la règle `dns`, que les domaines
             * `example.*` de Faker ne passent pas.
             */
            'customer_email' => fake()->unique()->userName().'@gmail.com',
            'amount_cents' => 3700,
            'currency' => 'EUR',
            'status' => BookOrderStatus::Pending,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => BookOrderStatus::Paid,
            'paid_at' => now(),
            'stripe_payment_intent_id' => 'pi_'.fake()->unique()->lexify('??????????????'),
        ]);
    }

    public function refunded(): static
    {
        return $this->paid()->state(fn () => [
            'status' => BookOrderStatus::Refunded,
            'refunded_at' => now(),
        ]);
    }

    /**
     * Formule accompagnée : le produit porte le slug qui déclenche le coaching.
     */
    public function withCoaching(): static
    {
        return $this->state(fn () => [
            'product_id' => Product::factory()->state(['slug' => 'livre-coaching', 'price_cents' => 7000]),
            'amount_cents' => 7000,
        ]);
    }
}
