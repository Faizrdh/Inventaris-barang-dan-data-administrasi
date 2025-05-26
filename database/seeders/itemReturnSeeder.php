<?php

namespace Database\Seeders;

use App\Models\ItemReturn;
use App\Models\Customer; // Menggunakan Customer, bukan User
use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemReturnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to avoid duplication
        DB::table('item_returns')->truncate();
        
        // Sample data to match what we see in the screenshot
        $sampleData = [
            [
                'borrower_name' => 'Shin Tae yong',
                'item_code' => 'BRG-1329191',
                'return_date' => '2025-02-06',
                'status' => 'Baik',
            ],
            [
                'borrower_name' => 'Jay Idzes',
                'item_code' => 'BRG-1329192',
                'return_date' => '2025-02-03',
                'status' => 'Rusak',
            ],
            [
                'borrower_name' => 'Lionel Messi',
                'item_code' => 'BRG-1329194',
                'return_date' => '2025-01-01',
                'status' => 'Baik',
            ],
            [
                'borrower_name' => 'Goat',
                'item_code' => 'BRG-1329195',
                'return_date' => '2024-12-02',
                'status' => 'Rusak',
            ],
            [
                'borrower_name' => 'Phil Foden',
                'item_code' => 'BRG-1329196',
                'return_date' => '2024-08-12',
                'status' => 'Rusak',
            ],
            [
                'borrower_name' => 'Bellingham',
                'item_code' => 'BRG-1329197',
                'return_date' => '2024-08-10',
                'status' => 'Baik',
            ],
        ];
        
        // Create items and customers, then create returns
        foreach ($sampleData as $data) {
            // Ensure the item exists
            $item = Item::firstOrCreate(
                ['code' => $data['item_code']],
                [
                    'name' => 'Item ' . substr($data['item_code'], -6),
                    'description' => 'Auto-generated item for ' . $data['item_code'],
                    'category_id' => 1,
                    'status' => $data['status'],
                    'quantity' => 1,
                ]
            );
            
            // Create or find customer (menggunakan Customer model)
            $customer = Customer::firstOrCreate(
                ['name' => $data['borrower_name']],
                [
                    'phone_number' => '081231234' . rand(10000000, 99999999), // Sesuai dengan field di model
                    'address' => 'Alamat ' . $data['borrower_name'],
                ]
            );
            
            // Create the return record
            ItemReturn::create([
                'borrower_id' => $customer->id,
                'item_code' => $data['item_code'],
                'return_date' => $data['return_date'],
                'status' => $data['status'],
            ]);
            
            // Update the item status based on the return status
            $item->status = $data['status'] === 'Rusak' ? 'Rusak' : 'Tersedia';
            $item->save();
        }
        
        echo "ItemReturn seeder completed successfully!\n";
    }
}