<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    protected static array $templates = [
        [
            'title'   => 'The Golden Ratio of Indian Garam Masala',
            'content' => 'Garam Masala is the heartbeat of Indian kitchens. Discovering the exact balance between green cardamom, cinnamon, cloves, and black pepper is a rite of passage for every tandoori chef. We share our centuries-old proportions that bring out the true aroma without overpowering the main dish ingredients.',
        ],
        [
            'title'   => 'Tandoor Cooking: Fire, Clay, and Soul',
            'content' => 'Cooking in a clay tandoor requires an intuitive understanding of live coals. The high radiant heat instantly seals the juices of marinated paneer and flatbreads, creating the signature smoky crust. Learn how the shape of the clay pot concentrates thermal currents to bake soft, bubbly butter naan.',
        ],
        [
            'title'   => 'The Secret to a Perfect Lucknowi Biryani',
            'content' => 'Lucknowi (Awadhi) Biryani is cooked in the "Dum" style, where meat and half-cooked rice are layered in a heavy bottom copper pot sealed with dough. The slow steaming process makes the rice grains separate and absorbs the delicate spices. In this post, we detail the steps of preparing the Yakhni broth.',
        ],
        [
            'title'   => 'Exploring Coastal Malabar Flavors',
            'content' => 'The Malabar coast brings a completely different dimension to Indian cuisine with the heavy use of coconut milk, curry leaves, mustard seeds, and fresh tamarind. Learn how we prepare our Malabar Fish Curry with a perfect spice level balanced by sour kokum pieces.',
        ],
    ];

    protected static int $index = 0;

    public function definition(): array
    {
        $template = self::$templates[self::$index % count(self::$templates)];
        self::$index++;

        return [
            'restaurant_id' => Restaurant::factory(),
            'title'         => $template['title'],
            'slug'          => Str::slug($template['title'] . '-' . uniqid()),
            'content'       => $template['content'],
        ];
    }

    /**
     * Seed exactly 2 sequential blogs for a restaurant (matching original seeder).
     *
     * Usage: BlogFactory::defaultSet($restaurant, $restaurantIndex)
     */
    public static function defaultSet(Restaurant $restaurant, int $restaurantIndex): void
    {
        $count = count(self::$templates);

        foreach ([0, 1] as $offset) {
            $template = self::$templates[($restaurantIndex + $offset) % $count];
            $suffix   = $offset === 0 ? "at {$restaurant->name}" : 'Masterclass';

            $restaurant->blogs()->create([
                'title'   => "{$template['title']} — {$suffix}",
                'slug'    => Str::slug("{$template['title']}-{$restaurant->id}-{$offset}"),
                'content' => $template['content'],
            ]);
        }
    }
}
