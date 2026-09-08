<?php

namespace Modules\ServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ZoneManagement\Entities\Zone;

/**
 * Standalone Seeder dedicated to Services & Packages with 5 features and visits_count.
 *
 * Command to run:
 *   php artisan db:seed --class="Modules\\ServiceManagement\\Database\\Seeders\\Clean365ServicesSeeder"
 */
class Clean365ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Clean365ServicesSeeder: Seeding services with visits_count and 5 features...');

        DB::transaction(function () {
            $zones = Zone::query()->where('is_active', 1)->get();
            if ($zones->isEmpty()) {
                $zone = new Zone();
                $zone->id = (string) Str::uuid();
                $zone->name = 'منطقة الرياض';
                $zone->display_name = 'الرياض';
                $zone->is_active = 1;
                $zone->save();
                $zones = collect([$zone]);
            }

            $categories = Category::query()->whereNull('parent_id')->get()->keyBy('slug');

            $servicesData = [
                'hotel-cleaning' => [
                    [
                        'name' => 'استوديو',
                        'slug' => 'studio-package',
                        'short_description' => 'باقة تنظيف شاملة للاستوديو — ابتداء من 499 ر.س',
                        'description' => 'باقة مرنة تناسب الاستوديو تشمل التنظيف الفندقي الشامل وترتيب الأسرة وتعقيم الحمامات وتعطير فاخر.',
                        'price' => 499.00,
                        'visits_count' => 1,
                        'image' => 'service/studio.png',
                        'includes' => [
                            'تنظيف فندقي شامل لجميع المساحات',
                            'ترتيب الأسرة وتبديل الملاءات',
                            'تعقيم وتطير الحمامات بالكامل',
                            'تلميع المرايا والأجزاء الزجاجية',
                            'تعطير فاخر يدوم طويلاً',
                        ],
                    ],
                    [
                        'name' => 'غرفة وصالة',
                        'slug' => 'one-bedroom-package',
                        'short_description' => 'باقة تنظيف شاملة لغرفة وصالة — الأكثر طلباً ⭐',
                        'description' => 'الباقة الأكثر طلباً لغرفة وصالة تشمل التنظيف الفندقي الشامل والعناية أدق التفاصيل.',
                        'price' => 599.00,
                        'visits_count' => 2,
                        'image' => 'service/one-bedroom.png',
                        'includes' => [
                            'تنظيف عميق للغرفة والصالة',
                            'ترتيب السرير والعناية بالأثاث',
                            'تنظيف وتطير المطبخ والحمام',
                            'شفط الأتربة وجلي الأرضيات',
                            'تعطير فندقي ملكي للمكان',
                        ],
                    ],
                    [
                        'name' => 'غرفتين وصالة',
                        'slug' => 'two-bedroom-package',
                        'short_description' => 'باقة تنظيف شاملة لغرفتين وصالة — ابتداء من 914 ر.س',
                        'description' => 'باقة متكاملة للشقق المكونة من غرفتين وصالة لراحة وتجربة فندقية فاخرة.',
                        'price' => 914.00,
                        'visits_count' => 4,
                        'image' => 'service/two-bedroom.png',
                        'includes' => [
                            'تنظيف شامل لغرفتين وصالة واسعة',
                            'ترتيب وتنظيف الأسرة والدواليب',
                            'تعقيم كامل للحمامات والمطبخ',
                            'غسيل وتلميع الأرضيات والنوافذ',
                            'لمسات فندقية راقية وتعطير فاخر',
                        ],
                    ],
                    [
                        'name' => 'ثلاث غرف وصالة',
                        'slug' => 'three-bedroom-package',
                        'short_description' => 'باقة تنظيف شاملة لثلاث غرف وصالة — ابتداء من 1,200 ر.س',
                        'description' => 'باقة راقية للشقق الكبيرة المكونة من ثلاث غرف وصالة بأفضل معايير الجودة الفندقية.',
                        'price' => 1200.00,
                        'visits_count' => 4,
                        'image' => 'service/three-bedroom.png',
                        'includes' => [
                            'عناية متكاملة لـ 3 غرف وصالة مجالس',
                            'ترتيب وتنسيق كافة الأسرة والغرف',
                            'تعقيم دقيق للحمامات والمطابخ',
                            'تلميع الأثاث الخشبي والزجاجي',
                            'تعطير خاص برائحة فندقية مميزة',
                        ],
                    ],
                ],
                'deep-cleaning' => [
                    [
                        'name' => 'تنظيف ما قبل السكن',
                        'slug' => 'move-in-deep-cleaning',
                        'short_description' => 'تنظيف وتعقيم كامل قبل الانتقال للسكن — ابتداء من 450 ر.س',
                        'description' => 'خدمة تنظيف وتطهير شامل لكافة أركان المنزل قبل الانتقال وتأمين بيئة صحية ونظيفة.',
                        'price' => 450.00,
                        'visits_count' => 1,
                        'image' => 'service/move-in.png',
                        'includes' => [
                            'تنظيف وتطهير شامل قبل الانتقال',
                            'غسيل وتلميع الأرضيات والأسقف',
                            'إزالة الأتربة ورواسب البناء العالقة',
                            'تعقيم وتطير المطبخ والحمامات',
                            'تلميع النوافذ والشبابيك بالكامل',
                        ],
                    ],
                    [
                        'name' => 'تنظيف بعد التشطيب',
                        'slug' => 'post-construction-cleaning',
                        'short_description' => 'إزالة غبار الدهانات وبقايا البناء والتشطيب — ابتداء من 650 ر.س',
                        'description' => 'إزالة آثار ومخلفات البناء والدهان والغبار الناعم بأحدث الأجهزة والتقنيات.',
                        'price' => 650.00,
                        'visits_count' => 1,
                        'image' => 'service/post-construction.png',
                        'includes' => [
                            'إزالة بقايا الدهانات والدهان والغراء',
                            'شفط وتفتيت الغبار الناعم بأجهزة حديثة',
                            'جلي وتلميع جميع أنواع السيراميك',
                            'تنظيف وتلميع الواجهات والزجاج الداخلي',
                            'تعقيم كافة المرافق والأبواب بمسح شامل',
                        ],
                    ],
                    [
                        'name' => 'تنظيف بعد الانتقال',
                        'slug' => 'move-out-cleaning',
                        'short_description' => 'تنظيف عميق وشامل للوحدة السكنية بعد المغادرة — ابتداء من 400 ر.س',
                        'description' => 'تنظيف واسترجاع النظافة المثالية للمكان بعد خروج الساكنين أو انتهاء فترة الإيجار.',
                        'price' => 400.00,
                        'visits_count' => 1,
                        'image' => 'service/move-out.png',
                        'includes' => [
                            'تنظيف واسترجاع النظافة الأصلية للوحدة',
                            'إزالة البقع المستعصية والرواسب القديمة',
                            'تنظيف دواليب المطبخ والحمامات',
                            'غسيل الأرضيات والجدران المتسخة',
                            'تجهيز كامل للشقة للتسليم أو التأجير',
                        ],
                    ],
                    [
                        'name' => 'تنظيف عميق شامل',
                        'slug' => 'full-deep-cleaning-package',
                        'short_description' => 'خدمة النظافة العميقة للمنازل والفلل — ابتداء من 280 ر.س',
                        'description' => 'عناية فائقة وتنظيف عميق لكافة التفاصيل والأركان الصعبة في البيت.',
                        'price' => 280.00,
                        'visits_count' => 2,
                        'image' => 'service/deep-clean.png',
                        'includes' => [
                            'تنظيف دقيق لجميع الزوايا والأركان الصعبة',
                            'غسيل وتعقيم الأسطح والأثاث الثابت',
                            'إزالة الدهون والتكلسات من المطبخ',
                            'تلميع السيراميك والرخام بفرش متخصصة',
                            'معالجة الروائح الكريهة وتطهير الهواء',
                        ],
                    ],
                ],
                'outdoor-care' => [
                    [
                        'name' => 'تنظيف وتطهير المسابح',
                        'slug' => 'pool-cleaning-service',
                        'short_description' => 'تنظيف وفلترة مياه المسابح وتعقيمها — ابتداء من 350 ر.س',
                        'description' => 'شفط الأتربة، تنظيف الجدران، قياس وزيادة نسبة الكلور وفلترة المياه.',
                        'price' => 350.00,
                        'visits_count' => 4,
                        'image' => 'service/pool.png',
                        'includes' => [
                            'شفط الأتربة والرواسب من قاع المسبح',
                            'تنظيف وتطهير الجدران والأرضيات',
                            'قياس وضبط مستويات الكلور والحموضة',
                            'غسيل وتنظيف الفلاتر والمضخات',
                            'تصفية مياه المسبح وضمان نقائها',
                        ],
                    ],
                    [
                        'name' => 'تنسيق وتقليم الحدائق',
                        'slug' => 'garden-landscaping-service',
                        'short_description' => 'العناية بالحدائق والمسطحات الخضراء — ابتداء من 400 ر.س',
                        'description' => 'قص وتشكيل الأشجار والزهور، قص النجيل وتنسيق الديكورات الزراعية الخارجية.',
                        'price' => 400.00,
                        'visits_count' => 2,
                        'image' => 'service/garden.png',
                        'includes' => [
                            'قص وتشكيل الأشجار والشجيرات الخارجية',
                            'قص وتكريب المسطحات الخضراء والجيل',
                            'إزالة الأعشاب الضارة والميتة',
                            'تنسيق النباتات والزهور وتحسين المظهر',
                            'رش مبيدات وقائية للنباتات والحديقة',
                        ],
                    ],
                    [
                        'name' => 'جلي وتلميع البلاط الخارجي',
                        'slug' => 'outdoor-tile-polishing',
                        'short_description' => 'جلي وتنظيف أرضيات الحوش والأحواش — ابتداء من 500 ر.س',
                        'description' => 'إزالة الأوساخ المستعصية والزيوت وتلميع السيراميك والرخام الخارجي بمعدات متخصصة.',
                        'price' => 500.00,
                        'visits_count' => 1,
                        'image' => 'service/tile.png',
                        'includes' => [
                            'غسيل وتلميع أرضيات الأحواش بالحارة',
                            'إزالة أثر الزيوت والدهون والأوساخ',
                            'جلي السيراميك والرخام الخارجي بمعدات حديثة',
                            'تنظيف وتنقية فواصل البلاط والأرضيات',
                            'حماية الأسطح بطبقة عازلة شمعية',
                        ],
                    ],
                ],
                'specialized-services' => [
                    [
                        'name' => 'تنظيف وصيانة المكيفات',
                        'slug' => 'ac-cleaning-service',
                        'short_description' => 'غسيل وتطهير فلاتر ومراوح المكيفات — ابتداء من 150 ر.س',
                        'description' => 'تنظيف مكيفات السبلت والشباك بأجهزة الضغط العالي مع التعقيم العطري.',
                        'price' => 150.00,
                        'visits_count' => 1,
                        'image' => 'service/ac.png',
                        'includes' => [
                            'غسيل الوحدة الداخلية والخارجية بالضغط',
                            'تنظيف الفلاتر ومجرى الصرف بالكامل',
                            'فحص مستوى فريون المكيف وتأكيد الأداء',
                            'تعقيم وتطير مجرى الهواء لمكافحة البكتيريا',
                            'تعطير وتنقيه الهواء الخارج من المكيف',
                        ],
                    ],
                    [
                        'name' => 'تنظيف الكنب والسجاد بالبخار',
                        'slug' => 'sofa-carpet-steam-cleaning',
                        'short_description' => 'غسيل وتطهير أطقم الكنب والسجاد بالبخار — ابتداء من 220 ر.س',
                        'description' => 'إزالة البقع الصعبة والرائحة من الأقمشة والسجاد بتقنية الاستخلاص الحراري والبخار.',
                        'price' => 220.00,
                        'visits_count' => 1,
                        'image' => 'service/sofa.png',
                        'includes' => [
                            'غسيل أطقم الكنب والسجاد بتقنية البخار',
                            'إزالة البقع الصعبة والزيوت المتراكمة',
                            'شفط الغبار والشوائب من عمق الأنسجة',
                            'تعقيم حراري للقضاء على العثة والميكروبات',
                            'تعطير الأقمشة برائحة عطرية منعشة',
                        ],
                    ],
                    [
                        'name' => 'التعقيم والرش الاحترافي',
                        'slug' => 'professional-sanitization-service',
                        'short_description' => 'تعقيم شامل للمنازل والمنشآت ضد البكتيريا — ابتداء من 300 ر.س',
                        'description' => 'رش وتقطير مواد معقمة وآمنة تماماً على الصحة ومعتمدة عالمياً.',
                        'price' => 300.00,
                        'visits_count' => 2,
                        'image' => 'service/sanitize.png',
                        'includes' => [
                            'رش معقمات طبية آمنة ومعتمدة من الصحة',
                            'تطهير مقابض الأبواب والأسطح الأكثر ملامسة',
                            'تعقيم غرف النوم والمجالس والمطابخ',
                            'القضاء على 99.9% من الفيروسات والبكتيريا',
                            'معالجة وتصفية هواء الغرف برائحة نقية',
                        ],
                    ],
                ],
            ];

            $categoryNames = [
                'hotel-cleaning' => 'التنظيف الفندقي',
                'deep-cleaning' => 'التنظيف المتخصص',
                'outdoor-care' => 'العناية الخارجية',
                'specialized-services' => 'الخدمات المتخصصة',
            ];

            foreach ($servicesData as $catSlug => $packages) {
                $category = Category::where('slug', $catSlug)->first();
                if (!$category) {
                    $category = new Category();
                    $category->id = (string) Str::uuid();
                    $category->name = $categoryNames[$catSlug] ?? $catSlug;
                    $category->slug = $catSlug;
                    $category->position = 1;
                    $category->is_active = 1;
                    $category->is_featured = 1;
                    $category->starting_price = $packages[0]['price'] ?? 100;
                    $category->save();
                    $category->zones()->sync($zones->pluck('id')->toArray());
                }

                // Delete any old services for this category to clean up old 0-visit records
                $oldServiceIds = DB::table('services')->where('category_id', $category->id)->pluck('id');
                DB::table('variations')->whereIn('service_id', $oldServiceIds)->delete();
                DB::table('services')->where('category_id', $category->id)->delete();

                foreach ($packages as $pkg) {
                    DB::table('services')->where('slug', $pkg['slug'])->delete();

                    $service = new Service();
                    $service->id = (string) Str::uuid();
                    $service->name = $pkg['name'];
                    $service->slug = $pkg['slug'];
                    $service->short_description = $pkg['short_description'];
                    $service->description = $pkg['description'];
                    $service->cover_image = $pkg['image'];
                    $service->thumbnail = $pkg['image'];
                    $service->category_id = $category->id;
                    $service->sub_category_id = null;
                    $service->tax = 15.00;
                    $service->min_bidding_price = $pkg['price'];
                    $service->visits_count = (int) $pkg['visits_count'];
                    $service->is_active = 1;
                    $service->service_includes = $pkg['includes'];
                    $service->save();

                    // Variations across active zones
                    foreach ($zones as $zone) {
                        $variation = new Variation();
                        $variation->service_id = $service->id;
                        $variation->zone_id = $zone->id;
                        $variation->variant = 'default';
                        $variation->variant_key = 'default';
                        $variation->price = $pkg['price'];
                        $variation->save();
                    }

                    $this->command?->info("  ✓ Seeded Service: {$service->name} ({$service->slug}) | visits_count: {$service->visits_count} | 5 features");
                }
            }
        });

        $this->command?->info('Clean365ServicesSeeder: Completed successfully!');
    }
}
