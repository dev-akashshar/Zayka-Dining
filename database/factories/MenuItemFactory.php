<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    protected static array $menu = [
        'Starters' => [
            ['Hara Bhara Kabab',    'Delicately spiced spinach and green pea patties, shallow fried.',              220.00],
            ['Paneer Tikka Angare', 'Cottage cheese cubes marinated in fiery spices and grilled in tandoor.',       310.00],
            ['Samosa Chaat',        'Crispy samosas crushed and topped with chickpeas, yogurt, chutneys, and sev.', 160.00],
            ['Chicken Tikka',       'Tender chicken thighs marinated in yogurt spices and cooked in clay oven.',    350.00],
        ],
        'Main Course' => [
            ['Paneer Butter Masala',     'Rich tomato and cashew gravy with soft paneer cubes and fresh cream.',         380.00],
            ['Dal Makhani',              'Black lentils slow cooked overnight with butter, cream, and spices.',           320.00],
            ['Hyderabadi Veg Biryani',   'Fragrant basmati rice layered with spiced vegetables, saffron, and mint.',     350.00],
            ['Butter Chicken',           'Tandoori grilled chicken cooked in a rich, buttery, spiced tomato sauce.',     420.00],
            ['Mutton Rogan Josh',        'Slow-cooked lamb shank in a rich aromatic Kashmiri chili gravy.',              490.00],
            ['Garlic Naan',              'Clay oven leavened flatbread topped with minced garlic and butter.',            90.00],
        ],
        'Desserts' => [
            ['Shahi Tukda',               'Royal bread pudding soaked in saffron-infused rabri and dry fruits.',          180.00],
            ['Gulab Jamun with Rabri',    'Warm milk-solid dumplings in rose-scented syrup paired with chilled rabri.',   190.00],
            ['Rasmalai',                  'Soft paneer patties soaked in sweetened, cardamom flavored milk.',             150.00],
        ],
        'Beverages' => [
            ['Kesaria Thandai', 'Traditional spiced milk with saffron, almonds, and cardamom.',          120.00],
            ['Masala Chaas',    'Refreshing spiced buttermilk tempered with roasted cumin and coriander.', 80.00],
            ['Mango Lassi',     'Sweet yogurt beverage blended with mango pulp and cardamom.',            130.00],
        ],
    ];

    public function definition(): array
    {
        // Pick a random category then a random item within it
        $category = $this->faker->randomElement(array_keys(self::$menu));
        $item      = $this->faker->randomElement(self::$menu[$category]);

        return [
            'restaurant_id' => Restaurant::factory(),
            'name'          => $item[0],
            'description'   => $item[1],
            'price'         => $item[2],
            'category'      => $category,
            'is_available'  => $this->faker->boolean(90), // 90 % chance available
        ];
    }

    /**
     * State: mark item as unavailable (out of stock).
     */
    public function unavailable(): static
    {
        return $this->state(fn () => ['is_available' => false]);
    }

    /**
     * State: force a specific category.
     */
    public function category(string $category): static
    {
        return $this->state(function () use ($category) {
            $items = self::$menu[$category] ?? [];
            $item  = $this->faker->randomElement($items);

            return [
                'category'    => $category,
                'name'        => $item[0],
                'description' => $item[1],
                'price'       => $item[2],
            ];
        });
    }

    /**
     * Seed exactly 10 items (4 starters + 4 mains + 1 dessert + 1 beverage)
     * for a restaurant, matching the original seeder mix.
     *
     * Usage: MenuItemFactory::defaultSet($restaurant)
     */
    public static function defaultSet(Restaurant $restaurant): void
    {
        $counts = ['Starters' => 4, 'Main Course' => 4, 'Desserts' => 1, 'Beverages' => 1];

        foreach ($counts as $category => $n) {
            $items = array_slice(self::$menu[$category], 0, $n);
            foreach ($items as $item) {
                $restaurant->menuItems()->create([
                    'name'         => $item[0],
                    'description'  => $item[1],
                    'price'        => $item[2],
                    'category'     => $category,
                    'is_available' => true,
                ]);
            }
        }
    }
}
