<?php

namespace Modules\CategoryManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\Banner;
use Modules\ServiceManagement\Entities\Product;
use Modules\ServiceManagement\Entities\ProductCategory;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ZoneManagement\Entities\Zone;

/**
 * Seeds complete Clean365 catalog, banners, packages, categories, and store products matching the exact official UI flyer.
 *
 * Safe & Idempotent with Full Reset of existing categories, services, variations, banners & products.
 *
 * Command to run:
 *   php artisan db:seed --class="Modules\\CategoryManagement\\Database\\Seeders\\Clean365CatalogSeeder"
 */
class Clean365CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Clean365CatalogSeeder: Resetting & seeding all official flyer data & packages...');

        // 1. Safe Reset of existing catalog, banners & store data
        $this->resetData();

        DB::transaction(function () {
            // 2. Ensure at least one Active Zone exists
            $zones = $this->ensureZoneExists();

            // 3. Create Placeholder Images on Public Storage
            $this->ensureImagesExist();

            // 4. Seed Categories (الأقسام الرئيسية والخدمات بالكامل من البروشور الرسمي)
            $categories = $this->seedCategories($zones);

            // 5. Seed Services (الباقات المتعددة والأسعار الرسمية المعتمدة لكل قسم)
            $this->seedServices($categories, $zones);

            // 6. Seed Banners (البانرات الرئيسية والعروض)
            $this->seedBanners($categories['studio'] ?? reset($categories));

            // 7. Seed Store (المتجر والمنتجات)
            $this->seedStore();
        });

        $this->command?->info('Clean365CatalogSeeder: Seeding completed successfully!');
    }

    /**
     * Resets old categories, services, variations, banners, and products cleanly.
     */
    private function resetData(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('variations')->delete();
        DB::table('services')->delete();
        DB::table('categories')->delete();
        DB::table('banners')->delete();
        DB::table('products')->delete();
        DB::table('product_categories')->delete();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Ensures an active zone exists for price variations.
     */
    private function ensureZoneExists()
    {
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
        return $zones;
    }

    /**
     * Create placeholder 1x1 image files in public storage so getSingleImageFullPath returns valid URLs.
     */
    private function ensureImagesExist(): void
    {
        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $pngBinary = base64_decode($pngBase64);

        $disk = Storage::disk('public');

        $paths = [
            'category/studio.png',
            'category/one-bedroom.png',
            'category/two-bedroom.png',
            'category/three-bedroom.png',
            'category/four-bedroom.png',
            'category/small-duplex-villa.png',
            'category/deep-cleaning.png',
            'category/outdoor-care.png',
            'category/specialized-services.png',
            'service/studio.png',
            'service/one-bedroom.png',
            'service/two-bedroom.png',
            'service/three-bedroom.png',
            'service/four-bedroom.png',
            'service/small-duplex-villa.png',
            'service/move-in.png',
            'service/post-construction.png',
            'service/move-out.png',
            'service/deep-clean.png',
            'service/pool.png',
            'service/garden.png',
            'service/tile.png',
            'service/tree.png',
            'service/ac.png',
            'service/glass.png',
            'service/sofa.png',
            'service/sanitize.png',
            'banner/hero-banner.png',
            'banner/promo-banner.png',
            'product/bedding-set.png',
            'product/pillow.png',
            'product/towel-set.png',
            'product/air-freshener.png',
            'product/candle.png',
            'product/diffuser.png',
            'product-category/air-fresheners.png',
            'product-category/candles.png',
            'product-category/pillows-bedding.png',
            'product-category/bath-accessories.png',
            'product-category/hospitality-decor.png',
            'product-category/towels-decor.png',
        ];

        foreach ($paths as $path) {
            if (!$disk->exists($path)) {
                $disk->put($path, $pngBinary);
            }
        }
    }

    /**
     * Seeds Main Categories (الأقسام المعتمدة بالبروشور الرسمي) with their exact includes & features.
     * @return array<string, Category>
     */
    private function seedCategories($zones): array
    {
        $categoryDefs = [
            'studio' => [
                'name' => 'استوديو',
                'slug' => 'studio',
                'description' => 'باقة نظافة وتدبير فندقي متكامل للاستوديو',
                'image' => 'category/studio.png',
                'starting_price' => 188.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'معطر فاخر يدوم طويلاً', 'icon' => 'icons/fragrance.svg'],
                    ['title' => 'الأسطح والزجاج', 'icon' => 'icons/glass.svg'],
                    ['title' => 'تنظيف المطبخ والمعيشة', 'icon' => 'icons/cleaning.svg'],
                    ['title' => 'تطهير دورة المياه', 'icon' => 'icons/bath.svg'],
                    ['title' => 'العناية بغرفة النوم', 'icon' => 'icons/bed.svg'],
                ],
            ],
            'one-bedroom' => [
                'name' => 'غرفة وصالة',
                'slug' => 'one-bedroom',
                'description' => 'باقة نظافة وتدبير فندقي لغرفة وصالة — الأكثر طلباً ⭐',
                'image' => 'category/one-bedroom.png',
                'starting_price' => 245.00,
                'position' => 1,
                'is_featured' => 1,
                'includes' => [
                    ['title' => 'معطر فاخر', 'icon' => 'icons/fragrance.svg'],
                    ['title' => 'كنس ومسح الأرضيات', 'icon' => 'icons/cleaning.svg'],
                    ['title' => 'تنظيف المطبخ والرخام', 'icon' => 'icons/kitchen.svg'],
                    ['title' => 'تطهير وتعقيم دورات المياه', 'icon' => 'icons/bath.svg'],
                    ['title' => 'ترتيب السرير وتنسيق المفارش', 'icon' => 'icons/bed.svg'],
                ],
            ],
            'two-bedroom' => [
                'name' => 'غرفتين وصالة',
                'slug' => 'two-bedroom',
                'description' => 'باقة نظافة فندقية متكاملة للشقق المكونة من غرفتين وصالة',
                'image' => 'category/two-bedroom.png',
                'starting_price' => 301.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'معطر فاخر يدوم طويلاً', 'icon' => 'icons/fragrance.svg'],
                    ['title' => 'الأرضيات والزجاج', 'icon' => 'icons/glass.svg'],
                    ['title' => 'تنظيف المطبخ والكونترتوب', 'icon' => 'icons/kitchen.svg'],
                    ['title' => 'تطهير وتعقيم دورات المياه', 'icon' => 'icons/bath.svg'],
                    ['title' => 'العناية بجميع غرف النوم', 'icon' => 'icons/bed.svg'],
                ],
            ],
            'three-bedroom' => [
                'name' => 'ثلاث غرف وصالة',
                'slug' => 'three-bedroom',
                'description' => 'باقة نظافة فندقية راقية للشقق المكونة من ثلاث غرف وصالة',
                'image' => 'category/three-bedroom.png',
                'starting_price' => 376.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'معطر فاخر يدوم طويلاً', 'icon' => 'icons/fragrance.svg'],
                    ['title' => 'أرضيات نظيفة ومصقولة', 'icon' => 'icons/tile.svg'],
                    ['title' => 'المطبخ والرخام وغسيل الأواني', 'icon' => 'icons/kitchen.svg'],
                    ['title' => 'تطهير وتعقيم دورات المياه', 'icon' => 'icons/bath.svg'],
                    ['title' => 'تنظيف وتعقيم الممرات والغرف', 'icon' => 'icons/bed.svg'],
                ],
            ],
            'four-bedroom' => [
                'name' => 'أربع غرف وصالة',
                'slug' => 'four-bedroom',
                'description' => 'باقة نظافة فندقية شاملة للشقق الكبيرة المكونة من أربع غرف وصالة',
                'image' => 'category/four-bedroom.png',
                'starting_price' => 470.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'معطر فاخر يدوم طويلاً', 'icon' => 'icons/fragrance.svg'],
                    ['title' => 'الأرضيات والممرات', 'icon' => 'icons/cleaning.svg'],
                    ['title' => 'تنظيف المطبخ وحوض الغسيل', 'icon' => 'icons/kitchen.svg'],
                    ['title' => 'تعقيم دورات المياه بدقة', 'icon' => 'icons/bath.svg'],
                    ['title' => 'ترتيب شامل لكافة الغرف', 'icon' => 'icons/bed.svg'],
                ],
            ],
            'small-duplex-villa' => [
                'name' => 'الفلل الصغيرة والدوبلكس',
                'slug' => 'small-duplex-villa',
                'description' => 'العناية الفندقية الفاخرة والقصوى للفلل الصغيرة والدوبلكس لجميع الطوابق',
                'image' => 'category/small-duplex-villa.png',
                'starting_price' => 602.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'الأرضيات والممرات ومعطرات ملكي فاخر', 'icon' => 'icons/fragrance.svg'],
                    ['title' => 'العناية بالمطبخ والرخام والكونترتوب', 'icon' => 'icons/kitchen.svg'],
                    ['title' => 'تطهير دورات المياه وتلميع الزجاج', 'icon' => 'icons/bath.svg'],
                    ['title' => 'ترتيب الغرف والممرات ومسح الغبار الدقيق', 'icon' => 'icons/bed.svg'],
                ],
            ],
            'deep-cleaning' => [
                'name' => 'التنظيف المتخصص',
                'slug' => 'deep-cleaning',
                'description' => 'حلول احترافية متكاملة للنظافة العميقة والتشطيب',
                'image' => 'category/deep-cleaning.png',
                'starting_price' => 280.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'تنظيف عميق', 'icon' => 'icons/deep_clean.svg'],
                    ['title' => 'تنظيف ما قبل السكن', 'icon' => 'icons/move_in.svg'],
                    ['title' => 'تنظيف بعد التشطيب', 'icon' => 'icons/post_construction.svg'],
                    ['title' => 'تنظيف بعد الانتقال', 'icon' => 'icons/move_out.svg'],
                ],
            ],
            'outdoor-care' => [
                'name' => 'العناية الخارجية',
                'slug' => 'outdoor-care',
                'description' => 'خدمات العناية بالحدائق والمسابح والمحيط الخارجي للمباني',
                'image' => 'category/outdoor-care.png',
                'starting_price' => 350.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'تنظيف المسابح', 'icon' => 'icons/pool.svg'],
                    ['title' => 'تنسيق الحدائق', 'icon' => 'icons/garden.svg'],
                    ['title' => 'جلي البلاط', 'icon' => 'icons/tile.svg'],
                    ['title' => 'تقليم الأشجار', 'icon' => 'icons/tree.svg'],
                ],
            ],
            'specialized-services' => [
                'name' => 'الخدمات المتخصصة',
                'slug' => 'specialized-services',
                'description' => 'تنظيف وتعقيم المكيفات والواجهات الزجاجية والكنب والسجاد',
                'image' => 'category/specialized-services.png',
                'starting_price' => 150.00,
                'position' => 1,
                'is_featured' => 0,
                'includes' => [
                    ['title' => 'تنظيف المكيفات', 'icon' => 'icons/ac.svg'],
                    ['title' => 'تنظيف الواجهات الزجاجية', 'icon' => 'icons/glass.svg'],
                    ['title' => 'تنظيف الكنب والسجاد', 'icon' => 'icons/sofa.svg'],
                    ['title' => 'التعقيم الاحترافي', 'icon' => 'icons/sanitize.svg'],
                ],
            ],
        ];

        $zoneIds = $zones->pluck('id')->toArray();
        $categoriesMap = [];

        foreach ($categoryDefs as $key => $def) {
            DB::table('categories')->where('slug', $def['slug'])->delete();

            $cat = new Category();
            $cat->id = (string) Str::uuid();
            $cat->name = $def['name'];
            $cat->slug = $def['slug'];
            $cat->description = $def['description'];
            $cat->image = $def['image'];
            $cat->starting_price = $def['starting_price'];
            $cat->position = $def['position'];
            $cat->is_active = 1;
            $cat->is_featured = $def['is_featured'] ?? 0;
            $cat->parent_id = null;
            $cat->includes = $def['includes'];
            $cat->save();

            $cat->zones()->sync($zoneIds);

            $categoriesMap[$key] = $cat;
        }

        return $categoriesMap;
    }

    /**
     * Seeds Packages & Services for ALL categories matching the official flyer prices.
     */
    private function seedServices(array $categories, $zones): void
    {
        $servicesByCategory = [];

        // 1. باقات كروت الإقامة الفندقية بالأسعار الرسمية المكتوبة في البوستر بالظبط
        $hotelCategories = [
            'studio' => [
                'name' => 'استوديو',
                'prices' => [
                    1 => ['price' => 188.00, 'desc' => 'زيارة واحدة مفردة — تنظيف فندقي متكامل'],
                    4 => ['price' => 752.00, 'desc' => 'باقة 4 زيارات — تنظيف فندقي متكامل'],
                    8 => ['price' => 1505.00, 'desc' => 'باقة 8 زيارات — أولوية مرنة في الجدولة'],
                    12 => ['price' => 2257.00, 'desc' => 'باقة 12 زيارة — العناية القصوى المستمرة'],
                ],
                'includes' => ['معطر فاخر يدوم طويلاً', 'الأسطح والزجاج', 'تنظيف المطبخ والمعيشة', 'تطهير دورة المياه', 'العناية بغرفة النوم']
            ],
            'one-bedroom' => [
                'name' => 'غرفة وصالة',
                'prices' => [
                    1 => ['price' => 245.00, 'desc' => 'زيارة واحدة مفردة — تنظيف فندقي لغرفة وصالة'],
                    4 => ['price' => 978.00, 'desc' => 'باقة 4 زيارات — تغطية شهرية منتظمة ومريحة'],
                    8 => ['price' => 1956.00, 'desc' => 'باقة 8 زيارات — جدولة مكثفة للمحافظة على الرونق'],
                    12 => ['price' => 2935.00, 'desc' => 'باقة 12 زيارة — اهتمام دوري متكامل ومستدام'],
                ],
                'includes' => ['معطر فاخر', 'كنس ومسح الأرضيات', 'تنظيف المطبخ والرخام', 'تطهير وتعقيم دورات المياه', 'ترتيب السرير وتنسيق المفارش']
            ],
            'two-bedroom' => [
                'name' => 'غرفتين وصالة',
                'prices' => [
                    1 => ['price' => 301.00, 'desc' => 'زيارة واحدة مفردة — تنظيف فندقي لغرفتين وصالة'],
                    4 => ['price' => 1204.00, 'desc' => 'باقة 4 زيارات — العناية الأساسية المنتظمة'],
                    8 => ['price' => 2408.00, 'desc' => 'باقة 8 زيارات — مثالية للمحافظة المستمرة'],
                    12 => ['price' => 3612.00, 'desc' => 'باقة 12 زيارة — التغطية القصوى الشاملة'],
                ],
                'includes' => ['معطر فاخر يدوم طويلاً', 'الأرضيات والزجاج', 'تنظيف المطبخ والكونترتوب', 'تطهير وتعقيم دورات المياه', 'العناية بجميع غرف النوم']
            ],
            'three-bedroom' => [
                'name' => 'ثلاث غرف وصالة',
                'prices' => [
                    1 => ['price' => 376.00, 'desc' => 'زيارة واحدة مفردة — تنظيف فندقي لثلاث غرف وصالة'],
                    4 => ['price' => 1550.00, 'desc' => 'باقة 4 زيارات — اهتمام دوري منتظم للعائلة'],
                    8 => ['price' => 3010.00, 'desc' => 'باقة 8 زيارات — عناية مكثفة لكافة الغرف والممرات'],
                    12 => ['price' => 4515.00, 'desc' => 'باقة 12 زيارة — التغطية الفندقية القصوى'],
                ],
                'includes' => ['معطر فاخر يدوم طويلاً', 'أرضيات نظيفة ومصقولة', 'المطبخ والرخام وغسيل الأواني', 'تطهير وتعقيم دورات المياه', 'تنظيف وتعقيم الممرات وغرف النوم']
            ],
            'four-bedroom' => [
                'name' => 'أربع غرف وصالة',
                'prices' => [
                    1 => ['price' => 470.00, 'desc' => 'زيارة واحدة مفردة — تنظيف فندقي لأربع غرف وصالة'],
                    4 => ['price' => 1881.00, 'desc' => 'باقة 4 زيارات — التغطية الدورية لراحة عائلية'],
                    8 => ['price' => 3762.00, 'desc' => 'باقة 8 زيارات — جدولة مرنة للمحافظة على الفخامة'],
                    12 => ['price' => 5644.00, 'desc' => 'باقة 12 زيارة — العناية القصوى المستدامة'],
                ],
                'includes' => ['معطر فاخر يدوم طويلاً', 'الأرضيات والممرات', 'تنظيف المطبخ وحوض الغسيل', 'تعقيم دورات المياه بدقة', 'ترتيب شامل لكافة الغرف']
            ],
            'small-duplex-villa' => [
                'name' => 'الفلل الصغيرة والدوبلكس',
                'prices' => [
                    1 => ['price' => 602.00, 'desc' => 'زيارة واحدة مفردة — تنظيف فندقي للفلل الصغيرة والدوبلكس'],
                    4 => ['price' => 2408.00, 'desc' => 'باقة 4 زيارات — صيانة ونظافة دورية لجميع الطوابق'],
                    8 => ['price' => 4816.00, 'desc' => 'باقة 8 زيارات — جدولة مخصصة مرنة للحفاظ على الرونق'],
                    12 => ['price' => 7224.00, 'desc' => 'باقة 12 زيارة — العناية الفندقية الفاخرة والقصوى'],
                ],
                'includes' => ['الأرضيات والممرات ومعطرات ملكي فاخر', 'العناية بالمطبخ والرخام والكونترتوب', 'تطهير دورات المياه وتلميع الزجاج', 'ترتيب الغرف والممرات ومسح الغبار الدقيق']
            ],
        ];

        foreach ($hotelCategories as $catSlug => $info) {
            $servicesByCategory[$catSlug] = [
                [
                    'name' => 'باقة زيارة واحدة — ' . $info['name'],
                    'slug' => $catSlug . '-single-visit',
                    'short_description' => $info['name'] . ' — ' . $info['prices'][1]['desc'] . ' بسعر ' . $info['prices'][1]['price'] . ' ر.س',
                    'description' => $info['prices'][1]['desc'],
                    'price' => $info['prices'][1]['price'],
                    'visits_count' => 1,
                    'image' => 'service/' . $catSlug . '.png',
                    'includes' => $info['includes'],
                ],
                [
                    'name' => 'باقة 4 زيارات — ' . $info['name'],
                    'slug' => $catSlug . '-4-visits',
                    'short_description' => $info['name'] . ' — ' . $info['prices'][4]['desc'] . ' بسعر ' . $info['prices'][4]['price'] . ' ر.س',
                    'description' => $info['prices'][4]['desc'],
                    'price' => $info['prices'][4]['price'],
                    'visits_count' => 4,
                    'image' => 'service/' . $catSlug . '.png',
                    'includes' => $info['includes'],
                ],
                [
                    'name' => 'باقة 8 زيارات — ' . $info['name'],
                    'slug' => $catSlug . '-8-visits',
                    'short_description' => $info['name'] . ' — ' . $info['prices'][8]['desc'] . ' بسعر ' . $info['prices'][8]['price'] . ' ر.س',
                    'description' => $info['prices'][8]['desc'],
                    'price' => $info['prices'][8]['price'],
                    'visits_count' => 8,
                    'image' => 'service/' . $catSlug . '.png',
                    'includes' => $info['includes'],
                ],
                [
                    'name' => 'باقة 12 زيارة — ' . $info['name'],
                    'slug' => $catSlug . '-12-visits',
                    'short_description' => $info['name'] . ' — ' . $info['prices'][12]['desc'] . ' بسعر ' . $info['prices'][12]['price'] . ' ر.س',
                    'description' => $info['prices'][12]['desc'],
                    'price' => $info['prices'][12]['price'],
                    'visits_count' => 12,
                    'image' => 'service/' . $catSlug . '.png',
                    'includes' => $info['includes'],
                ],
            ];
        }

        // 2. باقات التنظيف المتخصص (Deep Cleaning)
        $deepServices = [
            [
                'name' => 'تنظيف عميق',
                'slug' => 'deep-clean',
                'price' => 280.00,
                'image' => 'service/deep-clean.png',
                'includes' => ['تنظيف زوايا وأماكن صعبة', 'غسيل وتعقيم الأسطح', 'تلميع الأثاث والأرضيات'],
                'desc' => 'عناية فائقة وتنظيف عميق لكافة التفاصيل والأركان الصعبة في البيت.',
            ],
            [
                'name' => 'تنظيف ما قبل السكن',
                'slug' => 'move-in-cleaning',
                'price' => 450.00,
                'image' => 'service/move-in.png',
                'includes' => ['تنظيف الأرضيات والأسقف', 'تعقيم المطبخ والحمامات', 'تلميع النوافذ والشبابيك'],
                'desc' => 'خدمة تنظيف وتطهير شامل لكافة أركان المنزل قبل الانتقال وتأمين بيئة صحية ونظيفة.',
            ],
            [
                'name' => 'تنظيف بعد التشطيب',
                'slug' => 'post-construction-cleaning',
                'price' => 650.00,
                'image' => 'service/post-construction.png',
                'includes' => ['إزالة آثار الدهان والغراء', 'جلي وتنظيف الأرضيات', 'تلميع الزجاج والواجهات'],
                'desc' => 'إزالة آثار ومخلفات البناء والدهان والغبار الناعم بأحدث الأجهزة والتقنيات.',
            ],
            [
                'name' => 'تنظيف بعد الانتقال',
                'slug' => 'move-out-cleaning',
                'price' => 400.00,
                'image' => 'service/move-out.png',
                'includes' => ['تنظيف وتجهيز الشقة', 'إزالة البقع والرواسب', 'تعقيم وتطهير شامل'],
                'desc' => 'تنظيف واسترجاع النظافة المثالية للمكان بعد خروج الساكنين أو انتهاء فترة الإيجار.',
            ],
        ];

        $servicesByCategory['deep-cleaning'] = [];
        foreach ($deepServices as $item) {
            $servicesByCategory['deep-cleaning'] = array_merge(
                $servicesByCategory['deep-cleaning'],
                $this->buildPackagesForService($item['name'], $item['slug'], $item['price'], $item['image'], $item['includes'], $item['desc'])
            );
        }

        // 3. باقات العناية الخارجية (Outdoor Care)
        $outdoorServices = [
            [
                'name' => 'تنظيف المسابح',
                'slug' => 'pool-cleaning',
                'price' => 350.00,
                'image' => 'service/pool.png',
                'includes' => ['تنظيف الجدران والأرضيات', 'فحص وتعقيم المياه', 'تنظيف وغسيل الفلاتر'],
                'desc' => 'شفط الأتربة، تنظيف الجدران، قياس وزيادة نسبة الكلور وفلترة المياه.',
            ],
            [
                'name' => 'تنسيق الحدائق',
                'slug' => 'garden-landscaping',
                'price' => 400.00,
                'image' => 'service/garden.png',
                'includes' => ['قص وتقليم الأشجار', 'تنظيف وتعشيب الحدائق', 'تنسيق النباتات والزهور'],
                'desc' => 'قص وتشكيل الأشجار والزهور، قص النجيل وتنسيق الديكورات الزراعية الخارجية.',
            ],
            [
                'name' => 'جلي البلاط',
                'slug' => 'tile-polishing',
                'price' => 500.00,
                'image' => 'service/tile.png',
                'includes' => ['إزالة الأوساخ والبقع', 'جلي وتلميع الرخام والبلاط', 'معالجة الفواصل والخراسانات'],
                'desc' => 'إزالة الأوساخ المستعصية والزيوت وتلميع السيراميك والرخام الخارجي بمعدات متخصصة.',
            ],
            [
                'name' => 'تقليم الأشجار',
                'slug' => 'tree-pruning',
                'price' => 250.00,
                'image' => 'service/tree.png',
                'includes' => ['تقليم الفروع الزائدة', 'تنسيق الشكل الخارجي', 'التنظيف والتخلص من المخلفات'],
                'desc' => 'قص وتقليم فروع الأشجار المرتفعة والتخلص الآمن من مخلفات التقليم.',
            ],
        ];

        $servicesByCategory['outdoor-care'] = [];
        foreach ($outdoorServices as $item) {
            $servicesByCategory['outdoor-care'] = array_merge(
                $servicesByCategory['outdoor-care'],
                $this->buildPackagesForService($item['name'], $item['slug'], $item['price'], $item['image'], $item['includes'], $item['desc'])
            );
        }

        // 4. باقات الخدمات المتخصصة (Specialized Services)
        $specializedServices = [
            [
                'name' => 'تنظيف المكيفات',
                'slug' => 'ac-cleaning',
                'price' => 150.00,
                'image' => 'service/ac.png',
                'includes' => ['غسيل الوحدة الداخلية والخارجية', 'تنظيف الفلاتر ومسار الصرف', 'تعقيم وتعطير المجرى'],
                'desc' => 'تنظيف مكيفات السبلت والشباك بأجهزة الضغط العالي مع التعقيم العطري.',
            ],
            [
                'name' => 'تنظيف الواجهات الزجاجية',
                'slug' => 'glass-cleaning',
                'price' => 300.00,
                'image' => 'service/glass.png',
                'includes' => ['تلميع الواجهات الزجاجية', 'إزالة الأتربة والأملاح', 'استخدام معاجين حماية'],
                'desc' => 'تنظيف وتلميع الواجهات والشبابيك الزجاجية بأجهزة ومعدات السلامة الاحترافية.',
            ],
            [
                'name' => 'تنظيف الكنب والسجاد',
                'slug' => 'sofa-carpet-cleaning',
                'price' => 220.00,
                'image' => 'service/sofa.png',
                'includes' => ['إزالة البقع الصعبة', 'غسيل بالبخار والشفط', 'تعقيم وتجفيف سريع'],
                'desc' => 'إزالة البقع الصعبة والرائحة من الأقمشة والسجاد بتقنية الاستخلاص الحراري والبخار.',
            ],
            [
                'name' => 'التعقيم الاحترافي',
                'slug' => 'professional-sanitization',
                'price' => 300.00,
                'image' => 'service/sanitize.png',
                'includes' => ['رش معقمات طبية وآمنة', 'تطهير مقابض الأبواب والأسطح', 'تعطير وتنقيه الهواء'],
                'desc' => 'رش وتقطير مواد معقمة وآمنة تماماً على الصحة ومعتمدة عالمياً.',
            ],
        ];

        $servicesByCategory['specialized-services'] = [];
        foreach ($specializedServices as $item) {
            $servicesByCategory['specialized-services'] = array_merge(
                $servicesByCategory['specialized-services'],
                $this->buildPackagesForService($item['name'], $item['slug'], $item['price'], $item['image'], $item['includes'], $item['desc'])
            );
        }

        // حفظ جميع الخدمات والباقات في قاعدة البيانات
        foreach ($servicesByCategory as $catSlug => $packages) {
            if (!isset($categories[$catSlug])) {
                continue;
            }
            $category = $categories[$catSlug];

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
                $service->visits_count = $pkg['visits_count'] ?? 1;
                $service->is_active = 1;
                $service->service_includes = $pkg['includes'];
                $service->save();

                // Create Variations for all active zones
                foreach ($zones as $zone) {
                    $variation = new Variation();
                    $variation->service_id = $service->id;
                    $variation->zone_id = $zone->id;
                    $variation->variant = 'default';
                    $variation->variant_key = 'default';
                    $variation->price = $pkg['price'];
                    $variation->save();
                }
            }
        }
    }

    /**
     * Builds standard 4 visits package tiers for non-hotel services.
     */
    private function buildPackagesForService(
        string $serviceName,
        string $slugPrefix,
        float $basePrice,
        string $image,
        array $includes,
        string $description
    ): array {
        return [
            [
                'name' => 'باقة زيارة واحدة — ' . $serviceName,
                'slug' => $slugPrefix . '-single-visit',
                'short_description' => $serviceName . ' — زيارة واحدة بسعر ' . $basePrice . ' ر.س',
                'description' => $description,
                'price' => $basePrice,
                'visits_count' => 1,
                'image' => $image,
                'includes' => $includes,
            ],
            [
                'name' => 'باقة 4 زيارات — ' . $serviceName,
                'slug' => $slugPrefix . '-4-visits',
                'short_description' => $serviceName . ' — باقة 4 زيارات شهرية بسعر ' . round($basePrice * 4 * 0.9) . ' ر.س',
                'description' => $description . ' (باقة 4 زيارات مخصصة لراحة أكثر)',
                'price' => round($basePrice * 4 * 0.9, 2),
                'visits_count' => 4,
                'image' => $image,
                'includes' => $includes,
            ],
            [
                'name' => 'باقة 8 زيارات — ' . $serviceName,
                'slug' => $slugPrefix . '-8-visits',
                'short_description' => $serviceName . ' — باقة 8 زيارات بسعر ' . round($basePrice * 8 * 0.85) . ' ر.س',
                'description' => $description . ' (باقة 8 زيارات شهرية بنسبة خصم 15%)',
                'price' => round($basePrice * 8 * 0.85, 2),
                'visits_count' => 8,
                'image' => $image,
                'includes' => $includes,
            ],
            [
                'name' => 'باقة 12 زيارة — ' . $serviceName,
                'slug' => $slugPrefix . '-12-visits',
                'short_description' => $serviceName . ' — باقة 12 زيارة بسعر ' . round($basePrice * 12 * 0.8) . ' ر.س',
                'description' => $description . ' (باقة 12 زيارة للتوفير الأقصى والجودة المستمرة)',
                'price' => round($basePrice * 12 * 0.8, 2),
                'visits_count' => 12,
                'image' => $image,
                'includes' => $includes,
            ],
        ];
    }

    /**
     * Seeds Hero Banner & Promotional Banner matching the UI image.
     */
    private function seedBanners(Category $heroCat): void
    {
        // 1. Hero Top Banner
        $heroBanner = new Banner();
        $heroBanner->id = (string) Str::uuid();
        $heroBanner->banner_title = 'احجز خدمة التنظيف الآن — نظافة فندقية... راحة تدوم';
        $heroBanner->resource_type = 'category';
        $heroBanner->resource_id = $heroCat->id;
        $heroBanner->redirect_link = '/services';
        $heroBanner->banner_image = 'hero-banner.png';
        $heroBanner->is_active = 1;
        $heroBanner->save();

        // 2. Promotional Discount Banner
        $promoBanner = new Banner();
        $promoBanner->id = (string) Str::uuid();
        $promoBanner->banner_title = 'عروض خاصة | خصم حتى 30% على الباقات المختارة — راحة أكثر... بسعر أفضل';
        $promoBanner->resource_type = 'link';
        $promoBanner->resource_id = null;
        $promoBanner->redirect_link = '/offers';
        $promoBanner->banner_image = 'promo-banner.png';
        $promoBanner->is_active = 1;
        $promoBanner->save();
    }

    /**
     * Seeds Store Product Categories & Products matching the store section in the UI.
     */
    private function seedStore(): void
    {
        $storeCategories = [
            'air-fresheners' => ['name' => 'معطرات الجو', 'image' => 'air-fresheners.png'],
            'candles' => ['name' => 'الشموع المعطرة', 'image' => 'candles.png'],
            'pillows-bedding' => ['name' => 'الوسائد والبياضات', 'image' => 'pillows-bedding.png'],
            'bath-accessories' => ['name' => 'إكسسوارات الحمام', 'image' => 'bath-accessories.png'],
            'hospitality-decor' => ['name' => 'الضيافة والديكور', 'image' => 'hospitality-decor.png'],
            'towels-decor' => ['name' => 'مناشف والديكور', 'image' => 'towels-decor.png'],
        ];

        $catMap = [];
        foreach ($storeCategories as $key => $def) {
            $cat = new ProductCategory();
            $cat->id = (string) Str::uuid();
            $cat->name = $def['name'];
            $cat->image = $def['image'];
            $cat->is_active = 1;
            $cat->save();

            $catMap[$key] = $cat;
        }

        $products = [
            [
                'name' => 'طقم مفارش فندقية 6 قطع',
                'short_description' => 'طقم مفارش فندقية فاخرة 6 قطع مصنوع من قطن ناعم 100%',
                'price' => 249.00,
                'sale_price' => null,
                'badge' => 'فندقي',
                'rating' => 5.0,
                'thumbnail' => 'bedding-set.png',
                'category' => $catMap['pillows-bedding']->id,
            ],
            [
                'name' => 'وسادة فندقية فاخرة',
                'short_description' => 'وسادة فندقية فاخرة تمنحك إحساساً رائعاً بالراحة والدعم للرقبة',
                'price' => 99.00,
                'sale_price' => null,
                'badge' => null,
                'rating' => 4.9,
                'thumbnail' => 'pillow.png',
                'category' => $catMap['pillows-bedding']->id,
            ],
            [
                'name' => 'طقم مناشف فندقية قطن 100%',
                'short_description' => 'طقم مناشف فندقية قطنية فاخرة فائقة الامتصاص والنعومة',
                'price' => 159.00,
                'sale_price' => 129.00,
                'badge' => 'تخفيض',
                'rating' => 5.0,
                'thumbnail' => 'towel-set.png',
                'category' => $catMap['towels-decor']->id,
            ],
            [
                'name' => 'معطر جو فندقي 500 مل',
                'short_description' => 'معطر جو فندقي فاخر يعزز الانتعاش برائحة تدوم طويلاً',
                'price' => 79.00,
                'sale_price' => null,
                'badge' => null,
                'rating' => 4.8,
                'thumbnail' => 'air-freshener.png',
                'category' => $catMap['air-fresheners']->id,
            ],
            [
                'name' => 'شمعة معطرة برائحة فاخرة',
                'short_description' => 'شمعة معطرة طبيعية تمنح المكان أجوائاً دافئة وأنيقة',
                'price' => 69.00,
                'sale_price' => null,
                'badge' => null,
                'rating' => 4.9,
                'thumbnail' => 'candle.png',
                'category' => $catMap['candles']->id,
            ],
            [
                'name' => 'معطر جو فاخر 500 مل',
                'short_description' => 'فواحة معطر جو فندقي بالعود والنوتات العطرية العميقة والفاخرة',
                'price' => 89.00,
                'sale_price' => null,
                'badge' => null,
                'rating' => 5.0,
                'thumbnail' => 'diffuser.png',
                'category' => $catMap['air-fresheners']->id,
            ],
        ];

        foreach ($products as $prodDef) {
            $product = new Product();
            $product->id = (string) Str::uuid();
            $product->name = $prodDef['name'];
            $product->short_description = $prodDef['short_description'];
            $product->price = $prodDef['price'];
            $product->sale_price = $prodDef['sale_price'];
            $product->badge = $prodDef['badge'];
            $product->rating = $prodDef['rating'];
            $product->thumbnail = $prodDef['thumbnail'];
            $product->product_category_id = $prodDef['category'];
            $product->is_active = 1;
            $product->save();
        }
    }
}
