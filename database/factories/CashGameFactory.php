<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\CashGame;
use App\Attributes\Limit;
use App\Attributes\Stake;
use App\Attributes\Variant;
use App\Attributes\TableSize;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashGameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start_time = Carbon::now()->subHours(rand(1, 17520));
        $end_time = $start_time->copy()->addMinutes(rand(10, 1440));

        $currencies = ['GBP', 'USD', 'EUR', 'PLN', 'CAD', 'AUD'];

        return [
            'user_id' => User::factory(),
            'stake_id' => Stake::all()->random()->id,
            'limit_id' => Limit::all()->random()->id,
            'variant_id' => Variant::all()->random()->id,
            'table_size_id' => TableSize::all()->random()->id,
            'location' => $this->faker->randomElement(['CasinoMK', 'Las Vegas', 'Grosvenor Casino Luton', 'Grosvenor Casino Cardiff']),
            'comments' => $this->faker->paragraph,
            'start_time' => $start_time->toDateTimeString(),
            'end_time' => $end_time->toDateTimeString(),
            'currency' => $this->faker->randomElement($currencies),
        ];
    }

        /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (CashGame $cashGame) {
            $currencies = ['GBP', 'USD', 'EUR', 'PLN', 'CAD', 'AUD'];

            // Add at 1, 2 or 3 Buy Ins
            $num_buy_ins = rand(1,3);
            for ($i = 1; $i <= $num_buy_ins; $i++) {
                $currency = ($i == 1) ? $cashGame->currency : $this->faker->randomElement($currencies);
                $cashGame->addBuyIn($this->faker->numberBetween(20, 200), $currency);
            }

            // Add 0, 1 or 2 Expenses
            $num_expenses = rand(0,2);
            for ($i = 1; $i <= $num_expenses; $i++) {
                $cashGame->addExpense($this->faker->numberBetween(1, 10), $this->faker->randomElement($currencies), $this->faker->sentence(2, true));
            }

            // Cash Out Between £0 and £1000
            $cashGame->addCashOut($this->faker->numberBetween(0, 1000), $this->faker->randomElement($currencies));
        });
    }
}
