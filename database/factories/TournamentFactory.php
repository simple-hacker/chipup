<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Tournament;
use App\Attributes\Limit;
use App\Attributes\Variant;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TournamentFactory extends Factory
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

        $entries = $this->faker->numberBetween(50, 500);

        $currencies = ['GBP', 'USD', 'EUR', 'PLN', 'CAD', 'AUD'];

        return [
            'user_id' => User::factory(),
            'limit_id' => Limit::all()->random()->id,
            'variant_id' => Variant::all()->random()->id,
            'name' => $this->faker->sentence(3, true),
            'prize_pool' => ($this->faker->numberBetween(10, 500)) * 100,
            'entries' => $entries,
            'position' => $this->faker->numberBetween(1, $entries),
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
        return $this->afterCreating(function (Tournament $tournament) {
            $currencies = ['GBP', 'USD', 'EUR', 'PLN', 'CAD', 'AUD'];

            // Add BuyIn amount between £5 and £500
            $buy_in = $this->faker->numberBetween(5, 500);
            $currency = $tournament->currency;
            $tournament->addBuyIn($buy_in, $currency);

            // Add 0, 1 or 2 Rebuys
            $num_rebuys = rand(0,2);
            for ($i = 1; $i <= $num_rebuys; $i++) {
                $tournament->addRebuy($buy_in, $this->faker->randomElement($currencies));
            }

            // Add 0 or 1 Add Ons
            $num_add_ons = rand(0,1);
            for ($i = 1; $i <= $num_add_ons; $i++) {
                $tournament->addAddOn(($buy_in / 2), $this->faker->randomElement($currencies));
            }

            // Add 0, 1 or 2 Expenses
            $num_expenses = rand(0,2);
            for ($i = 1; $i <= $num_expenses; $i++) {
                $tournament->addExpense($this->faker->numberBetween(1, 10), $this->faker->randomElement($currencies), $this->faker->sentence(2, true));
            }

            // Cash Out depending on position and entries.
            $finish_percentile = $tournament->position / $tournament->entries;

            if ($finish_percentile < 0.01) {
                $cash_out_amount = $buy_in * 30;
            }
            elseif ($finish_percentile < 0.02) {
                $cash_out_amount = $buy_in * 10;
            }
            elseif ($finish_percentile < 0.05) {
                $cash_out_amount = $buy_in * 5;
            }
            elseif ($finish_percentile < 0.10) {
                $cash_out_amount = $buy_in * 2;
            }
            elseif ($finish_percentile < 0.15) {
                $cash_out_amount = $buy_in * 1.2;
            }
            else {
                $cash_out_amount = 0;
            }

            $tournament->addCashOut($cash_out_amount, $this->faker->randomElement($currencies));
        });
    }
}
