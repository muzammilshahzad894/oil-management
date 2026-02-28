<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Payment;
use Carbon\Carbon;

class OilManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Brands (with quantity / inventory and purchase price for P&L)
        $brandsData = [
            ['name' => 'Castrol', 'description' => 'Premium engine oil'],
            ['name' => 'Mobil', 'description' => 'High performance motor oil'],
            ['name' => 'Shell', 'description' => 'Quality engine lubricant'],
            ['name' => 'Valvoline', 'description' => 'Advanced engine protection'],
            ['name' => 'Pennzoil', 'description' => 'Full synthetic motor oil'],
        ];

        $createdBrands = [];
        foreach ($brandsData as $data) {
            $costPrice = rand(20, 80) + (rand(0, 99) / 100);
            $createdBrands[] = Brand::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'quantity' => rand(50, 500),
                'cost_price' => $costPrice,
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

        // Create Sales (decrease brand quantity, set cost_at_sale for P&L)
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        $saleIds = [];

        for ($i = 0; $i < 150; $i++) {
            $customer = $customers[array_rand($customers)];
            $brand = $createdBrands[array_rand($createdBrands)];

            if ($brand->quantity > 0) {
                $quantity = rand(1, min(20, $brand->quantity));
                $price = rand(50, 500);
                $costAtSale = $brand->cost_price !== null ? (float) $brand->cost_price : null;
                $saleDate = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                $sale = Sale::create([
                    'customer_id' => $customer->id,
                    'brand_id' => $brand->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'cost_at_sale' => $costAtSale,
                    'sale_date' => $saleDate,
                    'is_paid' => false,
                    'notes' => rand(0, 1) == 1 ? 'Regular customer order' : null,
                ]);
                $saleIds[] = $sale;
                $brand->removeStock($quantity);
            }
        }

        // Add payments for some sales (partial, full, overpayment)
        foreach (array_slice($saleIds, 0, 60) as $index => $sale) {
            $payType = $index % 3;
            if ($payType === 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $sale->price,
                    'payment_date' => $sale->sale_date,
                    'method' => 'cash',
                    'notes' => 'Full payment',
                ]);
            } elseif ($payType === 1) {
                $amt = round($sale->price * 0.5, 2);
                Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $amt,
                    'payment_date' => $sale->sale_date,
                    'method' => 'cash',
                    'notes' => 'Half payment',
                ]);
            }
            $sale->refreshIsPaid();
        }
    }
}
