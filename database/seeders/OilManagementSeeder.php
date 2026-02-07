<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Sale;
use Carbon\Carbon;

class OilManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Brands (with quantity / inventory)
        $brandsData = [
            ['name' => 'Castrol', 'description' => 'Premium engine oil'],
            ['name' => 'Mobil', 'description' => 'High performance motor oil'],
            ['name' => 'Shell', 'description' => 'Quality engine lubricant'],
            ['name' => 'Valvoline', 'description' => 'Advanced engine protection'],
            ['name' => 'Pennzoil', 'description' => 'Full synthetic motor oil'],
        ];

        $createdBrands = [];
        foreach ($brandsData as $data) {
            $createdBrands[] = Brand::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'quantity' => rand(50, 500),
            ]);
        }

        // Create Customers
        $customers = [];
        $customerNames = [
            'John Smith', 'Sarah Johnson', 'Michael Brown', 'Emily Davis', 'David Wilson',
            'Jessica Martinez', 'Christopher Anderson', 'Amanda Taylor', 'Matthew Thomas', 'Ashley Jackson',
            'Daniel White', 'Michelle Harris', 'Andrew Martin', 'Stephanie Thompson', 'Joshua Garcia',
            'Nicole Martinez', 'Ryan Rodriguez', 'Lauren Lewis', 'Kevin Lee', 'Rachel Walker',
        ];

        foreach ($customerNames as $name) {
            $customers[] = Customer::create([
                'name' => $name,
                'phone' => '+1' . rand(2000000000, 9999999999),
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'address' => rand(100, 9999) . ' Main Street, City, State ' . rand(10000, 99999),
            ]);
        }

        // Create Sales (decrease brand quantity)
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        for ($i = 0; $i < 150; $i++) {
            $customer = $customers[array_rand($customers)];
            $brand = $createdBrands[array_rand($createdBrands)];

            if ($brand->quantity > 0) {
                $quantity = rand(1, min(20, $brand->quantity));
                $price = rand(50, 500);
                $saleDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                Sale::create([
                    'customer_id' => $customer->id,
                    'brand_id' => $brand->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'sale_date' => $saleDate,
                    'is_paid' => rand(0, 1) == 1,
                    'notes' => rand(0, 1) == 1 ? 'Regular customer order' : null,
                ]);

                $brand->removeStock($quantity);
            }
        }
    }
}
