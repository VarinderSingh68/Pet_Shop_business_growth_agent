<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

$categoryIds = [];
foreach ($db->select('SELECT id, name FROM categories') as $c) {
    $categoryIds[$c['name']] = (int) $c['id'];
}
$brandIds = [];
foreach ($db->select('SELECT id, name FROM brands') as $b) {
    $brandIds[$b['name']] = (int) $b['id'];
}

$products = [
    // Dog Food
    ['name' => 'Senior Dog Formula, Joint Support', 'category' => 'Dog Food', 'brand' => 'Bark & Bowl', 'pet_type' => 'dog', 'life_stage' => 'senior', 'short' => 'Lower calorie, added glucosamine for dogs over 7.', 'description' => "Senior dogs burn fewer calories but still need full nutrition — this formula trims calorie density while adding glucosamine and chondroitin for joint support, plus easier-to-digest protein.", 'feeding_grams_per_day' => 180, 'variants' => [['label' => '3kg', 'price' => 129900, 'weight' => 3000, 'stock' => 22], ['label' => '10kg', 'price' => 349900, 'weight' => 10000, 'stock' => 10]]],
    ['name' => 'Grain-Inclusive Chicken & Vegetable Adult Formula', 'category' => 'Dog Food', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'adult', 'short' => 'An everyday budget-friendly kibble with real chicken.', 'description' => "A straightforward, affordable everyday food — chicken meal, rice, and vegetables in a balanced ratio, without premium pricing for ingredients most dogs don't need.", 'feeding_grams_per_day' => 210, 'variants' => [['label' => '3kg', 'price' => 74900, 'weight' => 3000, 'stock' => 30], ['label' => '15kg', 'price' => 329900, 'weight' => 15000, 'stock' => 8]]],
    ['name' => 'Small Breed Adult Formula', 'category' => 'Dog Food', 'brand' => 'Bark & Bowl', 'pet_type' => 'dog', 'life_stage' => 'adult', 'short' => 'Smaller kibble sized for toy and small breed jaws.', 'description' => "Small breed dogs have faster metabolisms and smaller mouths — this formula is calorie-dense with a smaller kibble shape built for breeds under 10kg.", 'feeding_grams_per_day' => 90, 'variants' => [['label' => '1.5kg', 'price' => 64900, 'weight' => 1500, 'stock' => 25]]],
    ['name' => 'Large Breed Puppy Formula', 'category' => 'Dog Food', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'puppy_kitten', 'short' => 'Controlled calcium for healthy large-breed joint growth.', 'description' => "Large breed puppies grow fast, and too much calcium too quickly raises the risk of joint problems later. This formula controls calcium levels precisely for breeds expected to exceed 25kg as adults.", 'feeding_grams_per_day' => 240, 'variants' => [['label' => '3kg', 'price' => 139900, 'weight' => 3000, 'stock' => 18]]],
    ['name' => 'Limited Ingredient Duck & Potato Recipe', 'category' => 'Dog Food', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'A single novel protein for dogs with food sensitivities.', 'description' => "For dogs who react to common proteins like chicken or beef, this formula uses duck as the sole animal protein with potato as the only carbohydrate — fewer ingredients to react to.", 'feeding_grams_per_day' => 190, 'variants' => [['label' => '2kg', 'price' => 159900, 'weight' => 2000, 'stock' => 14]]],
    ['name' => 'Freeze-Dried Liver Training Treats', 'category' => 'Dog Food', 'brand' => 'Bark & Bowl', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Single-ingredient, high-value treats for training sessions.', 'description' => "Just liver, freeze-dried to lock in flavour dogs go wild for — small enough to reward frequently during training without overfeeding.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '80g', 'price' => 34900, 'stock' => 40]]],
    ['name' => 'Dental Care Chew Sticks', 'category' => 'Dog Food', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'adult', 'short' => 'Textured chews that help reduce tartar build-up.', 'description' => "The ridged texture scrapes plaque as your dog chews — a tasty daily habit that supports dental health between brushings.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '7 pack', 'price' => 29900, 'stock' => 35]]],
    // Dog Accessories
    ['name' => 'Adjustable Padded Harness', 'category' => 'Dog Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'No-pull design with a padded chest plate.', 'description' => "A front-clip harness that discourages pulling without choking — the padded chest plate spreads pressure evenly and adjusts across four points for a secure fit.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Small', 'price' => 79900, 'stock' => 20], ['label' => 'Medium', 'price' => 89900, 'stock' => 24], ['label' => 'Large', 'price' => 99900, 'stock' => 16]]],
    ['name' => 'Stainless Steel Non-Slip Bowl Set', 'category' => 'Dog Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Two bowls with a rubber base that stays put.', 'description' => "Stainless steel resists odour and bacteria far better than plastic, and the rubber base keeps enthusiastic eaters from pushing the bowl across the floor.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Set of 2', 'price' => 59900, 'stock' => 30]]],
    ['name' => 'Travel Carrier, Airline Approved', 'category' => 'Dog Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Ventilated soft-sided carrier for small dogs.', 'description' => "Meets most airline cabin-carry dimensions, with mesh ventilation on three sides and a fleece-lined base for comfort on the move.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 189900, 'stock' => 8]]],
    ['name' => 'Interactive Puzzle Feeder', 'category' => 'Dog Accessories', 'brand' => 'Bark & Bowl', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Slows fast eaters and adds mental stimulation.', 'description' => "Hides kibble in a maze pattern your dog has to work to access — slows down fast eaters (reducing bloat risk) and gives bored dogs something constructive to do at mealtime.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 69900, 'stock' => 22]]],
    ['name' => 'Rope Tug Toy, Triple Braided', 'category' => 'Dog Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Durable cotton rope for tug and fetch.', 'description' => "Triple-braided cotton rope holds up to determined chewers far longer than single-strand versions, and doubles as a light flossing action for teeth.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 24900, 'stock' => 45]]],
    ['name' => 'Waterproof Dog Raincoat', 'category' => 'Dog Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Keeps your dog dry on monsoon walks.', 'description' => "A fully waterproof shell with a belly strap and leash hole, cut generously to fit over a harness for monsoon-season walks.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Medium', 'price' => 89900, 'stock' => 15], ['label' => 'Large', 'price' => 99900, 'stock' => 12]]],
    // Cat Food
    ['name' => 'Indoor Cat Weight Control Formula', 'category' => 'Cat Food', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'adult', 'short' => 'Lower calorie for less-active indoor cats.', 'description' => "Indoor cats burn noticeably fewer calories than outdoor cats — this formula reduces calorie density while keeping protein high, so your cat stays satisfied without gaining weight.", 'feeding_grams_per_day' => 55, 'variants' => [['label' => '1.5kg', 'price' => 69900, 'weight' => 1500, 'stock' => 24]]],
    ['name' => 'Senior Cat Formula, Kidney Support', 'category' => 'Cat Food', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'senior', 'short' => 'Reduced phosphorus for cats over 8 years old.', 'description' => "Kidney function often declines gradually in older cats — this formula moderates phosphorus levels, a change commonly recommended for senior cats as a preventive measure.", 'feeding_grams_per_day' => 50, 'variants' => [['label' => '1.5kg', 'price' => 79900, 'weight' => 1500, 'stock' => 16]]],
    ['name' => 'Wet Food Multipack, Tuna & Salmon', 'category' => 'Cat Food', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'all', 'short' => 'Boosts water intake — cats have a naturally low thirst drive.', 'description' => "Wet food is roughly 75% water, which meaningfully supports hydration for cats that don't drink much from a bowl. This pack mixes tuna and salmon recipes.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '12 x 85g', 'price' => 89900, 'stock' => 28]]],
    ['name' => 'Kitten Wet Food, Chicken Pate', 'category' => 'Cat Food', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'puppy_kitten', 'short' => 'Soft pate texture, easy for kittens to eat.', 'description' => "A smooth pate texture that's easy for kittens transitioning off milk, with the higher calorie density kittens need for rapid early growth.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '12 x 85g', 'price' => 94900, 'stock' => 20]]],
    // Cat Accessories
    ['name' => 'Silica Gel Crystal Litter', 'category' => 'Cat Accessories', 'brand' => 'Whisker Kitchen', 'pet_type' => 'cat', 'life_stage' => 'all', 'short' => 'Superior odour control, lasts up to 30 days.', 'description' => "Silica crystals absorb moisture and lock in odour far longer than clay litter — a single bag typically lasts a single cat close to a month.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '5L', 'price' => 89900, 'stock' => 22]]],
    ['name' => 'Covered Litter Box with Filter', 'category' => 'Cat Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'cat', 'life_stage' => 'all', 'short' => 'Enclosed design with a carbon odour filter.', 'description' => "The enclosed hood gives shy cats privacy and contains litter scatter, while a replaceable carbon filter in the lid cuts down on odour escaping the room.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 129900, 'stock' => 12]]],
    ['name' => 'Cat Tree with Scratching Posts', 'category' => 'Cat Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'cat', 'life_stage' => 'all', 'short' => 'Multi-level tower with sisal posts and a perch.', 'description' => "A weighted base supports multiple levels of platforms and hammocks, with sisal-wrapped posts throughout to redirect scratching away from furniture.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 349900, 'stock' => 6]]],
    ['name' => 'Feather Wand Interactive Toy', 'category' => 'Cat Accessories', 'brand' => 'Pawfect Basics', 'pet_type' => 'cat', 'life_stage' => 'all', 'short' => 'Encourages natural stalk-and-pounce play.', 'description' => "A long wand with feathers on an elastic cord lets you mimic prey movement — good daily exercise for indoor cats who need an outlet for hunting instincts.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 19900, 'stock' => 50]]],
    // Bird Supplies
    ['name' => 'Cuttlebone with Holder', 'category' => 'Bird Supplies', 'brand' => 'Nest & Feather', 'pet_type' => 'bird', 'life_stage' => 'all', 'short' => 'Natural calcium source birds beak-condition on.', 'description' => "A natural, mineral-rich calcium source that also helps keep a bird's beak trimmed and conditioned through regular gnawing.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '2 pack', 'price' => 14900, 'stock' => 40]]],
    ['name' => 'Bird Bath Attachment', 'category' => 'Bird Supplies', 'brand' => 'Nest & Feather', 'pet_type' => 'bird', 'life_stage' => 'all', 'short' => 'Clips to cage door for easy bathing.', 'description' => "Clips directly onto the cage door opening, giving your bird a shallow bathing spot without you needing to open the whole cage.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 24900, 'stock' => 25]]],
    ['name' => 'Natural Wood Perch Set', 'category' => 'Bird Supplies', 'brand' => 'Nest & Feather', 'pet_type' => 'bird', 'life_stage' => 'all', 'short' => 'Varied-diameter perches support healthy feet.', 'description' => "Uniform plastic perches can lead to foot problems over time — this set includes three varied-diameter natural wood branches that exercise a bird's feet naturally.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Set of 3', 'price' => 34900, 'stock' => 18]]],
    ['name' => 'Millet Spray Treats', 'category' => 'Bird Supplies', 'brand' => 'Nest & Feather', 'pet_type' => 'bird', 'life_stage' => 'all', 'short' => 'A favourite treat and foraging enrichment.', 'description' => "Millet sprays double as a treat and as foraging enrichment — most small parrots will happily spend a long time working seeds off the stem.", 'feeding_grams_per_day' => 10, 'variants' => [['label' => '150g', 'price' => 19900, 'weight' => 150, 'stock' => 35]]],
    // Fish & Aquarium
    ['name' => 'Water Conditioner, Chlorine Remover', 'category' => 'Fish & Aquarium', 'brand' => 'AquaLife Co.', 'pet_type' => 'fish', 'life_stage' => 'all', 'short' => 'Makes tap water safe for fish instantly.', 'description' => "Neutralizes chlorine and chloramine in tap water instantly, and detoxifies heavy metals — essential for every water change, not just initial setup.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '250ml', 'price' => 34900, 'stock' => 30]]],
    ['name' => 'Aquarium Gravel, Natural', 'category' => 'Fish & Aquarium', 'brand' => 'AquaLife Co.', 'pet_type' => 'fish', 'life_stage' => 'all', 'short' => 'Substrate for planted or community tanks.', 'description' => "Rounded natural gravel that won't damage delicate fins on bottom-dwelling species, with enough surface area to support beneficial bacteria colonies.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '2kg', 'price' => 39900, 'weight' => 2000, 'stock' => 20]]],
    ['name' => 'Submersible LED Aquarium Light', 'category' => 'Fish & Aquarium', 'brand' => 'AquaLife Co.', 'pet_type' => 'fish', 'life_stage' => 'all', 'short' => 'Full-spectrum light for planted tanks.', 'description' => "A full-spectrum LED strip that supports live plant growth while showing off fish colour far better than a bare tank under room lighting.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 99900, 'stock' => 14]]],
    ['name' => 'Betta Fish Pellets', 'category' => 'Fish & Aquarium', 'brand' => 'AquaLife Co.', 'pet_type' => 'fish', 'life_stage' => 'all', 'short' => 'Sized and formulated specifically for bettas.', 'description' => "Betta-specific pellets sized for their small mouths, with a protein ratio matched to their carnivorous diet in the wild — many generic flakes don't fit either need well.", 'feeding_grams_per_day' => 3, 'variants' => [['label' => '50g', 'price' => 19900, 'weight' => 50, 'stock' => 40]]],
    // Grooming
    ['name' => 'Deshedding Undercoat Brush', 'category' => 'Grooming', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Reaches the undercoat without cutting the topcoat.', 'description' => "Reaches through the topcoat to gently remove loose undercoat hair — the difference-maker during seasonal shedding for double-coated breeds.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 59900, 'stock' => 20]]],
    ['name' => 'Cat Grooming Glove', 'category' => 'Grooming', 'brand' => 'Pawfect Basics', 'pet_type' => 'cat', 'life_stage' => 'all', 'short' => 'Brushing that feels like petting to nervous cats.', 'description' => "Silicone tips on a glove let you brush while petting — far less stressful for cats that fight a traditional brush.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 34900, 'stock' => 30]]],
    ['name' => 'Nail Clipper with Safety Guard', 'category' => 'Grooming', 'brand' => 'Pawfect Basics', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Guard prevents cutting the quick too short.', 'description' => "A built-in safety guard limits how much nail is removed per clip, reducing the risk of cutting into the quick — good for first-time nail trimmers.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Standard', 'price' => 44900, 'stock' => 25]]],
    ['name' => 'Waterless Dry Shampoo Spray', 'category' => 'Grooming', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Freshens between baths, no rinsing needed.', 'description' => "A quick freshen-up between full baths — spray, work through the coat, and towel off. No water or rinsing required.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '200ml', 'price' => 39900, 'stock' => 28]]],
    // Health & Wellness
    ['name' => 'Omega-3 Fish Oil Supplement', 'category' => 'Health & Wellness', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Supports coat shine and skin health.', 'description' => "A daily fish oil supplement providing EPA and DHA, commonly used to support coat shine, skin health, and joint comfort in dogs of any age.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '250ml', 'price' => 69900, 'stock' => 22]]],
    ['name' => 'Probiotic Digestive Support Powder', 'category' => 'Health & Wellness', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Supports gut health, useful after antibiotics.', 'description' => "A vet-formulated probiotic blend often recommended after a course of antibiotics or during diet transitions to support healthy digestion.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '60g', 'price' => 79900, 'stock' => 18]]],
    ['name' => 'Flea & Tick Spot Treatment', 'category' => 'Health & Wellness', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'adult', 'short' => 'Monthly application, waterproof after 24 hours.', 'description' => "A monthly topical treatment that protects against fleas and ticks, safe once fully dry, and waterproof through regular bathing after the first 24 hours.", 'feeding_grams_per_day' => null, 'variants' => [['label' => 'Single dose', 'price' => 59900, 'stock' => 30], ['label' => '3-pack', 'price' => 159900, 'stock' => 15]]],
    ['name' => 'Calming Chews for Anxious Pets', 'category' => 'Health & Wellness', 'brand' => 'Vitality Vet', 'pet_type' => 'dog', 'life_stage' => 'all', 'short' => 'Chamomile and L-theanine for stressful moments.', 'description' => "A chamomile and L-theanine blend in a tasty chew, commonly used around fireworks, vet visits, or travel — non-drowsy for everyday stressors.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '30 chews', 'price' => 84900, 'stock' => 20]]],
    ['name' => 'Cat Hairball Control Supplement', 'category' => 'Health & Wellness', 'brand' => 'Vitality Vet', 'pet_type' => 'cat', 'life_stage' => 'adult', 'short' => 'A daily paste that helps hairballs pass naturally.', 'description' => "A malt-based paste most cats take readily off a fingertip, formulated to help ingested fur move through the digestive tract instead of forming hairballs.", 'feeding_grams_per_day' => null, 'variants' => [['label' => '100g', 'price' => 49900, 'stock' => 24]]],
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
        'is_featured' => random_int(0, 6) === 0 ? 1 : 0,
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

echo "  Catalogue expansion: {$productCount} more products created.\n";
