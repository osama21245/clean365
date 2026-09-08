<?php

namespace Modules\ServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ServiceManagement\Entities\AdditionalService;

class AdditionalServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'name' => 'الخدمات المتخصصة',
                'price' => 150.00,
                'sort_order' => 1,
                'features' => [
                    ['title' => 'تنظيف المكيفات', 'price' => 50.00, 'icon' => null],
                    ['title' => 'تنظيف الواجهات الزجاجية', 'price' => 80.00, 'icon' => null],
                    ['title' => 'تنظيف الكنب والسجاد', 'price' => 100.00, 'icon' => null],
                    ['title' => 'التعقيم الاحترافي', 'price' => 70.00, 'icon' => null],
                ],
            ],
            [
                'name' => 'العناية الخارجية',
                'price' => 200.00,
                'sort_order' => 2,
                'features' => [
                    ['title' => 'تنظيف المسابح', 'price' => 120.00, 'icon' => null],
                    ['title' => 'تنسيق الحدائق', 'price' => 150.00, 'icon' => null],
                    ['title' => 'جز العشب', 'price' => 60.00, 'icon' => null],
                    ['title' => 'تقليم الأشجار', 'price' => 90.00, 'icon' => null],
                ],
            ],
            [
                'name' => 'التنظيف المتخصص',
                'price' => 250.00,
                'sort_order' => 3,
                'features' => [
                    ['title' => 'تنظيف عميق', 'price' => 180.00, 'icon' => null],
                    ['title' => 'تنظيف تأهيلي قبل السكن', 'price' => 220.00, 'icon' => null],
                    ['title' => 'تنظيف بعد التشطيب', 'price' => 250.00, 'icon' => null],
                    ['title' => 'تنظيف بعد الانتقال', 'price' => 200.00, 'icon' => null],
                ],
            ],
        ];

        foreach ($services as $data) {
            AdditionalService::updateOrCreate(
                ['name' => $data['name']],
                [
                    'price' => $data['price'],
                    'features' => $data['features'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
