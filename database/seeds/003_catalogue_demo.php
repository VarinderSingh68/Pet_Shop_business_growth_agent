<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

function seed_get_or_create(Database $db, string $table, array $unique, array $data): int
{
    $whereParts = [];
    $bindings = [];
    foreach ($unique as $col => $val) {
        $whereParts[] = "`{$col}` = :{$col}";
        $bindings[$col] = $val;
    }
    $existing = $db->selectOne("SELECT id FROM `{$table}` WHERE " . implode(' AND ', $whereParts), $bindings);
    if ($existing !== null) {
        return (int) $existing['id'];
    }
    return $db->insert($table, array_merge($unique, $data));
}

$categories = [
    ['name' => 'Dog Food', 'icon' => 'dog'],
    ['name' => 'Dog Accessories', 'icon' => 'dog'],
    ['name' => 'Cat Food', 'icon' => 'cat'],
    ['name' => 'Cat Accessories', 'icon' => 'cat'],
    ['name' => 'Bird Supplies', 'icon' => 'bird'],
    ['name' => 'Fish & Aquarium', 'icon' => 'fish'],
    ['name' => 'Grooming', 'icon' => 'other'],
    ['name' => 'Health & Wellness', 'icon' => 'other'],
];

$categoryIds = [];
foreach ($categories as $i => $cat) {
    $slug = slugify($cat['name']);
    $categoryIds[$cat['name']] = seed_get_or_create($db, 'categories', ['slug' => $slug], [
        'name' => $cat['name'],
        'icon' => $cat['icon'],
        'sort_order' => $i,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

$brands = [
    'Bark & Bowl' => 'Everyday food and treats for dogs, made with real meat first.',
    'Whisker Kitchen' => 'Small-batch cat food and litter, formulated with vets.',
    'Nest & Feather' => 'Seed blends, cages, and enrichment toys for pet birds.',
    'AquaLife Co.' => 'Tanks, filters, and water care for freshwater aquariums.',
    'Pawfect Basics' => 'Affordable everyday gear: leads, bowls, beds, carriers.',
    'Vitality Vet' => 'Supplements and wellness products formulated by veterinarians.',
];

$brandIds = [];
foreach ($brands as $name => $desc) {
    $slug = slugify($name);
    $brandIds[$name] = seed_get_or_create($db, 'brands', ['slug' => $slug], [
        'name' => $name,
        'description' => $desc,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

$products = [
    [
        'name' => 'Chicken & Brown Rice Adult Dog Food',
        'category' => 'Dog Food', 'brand' => 'Bark & Bowl', 'pet_type' => 'dog', 'life_stage' => 'adult',
        'short' => 'Real chicken as the first ingredient, gentle on sensitive stomachs.',
        'description' => "A balanced everyday kibble built around real chicken and brown rice. No artificial colours or fillers — just the protein and slow-release carbs an adult dog needs to keep energy steady through the day.\n\nSuits most breeds from 1 to 7 years old. Introduce gradually over 5-7 days when switching from another food.",
        'feeding_grams_per_day' => 220,
        'variants' => [
            ['label' => '1.2kg', 'price' => 49900, 'weight' => 1200, 'stock' => 40],
            ['label' => '3kg', 'price' => 109900, 'weight' => 3000, 'stock' => 25],
            ['label' => '10kg', 'price' => 289900, 'compare' => 329900, 'weight' => 10000, 'stock' => 12],
        ],
    ],
    [
        'name' => 'Puppy Starter Formula, Lamb & Rice',
        'category' => 'Dog Food', 'brand' => 'Bark & Bowl', 'pet_type' => 'dog', 'life_stage' => 'puppy_kitten',
        'short' => 'Smaller kibble size and DHA for growing puppies up to 12 months.',
        'description' => "Formulated for the higher energy and calcium needs of puppies. Smaller kibble is easier for young jaws, and added DHA supports healthy brain and eye development during the first year.",
        'feeding_grams_per_day' => 150,
        'variants' => [
            ['label' => '1kg', 'price' => 54900, 'weight' => 1000, 'stock' => 30],
            ['label' => '2.5kg', 'price' => 119900, 'weight' => 2500, 'stock' => 18],
        ],
    ],
    [
        'name' => 'Grain-Free Salmon Recipe, All Life Stages',
        'category' => 'Dog Food', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'all',
        'short' => 'Single-source salmon protein for dogs with grain sensitivities.',
        'description' => "For dogs that do better without grain. Salmon is the sole animal protein source, with sweet potato and peas for slow-release energy. Suitable for puppies through seniors.",
        'feeding_grams_per_day' => 200,
        'variants' => [
            ['label' => '2kg', 'price' => 149900, 'weight' => 2000, 'stock' => 20],
            ['label' => '6kg', 'price' => 399900, 'weight' => 6000, 'stock' => 8],
        ],
    ],
    [
        'name' => 'Reflective Nylon Lead, 1.5m',
        'category' => 'Dog Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all',
        'short' => 'Reflective stitching for visibility on evening walks.',
        'description' => "A padded-handle lead with reflective thread woven through the full length — visible under headlights and streetlights. Rated for dogs up to 40kg.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => 'Red', 'price' => 39900, 'stock' => 35],
            ['label' => 'Navy', 'price' => 39900, 'stock' => 28],
        ],
    ],
    [
        'name' => 'Orthopedic Memory Foam Dog Bed',
        'category' => 'Dog Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'senior',
        'short' => 'Supportive foam base, easier on joints for older or larger dogs.',
        'description' => "A dense memory foam base holds shape under weight instead of flattening in weeks. The washable cover unzips fully. Popular with owners of senior dogs and larger breeds carrying joint strain.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => 'Medium (70x50cm)', 'price' => 179900, 'stock' => 15],
            ['label' => 'Large (90x60cm)', 'price' => 229900, 'stock' => 10],
        ],
    ],
    [
        'name' => 'Hairball Control Adult Cat Food',
        'category' => 'Cat Food', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'adult',
        'short' => 'Added fibre helps hairballs pass naturally instead of building up.',
        'description' => "Formulated with a specific fibre blend that helps ingested fur move through rather than forming hairballs. Chicken-based kibble that most cats take to immediately.",
        'feeding_grams_per_day' => 60,
        'variants' => [
            ['label' => '1kg', 'price' => 59900, 'weight' => 1000, 'stock' => 32],
            ['label' => '3kg', 'price' => 159900, 'weight' => 3000, 'stock' => 14],
        ],
    ],
    [
        'name' => 'Kitten Growth Formula',
        'category' => 'Cat Food', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'puppy_kitten',
        'short' => 'Higher protein and DHA for kittens up to 12 months.',
        'description' => "Kittens need almost twice the energy density of adult cats. This formula is calorie-dense with added taurine and DHA for eye and brain development during rapid growth.",
        'feeding_grams_per_day' => 45,
        'variants' => [
            ['label' => '1kg', 'price' => 64900, 'weight' => 1000, 'stock' => 22],
        ],
    ],
    [
        'name' => 'Clumping Unscented Cat Litter',
        'category' => 'Cat Accessories', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'all',
        'short' => 'Tight clumps lift clean in one scoop, no added fragrance.',
        'description' => "Bentonite clay litter that clumps tight around waste for one-scoop cleanup. Unscented — some cats reject the perfumed alternatives, this avoids that problem entirely.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => '5kg', 'price' => 69900, 'weight' => 5000, 'stock' => 40],
            ['label' => '10kg', 'price' => 119900, 'compare' => 139900, 'weight' => 10000, 'stock' => 18],
        ],
    ],
    [
        'name' => 'Sisal Rope Cat Scratcher Tower',
        'category' => 'Cat Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'cat', 'life_stage' => 'all',
        'short' => 'Natural sisal rope holds up to daily scratching for years.',
        'description' => "A weighted base keeps this upright even for enthusiastic scratchers. Tightly wound sisal rope resists fraying far longer than cardboard alternatives, with a perch platform on top.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => 'Standard', 'price' => 149900, 'stock' => 12],
        ],
    ],
    [
        'name' => 'Tropical Seed Mix for Budgies & Cockatiels',
        'category' => 'Bird Supplies', 'brand' => 'Nest & Feather', 'pet_type' => 'bird', 'life_stage' => 'all',
        'short' => 'A varied seed blend with millet, sunflower, and dried fruit.',
        'description' => "A varied mix that mirrors what small parrots forage for in the wild — millet sprays, canary seed, sunflower, and small dried fruit pieces for enrichment as much as nutrition.",
        'feeding_grams_per_day' => 20,
        'variants' => [
            ['label' => '500g', 'price' => 29900, 'weight' => 500, 'stock' => 45],
            ['label' => '1.5kg', 'price' => 74900, 'weight' => 1500, 'stock' => 20],
        ],
    ],
    [
        'name' => 'Powder-Coated Bird Cage, Double Door',
        'category' => 'Bird Supplies', 'brand' => 'Nest & Feather', 'pet_type' => 'bird', 'life_stage' => 'all',
        'short' => 'Wide bar spacing for medium birds, slide-out cleaning tray.',
        'description' => "A roomy cage with two front doors, a slide-out tray for daily cleaning, and powder coating that resists the corrosion regular cage cleaning causes over time.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => 'Medium', 'price' => 349900, 'stock' => 6],
        ],
    ],
    [
        'name' => '20L Freshwater Aquarium Starter Kit',
        'category' => 'Fish & Aquarium', 'brand' => 'AquaLife Co.', 'pet_type' => 'fish', 'life_stage' => 'all',
        'short' => 'Tank, filter, and light in one box — just add water and gravel.',
        'description' => "Everything needed to set up a first freshwater tank: a 20-litre glass tank, a quiet internal filter rated for the volume, and an LED light hood. A good size for bettas, guppies, or a small community tank.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => '20L Kit', 'price' => 279900, 'stock' => 9],
        ],
    ],
    [
        'name' => 'Tropical Fish Flakes, Colour Enhancing',
        'category' => 'Fish & Aquarium', 'brand' => 'AquaLife Co.', 'pet_type' => 'fish', 'life_stage' => 'all',
        'short' => 'Spirulina-based flakes that support natural colour vibrancy.',
        'description' => "A daily flake food with spirulina and carotenoids that support the natural colour vibrancy of tropical fish, without overfeeding fillers that cloud water quality.",
        'feeding_grams_per_day' => 5,
        'variants' => [
            ['label' => '100g', 'price' => 24900, 'weight' => 100, 'stock' => 50],
        ],
    ],
    [
        'name' => 'Oatmeal Sensitive Skin Shampoo',
        'category' => 'Grooming', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'all',
        'short' => 'Soap-free formula for dogs prone to itching or dry skin.',
        'description' => "A soap-free, pH-balanced shampoo with colloidal oatmeal that soothes irritated skin instead of stripping natural oils the way everyday shampoos can.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => '250ml', 'price' => 44900, 'stock' => 26],
        ],
    ],
    [
        'name' => 'Joint Care Chews, Glucosamine & Chondroitin',
        'category' => 'Health & Wellness', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'senior',
        'short' => 'Daily chewable support for hips and joints in older dogs.',
        'description' => "A daily soft chew with glucosamine, chondroitin, and omega-3s — the combination most commonly recommended for supporting joint comfort in dogs over seven.",
        'feeding_grams_per_day' => null,
        'variants' => [
            ['label' => '60 chews', 'price' => 89900, 'stock' => 20],
        ],
    ],
];

