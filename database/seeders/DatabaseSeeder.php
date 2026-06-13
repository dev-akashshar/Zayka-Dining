<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\Table;
use App\Models\Booking;
use App\Models\Attendance;
use App\Models\MenuItem;
use App\Models\Blog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $roles = ['super_admin', 'manager', 'staff', 'customer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // 2. Define 20 Indian Restaurants
        $restaurantsData = [
            [
                'name' => 'Spice Symphony',
                'slug' => 'spice-symphony',
                'email' => 'contact@spicesymphony.in',
                'phone' => '+91 98765 43210',
                'address' => '102, Park Street, Kolkata, West Bengal',
                'rating' => 4.80,
                'reviews_count' => 142,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Maharaja Palace',
                'slug' => 'maharaja-palace',
                'email' => 'royal@maharajapalace.in',
                'phone' => '+91 99887 76655',
                'address' => 'Palace Road, Jaipur, Rajasthan',
                'rating' => 4.70,
                'reviews_count' => 98,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Zayka Haveli',
                'slug' => 'zayka-haveli',
                'email' => 'info@zaykahaveli.in',
                'phone' => '+91 91234 56789',
                'address' => 'Chandni Chowk, Old Delhi, Delhi',
                'rating' => 4.90,
                'reviews_count' => 215,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Awadhi Rasoi',
                'slug' => 'awadhi-rasoi',
                'email' => 'manager@awadhirasoi.com',
                'phone' => '+91 88776 65544',
                'address' => 'Hazratganj, Lucknow, Uttar Pradesh',
                'rating' => 4.60,
                'reviews_count' => 78,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Tandoori Nights',
                'slug' => 'tandoori-nights',
                'email' => 'booking@tandoorinights.com',
                'phone' => '+91 77665 54433',
                'address' => 'Sector 18, Noida, Uttar Pradesh',
                'rating' => 4.50,
                'reviews_count' => 64,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Masala Junction',
                'slug' => 'masala-junction',
                'email' => 'hello@masalajunction.com',
                'phone' => '+91 66554 43322',
                'address' => 'Indiranagar, Bengaluru, Karnataka',
                'rating' => 4.45,
                'reviews_count' => 52,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Royal Biryani Durbar',
                'slug' => 'royal-biryani-durbar',
                'email' => 'orders@biryanidurbar.com',
                'phone' => '+91 55443 32211',
                'address' => 'Jubilee Hills, Hyderabad, Telangana',
                'rating' => 4.85,
                'reviews_count' => 189,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Shahi Dawat',
                'slug' => 'shahi-dawat',
                'email' => 'feast@shahidawat.in',
                'phone' => '+91 44332 21100',
                'address' => 'Colaba, Mumbai, Maharashtra',
                'rating' => 4.75,
                'reviews_count' => 110,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Punjab Grill House',
                'slug' => 'punjab-grill-house',
                'email' => 'info@punjabgrillhouse.com',
                'phone' => '+91 33221 10099',
                'address' => 'Sector 17, Chandigarh',
                'rating' => 4.65,
                'reviews_count' => 85,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Dakshin Delight',
                'slug' => 'dakshin-delight',
                'email' => 'manager@dakshindelight.com',
                'phone' => '+91 22110 09988',
                'address' => 'T. Nagar, Chennai, Tamil Nadu',
                'rating' => 4.55,
                'reviews_count' => 74,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Delhi Darbar',
                'slug' => 'delhi-darbar',
                'email' => 'contact@delhidarbar.in',
                'phone' => '+91 99991 11122',
                'address' => 'Connaught Place, New Delhi, Delhi',
                'rating' => 4.80,
                'reviews_count' => 135,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Jaipur Swaad',
                'slug' => 'jaipur-swaad',
                'email' => 'swaadjpr@jaipurswaad.in',
                'phone' => '+91 88882 22233',
                'address' => 'Vaishali Nagar, Jaipur, Rajasthan',
                'rating' => 4.40,
                'reviews_count' => 47,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Mumbai Masala Room',
                'slug' => 'mumbai-masala-room',
                'email' => 'spicy@masalaroom.in',
                'phone' => '+91 77773 33344',
                'address' => 'Bandra West, Mumbai, Maharashtra',
                'rating' => 4.72,
                'reviews_count' => 93,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'The Curry Leaf',
                'slug' => 'the-curry-leaf',
                'email' => 'leaf@thecurryleaf.in',
                'phone' => '+91 66664 44455',
                'address' => 'MG Road, Pune, Maharashtra',
                'rating' => 4.50,
                'reviews_count' => 58,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Bukhara Heritage',
                'slug' => 'bukhara-heritage',
                'email' => 'bukhara@heritagehotels.com',
                'phone' => '+91 55555 55566',
                'address' => 'Diplomatic Enclave, Chanakyapuri, New Delhi',
                'rating' => 4.95,
                'reviews_count' => 320,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Lucknowi Kebab Junction',
                'slug' => 'lucknowi-kebab-junction',
                'email' => 'kebabs@lucknowijunction.com',
                'phone' => '+91 44446 66677',
                'address' => 'Chowk, Lucknow, Uttar Pradesh',
                'rating' => 4.78,
                'reviews_count' => 125,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Golden Temple Kitchen',
                'slug' => 'golden-temple-kitchen',
                'email' => 'prasad@goldentemplekitchen.com',
                'phone' => '+91 33337 77788',
                'address' => 'Amritsar, Punjab',
                'rating' => 4.92,
                'reviews_count' => 412,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Peshawari Kitchens',
                'slug' => 'peshawari-kitchens',
                'email' => 'chef@peshawarikitchens.com',
                'phone' => '+91 22228 88899',
                'address' => 'IT Park, Hyderabad, Telangana',
                'rating' => 4.68,
                'reviews_count' => 87,
                'settings' => ['theme' => 'dark']
            ],
            [
                'name' => 'Indigo Bistro',
                'slug' => 'indigo-bistro',
                'email' => 'dine@indigobistro.com',
                'phone' => '+91 11119 99900',
                'address' => 'Park Street, Kolkata, West Bengal',
                'rating' => 4.62,
                'reviews_count' => 73,
                'settings' => ['theme' => 'light']
            ],
            [
                'name' => 'Malabar Coast',
                'slug' => 'malabar-coast',
                'email' => 'seafood@malabarcoast.in',
                'phone' => '+91 99990 00011',
                'address' => 'Marine Drive, Kochi, Kerala',
                'rating' => 4.82,
                'reviews_count' => 156,
                'settings' => ['theme' => 'dark']
            ]
        ];

        $restaurants = [];
        foreach ($restaurantsData as $data) {
            $restaurants[] = Restaurant::create($data);
        }

        // 3. Create Super Admin User
        $superAdmin = User::create([
            'name' => 'Zayka Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // 4. Create Customers
        $customers = [];
        $customerNames = [
            'Aarav Mehta', 'Diya Sharma', 'Kabir Patel', 'Ishaan Iyer', 'Ananya Roy',
            'Rohan Gupta', 'Pooja Reddy', 'Arjun Kapoor', 'Sneha Rao', 'Devendra Yadav'
        ];
        
        // Add a default master customer for easy testing
        $masterCustomer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $masterCustomer->assignRole('customer');
        $customers[] = $masterCustomer;

        for ($i = 0; $i < 9; $i++) {
            $cust = User::create([
                'name' => $customerNames[$i],
                'email' => 'customer' . ($i + 1) . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $cust->assignRole('customer');
            $customers[] = $cust;
        }

        // 5. Populate each restaurant with Managers, Staff, Tables, Menu Items, Blogs, Bookings, Attendance
        $dishNames = [
            'Starters' => [
                ['Hara Bhara Kabab', 'Delicately spiced spinach and green pea patties, shallow fried.', 220.00],
                ['Paneer Tikka Angare', 'Cottage cheese cubes marinated in fiery spices and grilled in tandoor.', 310.00],
                ['Samosa Chaat', 'Crispy samosas crushed and topped with chickpeas, yogurt, chutneys, and sev.', 160.00],
                ['Chicken Tikka', 'Tender chicken thighs marinated in yogurt spices and cooked in clay oven.', 350.00],
            ],
            'Main Course' => [
                ['Paneer Butter Masala', 'Rich tomato and cashew gravy with soft paneer cubes and a dollop of fresh cream.', 380.00],
                ['Dal Makhani', 'Black lentils slow cooked overnight with butter, cream, and traditional spices.', 320.00],
                ['Hyderabadi Veg Biryani', 'Fragrant basmati rice layered with spiced vegetables, saffron, and fresh mint.', 350.00],
                ['Butter Chicken', 'Tandoori grilled chicken cooked in a rich, buttery, spiced tomato sauce.', 420.00],
                ['Mutton Rogan Josh', 'Slow-cooked lamb shank in a rich aromatic gravy flavored with Kashmiri chilies.', 490.00],
                ['Garlic Naan', 'Clay oven leavened flatbread topped with minced garlic and butter.', 90.00],
            ],
            'Desserts' => [
                ['Shahi Tukda', 'Royal bread pudding soaked in saffron-infused rabri and topped with dry fruits.', 180.00],
                ['Gulab Jamun with Rabri', 'Warm milk-solid dumplings in rose-scented syrup paired with chilled rabri.', 190.00],
                ['Rasmalai', 'Soft paneer patties soaked in sweetened, cardamom flavored milk.', 150.00],
            ],
            'Beverages' => [
                ['Kesaria Thandai', 'Traditional spiced milk beverage flavored with saffron, almonds, and cardamom.', 120.00],
                ['Masala Chaas', 'Refreshing spiced buttermilk tempered with roasted cumin and coriander.', 80.00],
                ['Mango Lassi', 'Sweet yogurt beverage blended with sweet mango pulp and cardamon.', 130.00],
            ]
        ];

        $blogTemplates = [
            [
                'title' => 'The Golden Ratio of Indian Garam Masala',
                'content' => 'Garam Masala is the heartbeat of Indian kitchens. Discovering the exact balance between green cardamom, cinnamon, cloves, and black pepper is a rite of passage for every tandoori chef. We share our centuries-old proportions that bring out the true aroma without overpowering the main dish ingredients.',
            ],
            [
                'title' => 'Tandoor Cooking: Fire, Clay, and Soul',
                'content' => 'Cooking in a clay tandoor requires an intuitive understanding of live coals. The high radiant heat instantly seals the juices of marinated paneer and flatbreads, creating the signature smoky crust. Learn how the shape of the clay pot concentrates thermal currents to bake soft, bubbly butter naan.',
            ],
            [
                'title' => 'The Secret to a Perfect Lucknowi Biryani',
                'content' => 'Lucknowi (Awadhi) Biryani is cooked in the "Dum" style, where meat and half-cooked rice are layered in a heavy bottom copper pot sealed with dough. The slow steaming process makes the rice grains separate and absorbs the delicate spices. In this post, we detail the steps of preparing the Yakhni broth.',
            ],
            [
                'title' => 'Exploring Coastal Malabar Flavors',
                'content' => 'The Malabar coast brings a completely different dimension to Indian cuisine with the heavy use of coconut milk, curry leaves, mustard seeds, and fresh tamarind. Learn how we prepare our Malabar Fish Curry with a perfect spice level balanced by sour kokum pieces.',
            ]
        ];

        foreach ($restaurants as $idx => $restaurant) {
            $num = $idx + 1;

            // Create Manager
            $managerUser = User::create([
                'name' => "Manager " . $restaurant->name,
                'email' => "manager{$num}@example.com",
                'password' => Hash::make('password'),
                'restaurant_id' => $restaurant->id,
                'email_verified_at' => now(),
            ]);
            $managerUser->assignRole('manager');

            // Create Staff
            $staffUser = User::create([
                'name' => "Staff " . $restaurant->name,
                'email' => "staff{$num}@example.com",
                'password' => Hash::make('password'),
                'restaurant_id' => $restaurant->id,
                'email_verified_at' => now(),
            ]);
            $staffUser->assignRole('staff');

            // Seating Tables
            $tablesData = [
                ['name' => 'Royal Table A1', 'capacity' => 2],
                ['name' => 'Royal Table A2', 'capacity' => 2],
                ['name' => 'Maharaja Suite B1', 'capacity' => 4],
                ['name' => 'Maharaja Suite B2', 'capacity' => 4],
                ['name' => 'Haveli Courtyard C1', 'capacity' => 6],
                ['name' => 'Grand Banquet D1', 'capacity' => 8],
            ];

            $tables = [];
            foreach ($tablesData as $t) {
                $tables[] = $restaurant->tables()->create([
                    'name' => $t['name'],
                    'capacity' => $t['capacity'],
                    'status' => 'available',
                ]);
            }

            // Menu Items (Seed 10 items)
            $count = 0;
            foreach ($dishNames as $category => $items) {
                foreach ($items as $item) {
                    if ($count >= 10) break;
                    $restaurant->menuItems()->create([
                        'name' => $item[0],
                        'description' => $item[1],
                        'price' => $item[2],
                        'category' => $category,
                        'is_available' => true,
                    ]);
                    $count++;
                }
            }

            // Blogs (Seed 2 blogs per restaurant)
            $blog1 = $blogTemplates[$idx % count($blogTemplates)];
            $blog2 = $blogTemplates[($idx + 1) % count($blogTemplates)];

            $restaurant->blogs()->create([
                'title' => $blog1['title'] . " at " . $restaurant->name,
                'slug' => Str::slug($blog1['title'] . "-" . $restaurant->id),
                'content' => $blog1['content'],
            ]);

            $restaurant->blogs()->create([
                'title' => $blog2['title'] . " - Masterclass",
                'slug' => Str::slug($blog2['title'] . "-" . $restaurant->id . "-masterclass"),
                'content' => $blog2['content'],
            ]);

            // Seed Bookings (Seed 5 bookings per restaurant)
            for ($b = 0; $b < 5; $b++) {
                $customerIndex = ($idx + $b) % count($customers);
                $tableIndex = $b % count($tables);
                $status = ['confirmed', 'pending', 'cancelled', 'rejected', 'completed'][($idx + $b) % 5];
                $payStatus = $status === 'confirmed' || $status === 'completed' ? 'paid_full' : ($status === 'pending' ? 'paid_advance' : 'unpaid');
                $payType = $payStatus === 'paid_full' ? 'full' : ($payStatus === 'paid_advance' ? 'advance' : 'none');
                $amount = $payType === 'full' ? 4999.00 : ($payType === 'advance' ? 1250.00 : 0.00);

                Booking::create([
                    'restaurant_id' => $restaurant->id,
                    'user_id' => $customers[$customerIndex]->id,
                    'table_id' => $tables[$tableIndex]->id,
                    'guest_count' => min($tables[$tableIndex]->capacity, 2 + ($b % 4)),
                    'booking_date' => now()->addDays(($idx + $b) % 7)->subDays(2)->toDateString(),
                    'booking_time' => sprintf('%02d:00:00', 12 + ($b * 2) % 11),
                    'status' => $status,
                    'payment_status' => $payStatus,
                    'payment_type' => $payType,
                    'payment_amount' => $amount,
                    'notes' => 'Aesthetic royal seating preferred.',
                ]);
            }

            // Seed Attendance (Seed 2 records per restaurant staff)
            Attendance::create([
                'user_id' => $staffUser->id,
                'restaurant_id' => $restaurant->id,
                'check_in' => now()->subDays(1)->setHour(9)->setMinute(30)->toDateTimeString(),
                'check_out' => now()->subDays(1)->setHour(18)->setMinute(15)->toDateTimeString(),
                'notes' => 'Managed kitchen preparation shift.',
            ]);

            Attendance::create([
                'user_id' => $staffUser->id,
                'restaurant_id' => $restaurant->id,
                'check_in' => now()->setHour(9)->setMinute(15)->toDateTimeString(),
                'notes' => 'Active check-in morning duty roster.',
            ]);
        }
    }
}
