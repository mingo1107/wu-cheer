<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立一些固定的客戶資料
        $customers = [
            [
                'company_name' => '建商A有限公司',
                'contact_person' => '王小明',
                'phone' => '02-1234-5678',
                'email' => 'wang@buildera.com.tw',
                'address' => '台北市信義區信義路五段7號',
                'tax_id' => '12345678',
                'status' => 'active',
                'notes' => '主要合作建商，信用良好',
            ],
            [
                'company_name' => '營造工程股份有限公司',
                'contact_person' => '李大華',
                'phone' => '02-2345-6789',
                'email' => 'li@construction.com.tw',
                'address' => '新北市板橋區文化路一段188號',
                'tax_id' => '23456789',
                'status' => 'active',
                'notes' => '長期合作夥伴',
            ],
            [
                'company_name' => '土地開發有限公司',
                'contact_person' => '陳小美',
                'phone' => '03-3456-7890',
                'email' => 'chen@landdev.com.tw',
                'address' => '桃園市中壢區中正路100號',
                'tax_id' => '34567890',
                'status' => 'active',
                'notes' => '新客戶，需要密切關注',
            ],
            [
                'company_name' => '建築設計事務所',
                'contact_person' => '張志明',
                'phone' => '04-4567-8901',
                'email' => 'chang@architect.com.tw',
                'address' => '台中市西區台灣大道二段285號',
                'tax_id' => '45678901',
                'status' => 'inactive',
                'notes' => '暫時停止合作',
            ],
            [
                'company_name' => '工程顧問公司',
                'contact_person' => '林雅婷',
                'phone' => '07-5678-9012',
                'email' => 'lin@consulting.com.tw',
                'address' => '高雄市前金區中正四路211號',
                'tax_id' => '56789012',
                'status' => 'active',
                'notes' => '專業工程顧問，技術優良',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // 使用工廠建立額外的隨機客戶資料
        Customer::factory(10)->create();
    }
}