$productCount = 0;
foreach ($products as $p) {
    $slug = slugify($p['name']);
    $existing = $db->selectOne('SELECT id FROM products WHERE slug = :slug', ['slug' => $slug]);

    if ($existing !== null) {
        continue;
    }

    $productId = $db->insert('products', [
        'category_id' => $categoryIds[$p['category']],
        'brand_id' => $brandIds[$p['brand']],
        'name' => $p['name'],
        'slug' => $slug,
        'short_description' => $p['short'],
        'description' => $p['description'],
        'pet_type' => $p['pet_type'],
        'life_stage' => $p['life_stage'],
        'status' => 'active',
        'feeding_grams_per_day' => $p['feeding_grams_per_day'],
        'is_featured' => random_int(0, 4) === 0 ? 1 : 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ($p['variants'] as $i => $v) {
        $db->insert('product_variants', [
            'product_id' => $productId,
            'sku' => strtoupper(substr($slug, 0, 6)) . '-' . ($i + 1) . '-' . $productId,
            'label' => $v['label'],
            'price_paise' => $v['price'],
            'compare_at_price_paise' => $v['compare'] ?? null,
            'stock_quantity' => $v['stock'],
            'weight_grams' => $v['weight'] ?? null,
            'is_default' => $i === 0 ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $productCount++;
}

echo "  Catalogue demo: {$productCount} products created across " . count($categories) . " categories and " . count($brands) . " brands.\n";
