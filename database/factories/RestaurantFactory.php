<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    // Fixed restaurant definitions so every run gets consistent data
    protected static array $restaurants = [
        [
            'name'          => 'Spice Symphony',
            'slug'          => 'spice-symphony',
            'email'         => 'contact@spicesymphony.in',
            'phone'         => '+91 98765 43210',
            'address'       => '102, Park Street, Kolkata, West Bengal',
            'rating'        => 4.80,
            'reviews_count' => 142,
            'picsum_id'     => 292,
        ],
        [
            'name'          => 'Maharaja Palace',
            'slug'          => 'maharaja-palace',
            'email'         => 'royal@maharajapalace.in',
            'phone'         => '+91 99887 76655',
            'address'       => 'Palace Road, Jaipur, Rajasthan',
            'rating'        => 4.70,
            'reviews_count' => 98,
            'picsum_id'     => 431,
        ],
        [
            'name'          => 'Zayka Haveli',
            'slug'          => 'zayka-haveli',
            'email'         => 'info@zaykahaveli.in',
            'phone'         => '+91 91234 56789',
            'address'       => 'Chandni Chowk, Old Delhi, Delhi',
            'rating'        => 4.90,
            'reviews_count' => 215,
            'picsum_id'     => 674,
        ],
        [
            'name'          => 'Awadhi Rasoi',
            'slug'          => 'awadhi-rasoi',
            'email'         => 'manager@awadhirasoi.com',
            'phone'         => '+91 88776 65544',
            'address'       => 'Hazratganj, Lucknow, Uttar Pradesh',
            'rating'        => 4.60,
            'reviews_count' => 78,
            'picsum_id'     => 999,
        ],
        [
            'name'          => 'Tandoori Nights',
            'slug'          => 'tandoori-nights',
            'email'         => 'booking@tandoorinights.com',
            'phone'         => '+91 77665 54433',
            'address'       => 'Sector 18, Noida, Uttar Pradesh',
            'rating'        => 4.50,
            'reviews_count' => 64,
            'picsum_id'     => 137,
        ],
        [
            'name'          => 'Royal Biryani Durbar',
            'slug'          => 'royal-biryani-durbar',
            'email'         => 'orders@biryanidurbar.com',
            'phone'         => '+91 55443 32211',
            'address'       => 'Jubilee Hills, Hyderabad, Telangana',
            'rating'        => 4.85,
            'reviews_count' => 189,
            'picsum_id'     => 488,
        ],
        [
            'name'          => 'Shahi Dawat',
            'slug'          => 'shahi-dawat',
            'email'         => 'feast@shahidawat.in',
            'phone'         => '+91 44332 21100',
            'address'       => 'Colaba, Mumbai, Maharashtra',
            'rating'        => 4.75,
            'reviews_count' => 110,
            'picsum_id'     => 823,
        ],
        [
            'name'          => 'Delhi Darbar',
            'slug'          => 'delhi-darbar',
            'email'         => 'contact@delhidarbar.in',
            'phone'         => '+91 99991 11122',
            'address'       => 'Connaught Place, New Delhi, Delhi',
            'rating'        => 4.80,
            'reviews_count' => 135,
            'picsum_id'     => 340,
        ],
        [
            'name'          => 'Bukhara Heritage',
            'slug'          => 'bukhara-heritage',
            'email'         => 'bukhara@heritagehotels.com',
            'phone'         => '+91 55555 55566',
            'address'       => 'Diplomatic Enclave, Chanakyapuri, New Delhi',
            'rating'        => 4.95,
            'reviews_count' => 320,
            'picsum_id'     => 573,
        ],
        [
            'name'          => 'Malabar Coast',
            'slug'          => 'malabar-coast',
            'email'         => 'seafood@malabarcoast.in',
            'phone'         => '+91 99990 00011',
            'address'       => 'Marine Drive, Kochi, Kerala',
            'rating'        => 4.82,
            'reviews_count' => 156,
            'picsum_id'     => 217,
        ],
    ];

    // Track which preset to use across sequential calls
    protected static int $index = 0;

    public function definition(): array
    {
        $preset = self::$restaurants[self::$index % count(self::$restaurants)];
        self::$index++;

        $imagePath = $this->downloadImage($preset['slug'], $preset['picsum_id']);

        return [
            'name'          => $preset['name'],
            'slug'          => $preset['slug'],
            'email'         => $preset['email'],
            'phone'         => $preset['phone'],
            'address'       => $preset['address'],
            'rating'        => $preset['rating'],
            'reviews_count' => $preset['reviews_count'],
            'settings'      => ['theme' => 'dark'],
            'image_path'    => $imagePath,
        ];
    }

    /**
     * Download restaurant cover photo from Picsum and store it.
     * Returns the storage-relative path, or null on failure.
     */
    protected function downloadImage(string $slug, int $picsumId): ?string
    {
        Storage::disk('public')->makeDirectory('restaurants');

        $filename = "restaurants/{$slug}.jpg";

        // Skip download if the file already exists (idempotent re-seeding)
        if (Storage::disk('public')->exists($filename)) {
            return $filename;
        }

        try {
            // picsum.photos/seed/{id}/width/height returns a consistent image for the same seed
            $url      = "https://picsum.photos/seed/{$picsumId}/800/500";
            $response = Http::timeout(15)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
        } catch (\Exception $e) {
            // Silently fall through — image_path stays null and the blade fallback renders
        }

        return null;
    }

    /**
     * State: use a random restaurant from the preset list (useful for tests).
     */
    public function random(): static
    {
        return $this->state(function () {
            $preset    = $this->faker->randomElement(self::$restaurants);
            $imagePath = $this->downloadImage($preset['slug'], $preset['picsum_id']);

            return [
                'name'          => $preset['name'],
                'slug'          => $preset['slug'],
                'email'         => $preset['email'],
                'phone'         => $preset['phone'],
                'address'       => $preset['address'],
                'rating'        => $preset['rating'],
                'reviews_count' => $preset['reviews_count'],
                'settings'      => ['theme' => 'dark'],
                'image_path'    => $imagePath,
            ];
        });
    }

    /**
     * State: no image (useful for unit tests that don't need HTTP calls).
     */
    public function withoutImage(): static
    {
        return $this->state(fn () => ['image_path' => null]);
    }
}
