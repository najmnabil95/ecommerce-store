<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Review::truncate();
        Order::truncate();
        Product::truncate();
        Category::truncate();
        Coupon::truncate();
        User::truncate();
        Setting::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Seed Users
        $users = [
            [
                'id' => 1,
                'name' => 'مدير المتجر العام',
                'email' => 'admin@elite.com',
                'password' => Hash::make('admin123'),
                'phone' => '0500000000',
                'address' => 'المكتب الرئيسي، الرياض',
                'role' => 'admin',
            ],
            [
                'id' => 2,
                'name' => 'سارة الأحمد - مشرفة الأزياء',
                'email' => 'sara.fashion@elite.com',
                'password' => Hash::make('admin123'),
                'phone' => '0501112233',
                'address' => 'حي العليا، الرياض',
                'role' => 'admin',
            ],
            [
                'id' => 3,
                'name' => 'فيصل بن سلطان',
                'email' => 'user@example.com',
                'password' => Hash::make('user123'),
                'phone' => '0501234567',
                'address' => 'حي النخيل، فيلا 12، الرياض',
                'role' => 'customer',
            ],
            [
                'id' => 4,
                'name' => 'تركي الشمري',
                'email' => 'turki@example.com',
                'password' => Hash::make('user123'),
                'phone' => '0559876543',
                'address' => 'حي الشاطئ، جدة',
                'role' => 'customer',
            ],
            [
                'id' => 5,
                'name' => 'عمر العتيبي',
                'email' => 'omar@example.com',
                'password' => Hash::make('user123'),
                'phone' => '0567788990',
                'address' => 'حي الدوحة، الظهران',
                'role' => 'customer',
            ],
            [
                'id' => 6,
                'name' => 'عبدالعزيز الروقي',
                'email' => 'aziz@example.com',
                'password' => Hash::make('user123'),
                'phone' => '0543322110',
                'address' => 'حي المروج، الرياض',
                'role' => 'customer',
            ],
        ];

        foreach ($users as $u) {
            User::create($u);
        }

        // 2. Seed Fashion Categories
        $categories = [
            [
                'id' => 1,
                'slug' => 'formal-suits',
                'name_ar' => 'بدلات وأطقم رسمية',
                'name_en' => 'Suits & Blazers',
                'icon' => 'fa-user-tie',
            ],
            [
                'id' => 2,
                'slug' => 'traditional-thobes',
                'name_ar' => 'ثياب وأقمشة فاخرة',
                'name_en' => 'Luxury Thobes',
                'icon' => 'fa-scroll',
            ],
            [
                'id' => 3,
                'slug' => 'casual-youth',
                'name_ar' => 'كاجوال وملابس شبابية',
                'name_en' => 'Casual & Streetwear',
                'icon' => 'fa-shirt',
            ],
            [
                'id' => 4,
                'slug' => 'coats-jackets',
                'name_ar' => 'جاكيتات ومعاطف راقية',
                'name_en' => 'Jackets & Coats',
                'icon' => 'fa-vest',
            ],
            [
                'id' => 5,
                'slug' => 'footwear-leather',
                'name_ar' => 'أحذية ومصنوعات جلدية',
                'name_en' => 'Shoes & Leather',
                'icon' => 'fa-shoe-prints',
            ],
            [
                'id' => 6,
                'slug' => 'accessories-luxury',
                'name_ar' => 'أزرار وساعات وإكسسوارات',
                'name_en' => 'Cufflinks & Accessories',
                'icon' => 'fa-gem',
            ],
        ];

        foreach ($categories as $c) {
            Category::create($c);
        }

        // 3. Seed Luxury Fashion Products
        $products = [
            // Category 1: Formal Suits & Blazers
            [
                'id' => 1,
                'category_id' => 1,
                'name_ar' => 'بدلة توكسيدو ملكية من الصوف الإيطالي',
                'name_en' => 'Royal Italian Wool Tuxedo Suit',
                'description_ar' => 'بدلة سهرة فاخرة مصممة من أجود خيوط الصوف الإيطالي Super 150s مع ياقة حريرية ساتان ناعمة وقصة Slim Fit متناسقة، مثالية للمناسبات الراقية وحفلات الزفاف الفخمة.',
                'price' => 349.00,
                'original_price' => 480.00,
                'stock' => 14,
                'sizes' => ['46', '48', '50', '52', '54', '56'],
                'colors' => [
                    ['name' => 'أسود فاحم', 'hex' => '#0f172a'],
                    ['name' => 'كحلي ليلي', 'hex' => '#1e3a8a'],
                    ['name' => 'خمري داكن', 'hex' => '#881337'],
                ],
                'material' => 'صوف إيطالي نقي 100% Super 150s مع حرير ساتان',
                'care_instructions' => 'تنظيف جاف فقط (Dry Clean Only)، كوي بالبخار بدرجة حرارة منخفضة',
                'fit_type' => 'Slim Fit',
                'gender' => 'men',
                'rating' => 4.9,
                'reviews_count' => 38,
                'badge' => 'الأكثر طلباً',
                'image_url' => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'name_ar' => 'بليزر كلاسيكي صوف مقلم بقصة عصرية',
                'name_en' => 'Modern Cut Striped Wool Blazer',
                'description_ar' => 'بليزر رجالي استثنائي بنمط تقليم خفيف ونسيج خفيف الوزن ومقاوم للتجاعيد، مزود بجيوب قلابة وأزرار قرنية طبيعية، يتناسق بروعة مع البنطال الرسمي أو الجينز الداكن.',
                'price' => 189.50,
                'original_price' => 240.00,
                'stock' => 22,
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'colors' => [
                    ['name' => 'رمادي فحمي', 'hex' => '#374151'],
                    ['name' => 'أزرق كحلي', 'hex' => '#1e40af'],
                    ['name' => 'زيتي مائل للرمادي', 'hex' => '#3f6212'],
                ],
                'material' => 'مزيج صوف ناعم 70% وفيسكوز عالي المرونة 30%',
                'care_instructions' => 'غسيل جاف، لا تستخدم المبيضات، تعلق على شماعة عريضة',
                'fit_type' => 'Modern Fit',
                'gender' => 'men',
                'rating' => 4.8,
                'reviews_count' => 24,
                'badge' => 'تصميم حصري',
                'image_url' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 3,
                'category_id' => 1,
                'name_ar' => 'طقم بدلة رسمية 3 قطع مع سديري فاخر',
                'name_en' => 'Three-Piece Bespoke Business Suit',
                'description_ar' => 'طقم مكتمل من 3 قطع (جاكيت + سديري أنيق + بنطال) يمنحك حضوراً رسمياً استثنائياً في اجتماعات الأعمال الرفيعة، محبوك ببطانة بروكار ناعمة مقاومة للكهرباء الساكنة.',
                'price' => 299.00,
                'original_price' => 390.00,
                'stock' => 9,
                'sizes' => ['48', '50', '52', '54', '56'],
                'colors' => [
                    ['name' => 'كحلي ملكي', 'hex' => '#1e3a8a'],
                    ['name' => 'رصاصي داكن', 'hex' => '#4b5563'],
                    ['name' => 'بيج رملي', 'hex' => '#d4b996'],
                ],
                'material' => 'صوف صيفي إنجليزي فائق الجودة',
                'care_instructions' => 'تنظيف جاف، حفظ داخل كيس واقي من الأتربة',
                'fit_type' => 'Regular Fit',
                'gender' => 'men',
                'rating' => 5.0,
                'reviews_count' => 19,
                'badge' => 'طقم ملكي',
                'image_url' => 'https://images.unsplash.com/photo-1593030761757-71fae45fa0e7?w=700&auto=format&fit=crop&q=80',
            ],

            // Category 2: Traditional Thobes & Luxury Fabrics
            [
                'id' => 4,
                'category_id' => 2,
                'name_ar' => 'ثوب كلاسيكي مطرز من القماش الياباني الفاخر',
                'name_en' => 'Premium Japanese Fabric Tailored Thobe',
                'description_ar' => 'ثوب عربي أصيل مفصل بحرفية عالية من قماش تويوبو الياباني الأصلي البارد، نسيج ناصع البياض ومقاوم للاصفرار والانكماش مع خياطة يدوية دقيقة على الياقة والأكمام.',
                'price' => 119.00,
                'original_price' => 150.00,
                'stock' => 35,
                'sizes' => ['54', '56', '58', '60', '62'],
                'colors' => [
                    ['name' => 'أبيض ناصع', 'hex' => '#ffffff'],
                    ['name' => 'أوف وايت كريمي', 'hex' => '#fef3c7'],
                    ['name' => 'بيج صحراوي', 'hex' => '#e2d9cc'],
                ],
                'material' => 'قماش ياباني قطني 100% بارد ومقاوم للتكسر',
                'care_instructions' => 'غسيل آلي على درجة حرارة معتدلة، كوي على درجة حرارة متوسطة',
                'fit_type' => 'Classic Cut',
                'gender' => 'men',
                'rating' => 4.9,
                'reviews_count' => 45,
                'badge' => 'الأعلى تقييماً',
                'image_url' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 5,
                'category_id' => 2,
                'name_ar' => 'ثوب شتوي فخم من الكشمير والصوف الممشط',
                'name_en' => 'Winter Cashmere & Wool Premium Thobe',
                'description_ar' => 'ثوب شتوي دافئ وخفيف الوزن بملمس مخملي راقي من الصوف الممشط المعالج والكشمير، يمنحك الدفء والأناقة في الأيام الباردة بتفاصيل كبك كلاسيكية وأزرار مخفية.',
                'price' => 165.00,
                'original_price' => 210.00,
                'stock' => 16,
                'sizes' => ['54', '56', '58', '60', '62'],
                'colors' => [
                    ['name' => 'رمادي دخاني', 'hex' => '#4b5563'],
                    ['name' => 'بني شيكولاتة', 'hex' => '#451a03'],
                    ['name' => 'كحلي غامق', 'hex' => '#172554'],
                ],
                'material' => 'مزيج صوف كشمير 60% وبولي فيسكوز معزول 40%',
                'care_instructions' => 'تنظيف جاف أو غسيل يدوي خفيف بماء بارد',
                'fit_type' => 'Tailored Fit',
                'gender' => 'men',
                'rating' => 4.8,
                'reviews_count' => 29,
                'badge' => 'تشكيلة الشتاء',
                'image_url' => 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 6,
                'category_id' => 2,
                'name_ar' => 'شماغ ملكي خاص قطن طبيعي نقي 100%',
                'name_en' => 'Royal Signature Pure Cotton Shemagh',
                'description_ar' => 'شماغ فاخر مصنوع في إنجلترا من أرقى خيوط القطن المصري طويل التيلة، بنقشة كلاسيكية متناسقة الأطراف وثبات فائق للألوان مع كل غسلة وملمس فائق النعومة.',
                'price' => 65.00,
                'original_price' => 85.00,
                'stock' => 40,
                'sizes' => ['55', '58', '60'],
                'colors' => [
                    ['name' => 'أحمر كلاسيكي وأبيض', 'hex' => '#b91c1c'],
                    ['name' => 'أبيض ملكي سادة', 'hex' => '#f8fafc'],
                ],
                'material' => 'قطن مصري ممشط 100% طويل التيلة',
                'care_instructions' => 'غسيل يدوي بماء فاتر دون نقع، عدم استخدام الكلور، كوي بالبخار',
                'fit_type' => 'Standard',
                'gender' => 'men',
                'rating' => 5.0,
                'reviews_count' => 52,
                'badge' => 'جودة إنجليزية',
                'image_url' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=700&auto=format&fit=crop&q=80',
            ],

            // Category 3: Casual & Streetwear
            [
                'id' => 7,
                'category_id' => 3,
                'name_ar' => 'قميص كتان أوروبي نقي بقصة مريحة',
                'name_en' => 'Pure European Linen Relaxed Shirt',
                'description_ar' => 'قميص صيفي أنيق مصنوع من الكتان الفرنسي العضوي بنسبة 100%، نسيج طبيعي خفيف يسمح بمرور الهواء ويمتص الرطوبة، مثالي للمشاوير اليومية والعطلات الصيفية.',
                'price' => 58.00,
                'original_price' => 79.00,
                'stock' => 28,
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'colors' => [
                    ['name' => 'بيج طبيعي', 'hex' => '#d7c4a7'],
                    ['name' => 'أبيض نقي', 'hex' => '#ffffff'],
                    ['name' => 'أزرق سماوي', 'hex' => '#93c5fd'],
                    ['name' => 'أخضر زيتوني', 'hex' => '#65a30d'],
                ],
                'material' => 'كتان فرنسي عضوي نقي 100%',
                'care_instructions' => 'غسيل يدوي أو غسيل غسالة خفيف على 30 درجة، كوي وهو رطب قليلاً',
                'fit_type' => 'Relaxed Fit',
                'gender' => 'unisex',
                'rating' => 4.7,
                'reviews_count' => 34,
                'badge' => 'صيف 2026',
                'image_url' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 8,
                'category_id' => 3,
                'name_ar' => 'هودي أوفرسايز من القطن الثقيل الممتاز',
                'name_en' => 'Heavyweight Streetwear Oversized Hoodie',
                'description_ar' => 'هودي شبابي عصري بقصة واسعة Oversized مصنوع من القطن الثقيل بوزن 450 GSM وبطانة صوفية داخلية ناعمة، مع درزات مدعومة وجيب كنغر أمامي بتصميم مينيمالي راقي.',
                'price' => 69.90,
                'original_price' => 95.00,
                'stock' => 32,
                'sizes' => ['S', 'M', 'L', 'XL'],
                'colors' => [
                    ['name' => 'أسود مطفي', 'hex' => '#18181b'],
                    ['name' => 'رمادي ميلانج', 'hex' => '#71717a'],
                    ['name' => 'موكا دافئ', 'hex' => '#78350f'],
                    ['name' => 'أخضر غابات', 'hex' => '#14532d'],
                ],
                'material' => 'قطن ممشط فاخر 85% مع بوليستر مقوى 15% (450 GSM)',
                'care_instructions' => 'غسيل بالمقلوب مع ألوان مماثلة بماء بارد، تجفيف طبيعي',
                'fit_type' => 'Oversized',
                'gender' => 'youth',
                'rating' => 4.9,
                'reviews_count' => 41,
                'badge' => 'تريند شبابي',
                'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 9,
                'category_id' => 3,
                'name_ar' => 'تيشيرت بولو كلاسيكي قطن بيكيه ناعم',
                'name_en' => 'Classic Cotton Piqué Signature Polo',
                'description_ar' => 'تيشيرت بولو أنيق بنسيج بيكيه مسامي ومريح، ياقة متماسكة لا تنثني مع أزرار صدفية وشعار مطرز بدقة على الصدر، يمنحك مظهر Smart Casual جذاب طوال اليوم.',
                'price' => 42.00,
                'original_price' => 59.00,
                'stock' => 45,
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'colors' => [
                    ['name' => 'كحلي نيفي', 'hex' => '#1e3a8a'],
                    ['name' => 'أبيض ثلجي', 'hex' => '#ffffff'],
                    ['name' => 'عنابي فاخر', 'hex' => '#831843'],
                    ['name' => 'رمادي فاتح', 'hex' => '#9ca3af'],
                ],
                'material' => 'قطن بيكيه مصري 100% عالي الامتصاص',
                'care_instructions' => 'غسيل آلي عادي بماء بارد، تجنب المجفف الحراري لثبات المقاس',
                'fit_type' => 'Slim Fit',
                'gender' => 'men',
                'rating' => 4.8,
                'reviews_count' => 30,
                'badge' => 'أساسي يومي',
                'image_url' => 'https://images.unsplash.com/photo-1625910513413-5a04620021c3?w=700&auto=format&fit=crop&q=80',
            ],

            // Category 4: Jackets & Coats
            [
                'id' => 10,
                'category_id' => 4,
                'name_ar' => 'جاكيت جلد طبيعي فاخر بأسلوب رايدر كلاسيكي',
                'name_en' => 'Handcrafted Genuine Leather Biker Jacket',
                'description_ar' => 'جاكيت رجالي مصنوع يدوياً من جلد الخراف الطبيعي فائق النعومة والمتانة، يتميز بسحابات معدنية متينة من YKK وجيوب متعددة مع بطانة حريرية مريحة تعزز الفخامة والدفء.',
                'price' => 245.00,
                'original_price' => 320.00,
                'stock' => 11,
                'sizes' => ['M', 'L', 'XL', 'XXL'],
                'colors' => [
                    ['name' => 'أسود كربوني', 'hex' => '#09090b'],
                    ['name' => 'بني عتيق (Vintage Brown)', 'hex' => '#5c2c16'],
                ],
                'material' => 'جلد خراف طبيعي 100% ممتاز مع بطانة بوليستر حريري',
                'care_instructions' => 'تنظيف متخصص بالجلد الطبيعي فقط، تجنب التعرض للماء المباشر',
                'fit_type' => 'Modern Fit',
                'gender' => 'men',
                'rating' => 4.9,
                'reviews_count' => 27,
                'badge' => 'جلد طبيعي 100%',
                'image_url' => 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 11,
                'category_id' => 4,
                'name_ar' => 'معطف شتوي طويل من الصوف الإيطالي الكلاسيكي',
                'name_en' => 'Double-Breasted Long Wool Overcoat',
                'description_ar' => 'معطف شتوي طويل أنيق بأزرار مزدوجة وياقة بارزة وقصة مستقيمة مهيبة، يحميك من أقسى الأجواء الباردة مع لمسة أناقة باريسية لا غنى عنها في المناسبات الشتوية والسفر.',
                'price' => 280.00,
                'original_price' => 360.00,
                'stock' => 12,
                'sizes' => ['48', '50', '52', '54', '56'],
                'colors' => [
                    ['name' => 'جملي ملكي (Camel)', 'hex' => '#c28b58'],
                    ['name' => 'أسود كلاسيك', 'hex' => '#18181b'],
                    ['name' => 'رمادي فحمي', 'hex' => '#374151'],
                ],
                'material' => 'صوف إيطالي ثقيل 80% وكشمير 20%',
                'care_instructions' => 'تنظيف جاف فقط، استخدام فرشاة مخصصة للصوف لإزالة الألياف',
                'fit_type' => 'Tailored Long',
                'gender' => 'men',
                'rating' => 5.0,
                'reviews_count' => 18,
                'badge' => 'فخامة ملكية',
                'image_url' => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=700&auto=format&fit=crop&q=80',
            ],

            // Category 5: Shoes & Leather
            [
                'id' => 12,
                'category_id' => 5,
                'name_ar' => 'حذاء أكسفورد رسمي جلد إيطالي ملمع يدوياً',
                'name_en' => 'Hand-Burnished Italian Leather Oxford Shoes',
                'description_ar' => 'حذاء رسمي فاخر بتصميم أكسفورد المقفل مصنوع يدوياً من جلد العجل الإيطالي الفاخر مع تظليل لوني متدرج ونعل جلدي مدعم بنظام خياطة Goodyear Welted لسنوات من المتانة.',
                'price' => 175.00,
                'original_price' => 230.00,
                'stock' => 15,
                'sizes' => ['40', '41', '42', '43', '44', '45'],
                'colors' => [
                    ['name' => 'هافان بني محروق', 'hex' => '#78350f'],
                    ['name' => 'أسود لماع', 'hex' => '#0f172a'],
                    ['name' => 'عنابي برغندي', 'hex' => '#4c0519'],
                ],
                'material' => 'جلد عجل إيطالي كامل الحبيبات (Full-Grain Leather) مع نعل طبيعي',
                'care_instructions' => 'تلميع دوري بشمع العسل المخصص، وضع قالب أحذية خشبي عند عدم الاستخدام',
                'fit_type' => 'Classic Oxford',
                'gender' => 'men',
                'rating' => 4.9,
                'reviews_count' => 31,
                'badge' => 'صناعة يدوية',
                'image_url' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 13,
                'category_id' => 5,
                'name_ar' => 'سنيكرز عصري جلد أبيض ناصع بتصميم مينيمال',
                'name_en' => 'Minimalist White Leather Low-Top Sneakers',
                'description_ar' => 'حذاء رياضي كاجوال أنيق مصنوع من الجلد الطبيعي الناعم مع نعل مطاطي مرن وخفيف الوزن ووسادة قدم ميموري فوم توفر الراحة طوال اليوم، يتناسب مع كل الإطلالات.',
                'price' => 89.00,
                'original_price' => 120.00,
                'stock' => 26,
                'sizes' => ['40', '41', '42', '43', '44', '45'],
                'colors' => [
                    ['name' => 'أبيض كامل', 'hex' => '#ffffff'],
                    ['name' => 'أبيض مع كعب كحلي', 'hex' => '#f1f5f9'],
                    ['name' => 'رمادي خفيف', 'hex' => '#e2e8f0'],
                ],
                'material' => 'جلد طبيعي ناعم مع نعل مطاطي مانع للانزلاق',
                'care_instructions' => 'مسح بمنشفة رطبة ومحلول تنظيف الجلود، عدم الغسيل بالغسالة',
                'fit_type' => 'Comfort Fit',
                'gender' => 'unisex',
                'rating' => 4.8,
                'reviews_count' => 48,
                'badge' => 'الأكثر راحة',
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=700&auto=format&fit=crop&q=80',
            ],

            // Category 6: Accessories
            [
                'id' => 14,
                'category_id' => 6,
                'name_ar' => 'طقم أزرار أكمام ملكي مطلي بالذهب ومطعم بالصدف',
                'name_en' => 'Royal Gold-Plated Mother-of-Pearl Cufflinks Set',
                'description_ar' => 'طقم أزرار أكمام رجالية وكبك فخم مع قلم وحامل مفاتيح متناسق مطلي بذهب عيار 18 قيراط ومرصع بصدف البحر الطبيعي، يقدم داخل علبة هدايا جلدية فاخرة.',
                'price' => 85.00,
                'original_price' => 115.00,
                'stock' => 20,
                'sizes' => ['One Size'],
                'colors' => [
                    ['name' => 'ذهبي مع صدف أبيض', 'hex' => '#eab308'],
                    ['name' => 'فضي بلاتيني مع عقيق أسود', 'hex' => '#94a3b8'],
                ],
                'material' => 'فولاذ مقاوم للصدأ مطلي بذهب عيار 18 وصدف طبيعي',
                'care_instructions' => 'مسح بقطعة قماش ناعمة مخصصة للمجوهرات، حفظ داخل العلبة',
                'fit_type' => 'Luxury Accessory',
                'gender' => 'men',
                'rating' => 5.0,
                'reviews_count' => 22,
                'badge' => 'هدية مثالية',
                'image_url' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=700&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 15,
                'category_id' => 6,
                'name_ar' => 'حزام جلد طبيعي إيطالي ذو وجهين (أسود / بني)',
                'name_en' => 'Reversible Italian Full-Grain Leather Belt',
                'description_ar' => 'حزام رجالي عملي وراقي من الجلد الطبيعي الأصلي بوجهين قابلين للتبديل (أسود رسمي وبني كلاسيكي) مع إبزيم معدني دوار غير قابل للخدش يمنحك قطعتين في قطعة واحدة.',
                'price' => 45.00,
                'original_price' => 65.00,
                'stock' => 30,
                'sizes' => ['32-34', '36-38', '40-42', '44-46'],
                'colors' => [
                    ['name' => 'أسود / بني متبادل', 'hex' => '#1f2937'],
                ],
                'material' => 'جلد بقر إيطالي أصلي 100% مع إبزيم كروم مقاوم للتآكل',
                'care_instructions' => 'تجنب طي الحزام بقوة، تعليق الحزام بشكل مستقيم',
                'fit_type' => 'Adjustable',
                'gender' => 'men',
                'rating' => 4.7,
                'reviews_count' => 33,
                'badge' => 'وجهين (2 في 1)',
                'image_url' => 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?w=700&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }

        // 4. Seed Reviews
        $reviews = [
            ['product_id' => 1, 'author' => 'سعد الهاجري', 'rating' => 5, 'comment' => 'بدلة التوكسيدو مذهلة تماماً! القماش الصوف الإيطالي راقي جداً والقصة تناسبت بدقة متناهية مع مقاسي في حفل زفاف أخي.', 'date' => '2026-10-18'],
            ['product_id' => 1, 'author' => 'عبدالرحمن الشهري', 'rating' => 5, 'comment' => 'خامة ملكية فخمة وسرعة توصيل خيالية، الياقة الساتان تعطي لمعة رقي هادئة غير مبالغ فيها.', 'date' => '2026-10-19'],
            ['product_id' => 4, 'author' => 'نايف الدوسري', 'rating' => 5, 'comment' => 'أفضل ثوب جاهز اشتريته، قماش تويوبو الياباني بارد جداً ولا يتكسر مع الجلوس في السيارة أو المكتب.', 'date' => '2026-10-20'],
            ['product_id' => 7, 'author' => 'محمد السبيعي', 'rating' => 5, 'comment' => 'قميص الكتان خفيف ومريح في الطقس الحار، أخذت اللون البيج وجاري طلب اللون الزيتي والكحلي الآن!', 'date' => '2026-10-21'],
            ['product_id' => 8, 'author' => 'عادل الشمري', 'rating' => 5, 'comment' => 'الهودي وزنه ثقيل حقيقي وبطانته ناعمة ودافية، القصة الأوفرسايز شبابية ومضبوطة بالمللي.', 'date' => '2026-10-17'],
            ['product_id' => 10, 'author' => 'بدر الحربي', 'rating' => 5, 'comment' => 'الجاكيت الجلد خرافة! ريحة الجلد الطبيعي ونعومته واضحة من أول لمسة، سحابات YKK متينة ووزنه متناسق.', 'date' => '2026-10-15'],
            ['product_id' => 12, 'author' => 'د. فهد الغامدي', 'rating' => 5, 'comment' => 'حذاء أوكسفورد أنيق جداً في الاجتماعات الرسمية ومريح للقدم بعد ساعات عمل طويلة.', 'date' => '2026-10-19'],
            ['product_id' => 14, 'author' => 'يوسف العثمان', 'rating' => 5, 'comment' => 'طقم الكبك والقلم فخم ويواجه كهدية، تغليف المتجر يفتح النفس ومستوى عالي من العناية بالتفاصيل.', 'date' => '2026-10-20'],
        ];

        foreach ($reviews as $r) {
            Review::create($r);
        }

        // 5. Seed Coupons
        $coupons = [
            [
                'code' => 'FASHION20',
                'discount_percent' => 20,
                'min_order' => 100.00,
                'max_discount' => 150.00,
                'is_active' => true,
                'uses_count' => 18,
            ],
            [
                'code' => 'ELITE10',
                'discount_percent' => 10,
                'min_order' => 50.00,
                'max_discount' => 50.00,
                'is_active' => true,
                'uses_count' => 42,
            ],
            [
                'code' => 'VIP30',
                'discount_percent' => 30,
                'min_order' => 250.00,
                'max_discount' => 200.00,
                'is_active' => true,
                'uses_count' => 9,
            ],
            [
                'code' => 'EID2026',
                'discount_percent' => 25,
                'min_order' => 150.00,
                'max_discount' => 100.00,
                'is_active' => true,
                'uses_count' => 24,
            ],
        ];

        foreach ($coupons as $cp) {
            Coupon::create($cp);
        }

        // 6. Seed Sample Orders
        $orders = [
            [
                'order_number' => 'ORD-98201',
                'user_id' => 3,
                'customer_name' => 'فيصل بن سلطان',
                'customer_phone' => '0501234567',
                'customer_email' => 'user@example.com',
                'address' => 'حي النخيل، فيلا 12',
                'city' => 'الرياض',
                'payment_method' => 'apple',
                'notes' => 'يرجى الاتصال قبل التوصيل بنصف ساعة',
                'coupon_code' => 'FASHION20',
                'subtotal' => 349.00,
                'discount' => 69.80,
                'shipping' => 0.00,
                'total' => 279.20,
                'status' => 'delivered',
                'items_json' => [
                    [
                        'id' => 1,
                        'name_ar' => 'بدلة توكسيدو ملكية من الصوف الإيطالي',
                        'price' => 349.00,
                        'quantity' => 1,
                        'size' => '50',
                        'color' => 'أسود فاحم',
                        'image_url' => 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=700&auto=format&fit=crop&q=80',
                    ],
                ],
                'created_at' => now()->subDays(3),
            ],
            [
                'order_number' => 'ORD-98202',
                'user_id' => 4,
                'customer_name' => 'تركي الشمري',
                'customer_phone' => '0559876543',
                'customer_email' => 'turki@example.com',
                'address' => 'حي الشاطئ، برج الجوهرة',
                'city' => 'جدة',
                'payment_method' => 'mada',
                'notes' => '',
                'coupon_code' => 'ELITE10',
                'subtotal' => 245.00,
                'discount' => 24.50,
                'shipping' => 0.00,
                'total' => 220.50,
                'status' => 'shipped',
                'items_json' => [
                    [
                        'id' => 10,
                        'name_ar' => 'جاكيت جلد طبيعي فاخر بأسلوب رايدر كلاسيكي',
                        'price' => 245.00,
                        'quantity' => 1,
                        'size' => 'L',
                        'color' => 'أسود كربوني',
                        'image_url' => 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=700&auto=format&fit=crop&q=80',
                    ],
                ],
                'created_at' => now()->subDays(1),
            ],
            [
                'order_number' => 'ORD-98203',
                'user_id' => 5,
                'customer_name' => 'عمر العتيبي',
                'customer_phone' => '0567788990',
                'customer_email' => 'omar@example.com',
                'address' => 'حي الدوحة، الظهران',
                'city' => 'الظهران',
                'payment_method' => 'cod',
                'notes' => 'الدفع نقداً عند الاستلام',
                'coupon_code' => '',
                'subtotal' => 177.00,
                'discount' => 0.00,
                'shipping' => 0.00,
                'total' => 177.00,
                'status' => 'processing',
                'items_json' => [
                    [
                        'id' => 4,
                        'name_ar' => 'ثوب كلاسيكي مطرز من القماش الياباني الفاخر',
                        'price' => 119.00,
                        'quantity' => 1,
                        'size' => '58',
                        'color' => 'أبيض ناصع',
                        'image_url' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=700&auto=format&fit=crop&q=80',
                    ],
                    [
                        'id' => 7,
                        'name_ar' => 'قميص كتان أوروبي نقي بقصة مريحة',
                        'price' => 58.00,
                        'quantity' => 1,
                        'size' => 'L',
                        'color' => 'بيج طبيعي',
                        'image_url' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=700&auto=format&fit=crop&q=80',
                    ],
                ],
                'created_at' => now()->subHours(8),
            ],
            [
                'order_number' => 'ORD-98204',
                'user_id' => 6,
                'customer_name' => 'عبدالعزيز الروقي',
                'customer_phone' => '0543322110',
                'customer_email' => 'aziz@example.com',
                'address' => 'حي المروج، الرياض',
                'city' => 'الرياض',
                'payment_method' => 'apple',
                'notes' => '',
                'coupon_code' => '',
                'subtotal' => 69.90,
                'discount' => 0.00,
                'shipping' => 15.00,
                'total' => 84.90,
                'status' => 'pending',
                'items_json' => [
                    [
                        'id' => 8,
                        'name_ar' => 'هودي أوفرسايز من القطن الثقيل الممتاز',
                        'price' => 69.90,
                        'quantity' => 1,
                        'size' => 'XL',
                        'color' => 'موكا دافئ',
                        'image_url' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=700&auto=format&fit=crop&q=80',
                    ],
                ],
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($orders as $o) {
            Order::create($o);
        }

        // 7. Seed Settings for Elite Fashion Boutique
        $settings = [
            'store_name_ar' => 'بوتيك إليت للأزياء الراقية',
            'store_name_en' => 'Elite Haute Couture & Menswear',
            'currency' => '$',
            'shipping_cost' => '15.00',
            'free_shipping_threshold' => '150.00',
            'tax_percent' => '15',
            'contact_email' => 'concierge@elite-fashion.com',
            'contact_phone' => '+966 50 888 9999',
            'announcement_text' => '✨ إطلاق تشكيلة أزياء الموسم الجديد: كود FASHION20 يمنحك خصماً 20% والشحن المجاني متاح للطلبات فوق $150',
        ];

        foreach ($settings as $k => $v) {
            Setting::create(['key' => $k, 'value' => $v]);
        }
    }
}
