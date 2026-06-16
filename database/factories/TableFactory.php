<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    protected static array $tables = [
        ['name' => 'Royal Table A1',      'capacity' => 2],
        ['name' => 'Royal Table A2',      'capacity' => 2],
        ['name' => 'Maharaja Suite B1',   'capacity' => 4],
        ['name' => 'Maharaja Suite B2',   'capacity' => 4],
        ['name' => 'Haveli Courtyard C1', 'capacity' => 6],
        ['name' => 'Grand Banquet D1',    'capacity' => 8],
    ];

    public function definition(): array
    {
        $preset = $this->faker->randomElement(self::$tables);

        return [
            'restaurant_id' => Restaurant::factory(),
            'name'          => $preset['name'],
            'capacity'      => $preset['capacity'],
            'status'        => $this->faker->randomElement(['available', 'occupied']),
        ];
    }

    /**
     * State: table is available.
     */
    public function available(): static
    {
        return $this->state(fn () => ['status' => 'available']);
    }

    /**
     * State: table is occupied.
     */
    public function occupied(): static
    {
        return $this->state(fn () => ['status' => 'occupied']);
    }

    /**
     * Create the standard 6-table set for a restaurant.
     *
     * Usage: TableFactory::defaultSet($restaurant)
     * Returns: array of created Table models
     */
    public static function defaultSet(Restaurant $restaurant): array
    {
        $created = [];
        foreach (self::$tables as $preset) {
            $created[] = $restaurant->tables()->create([
                'name'     => $preset['name'],
                'capacity' => $preset['capacity'],
                'status'   => 'available',
            ]);
        }
        return $created;
    }
}
