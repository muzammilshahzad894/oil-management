<?php

namespace Database\Seeders;

use App\Models\LedgerCustomer;
use App\Models\LedgerTransaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LedgerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Ali Hassan', 'phone' => '0300-1234567', 'address' => 'Lahore'],
            ['name' => 'Fatima Khan', 'phone' => '0321-9876543', 'address' => 'Karachi'],
            ['name' => 'Muhammad Raza', 'phone' => '0333-5551234', 'address' => 'Islamabad'],
            ['name' => 'Ayesha Siddiqui', 'phone' => null, 'address' => 'Faisalabad'],
            ['name' => 'Usman Ahmed', 'phone' => '0345-7778899', 'address' => null],
            ['name' => 'Sana Malik', 'phone' => '0312-4445566', 'address' => 'Rawalpindi'],
            ['name' => 'Bilal Hussain', 'phone' => null, 'address' => 'Multan'],
            ['name' => 'Zainab Akhtar', 'phone' => '0301-2223344', 'address' => 'Peshawar'],
            ['name' => 'Imran Sheikh', 'phone' => '0322-6667788', 'address' => 'Quetta'],
            ['name' => 'Sadia Noor', 'phone' => null, 'address' => 'Sialkot'],
            ['name' => 'Kamran Ali', 'phone' => '0334-8889900', 'address' => 'Gujranwala'],
            ['name' => 'Hina Rashid', 'phone' => '0305-1112233', 'address' => 'Lahore'],
            ['name' => 'Faisal Mahmood', 'phone' => null, 'address' => 'Hyderabad'],
            ['name' => 'Nadia Parvez', 'phone' => '0311-4455667', 'address' => 'Sargodha'],
            ['name' => 'Tariq Mehmood', 'phone' => '0342-9990011', 'address' => null],
            ['name' => 'Rabia Aslam', 'phone' => null, 'address' => 'Bahawalpur'],
            ['name' => 'Adnan Farooq', 'phone' => '0331-3344556', 'address' => 'Lahore'],
            ['name' => 'Saima Iqbal', 'phone' => '0306-6677889', 'address' => 'Karachi'],
            ['name' => 'Waqas Jamil', 'phone' => null, 'address' => 'Islamabad'],
            ['name' => 'Noreen Bibi', 'phone' => '0320-9988776', 'address' => 'Faisalabad'],
            ['name' => 'Shahid Rafique', 'phone' => '0341-5544332', 'address' => null],
            ['name' => 'Amna Khalid', 'phone' => null, 'address' => 'Multan'],
            ['name' => 'Javed Akram', 'phone' => '0314-2211009', 'address' => 'Rawalpindi'],
            ['name' => 'Sara Tariq', 'phone' => '0335-7788990', 'address' => 'Peshawar'],
            ['name' => 'Omar Nadeem', 'phone' => null, 'address' => 'Quetta'],
        ];

        $created = [];
        foreach ($customers as $data) {
            $created[] = LedgerCustomer::create($data);
        }

        // Add transactions for first two customers (Ali Hassan and Fatima Khan)
        $descriptions = [
            'Oil payment',
            'Advance for next order',
            'Settled previous balance',
            'Partial payment',
            'Full payment received',
            'Cash on delivery',
            'Credit note adjustment',
            'Monthly settlement',
        ];

        foreach ([0, 1] as $index) {
            $customer = $created[$index];
            $baseDate = Carbon::now()->subDays(rand(5, 25));

            // 5–8 transactions per customer
            $count = rand(5, 8);
            for ($i = 0; $i < $count; $i++) {
                $type = rand(0, 1) ? LedgerTransaction::TYPE_RECEIVED : LedgerTransaction::TYPE_GAVE;
                $amount = (float) (rand(1, 100) * 500); // 500 to 50000 in steps of 500
                $transactionDate = $baseDate->copy()->addDays($i)->setTime(rand(9, 18), rand(0, 59));

                LedgerTransaction::create([
                    'ledger_customer_id' => $customer->id,
                    'type' => $type,
                    'amount' => $amount,
                    'description' => $descriptions[array_rand($descriptions)],
                    'transaction_date' => $transactionDate,
                ]);
            }
        }
    }
}
