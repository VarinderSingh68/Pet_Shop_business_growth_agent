<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

function seed_page_if_missing(Database $db, string $slug, string $title, string $body): void
{
    $existing = $db->selectOne('SELECT id FROM pages WHERE slug = :slug', ['slug' => $slug]);
    if ($existing !== null) {
        return;
    }
    $db->insert('pages', ['title' => $title, 'slug' => $slug, 'body' => $body, 'is_published' => 1, 'created_at' => now(), 'updated_at' => now()]);
}

seed_page_if_missing($db, 'privacy', 'Privacy Policy', <<<TXT
{{store_name}} collects the information you give us at checkout and account creation — name, email, phone, and delivery address — to fulfil orders and keep you updated on their status.

We use this information to process orders, respond to support requests, and, if you've opted in, send order reminders and offers. We never sell your information to third parties.

You can request a copy of your data or ask us to delete your account at any time by emailing {{store_email}}.

Contact us: {{store_address}} · {{store_phone}} · {{store_email}}
TXT);

seed_page_if_missing($db, 'terms', 'Terms of Service', <<<TXT
By placing an order with {{store_name}}, you agree to pay the listed price plus any applicable shipping and tax, and to provide accurate delivery information.

Prices and availability are subject to change without notice. We reserve the right to cancel any order where a listed price was a genuine error.

Coupons and promotional offers are limited to one use per customer unless stated otherwise, and cannot be combined unless explicitly marked as stackable.

Questions? Reach us at {{store_email}} or {{store_phone}}.
TXT);

seed_page_if_missing($db, 'shipping', 'Shipping Policy', <<<TXT
Orders are typically dispatched within 1-2 business days. Delivery within Bengaluru usually takes 1-3 days; other cities in India typically take 3-7 days.

Shipping is free on orders over the threshold shown at checkout, and a flat rate applies below that. Live tracking is available from your order confirmation page once dispatched.

For questions about a specific order, contact {{store_email}} with your order number.
TXT);

seed_page_if_missing($db, 'returns', 'Returns & Refunds', <<<TXT
Unopened food and accessories can be returned within 7 days of delivery for a full refund. Opened food items can't be returned for hygiene reasons, but if something arrived damaged or incorrect, contact us and we'll make it right.

Refunds are issued to the original payment method within 5-7 business days of the return being received, or credited toward your next cash-on-delivery order.

Start a return by contacting {{store_email}} with your order number and the reason for the return.
TXT);

$faqs = [
    ['q' => 'How do I track my order?', 'a' => "You'll get an order confirmation with a tracking link, or you can go to your account's Orders page anytime.", 'order' => 1],
    ['q' => 'Can I change or cancel my order?', 'a' => "Contact us as soon as possible — we can usually change or cancel an order before it's packed.", 'order' => 2],
    ['q' => 'How do subscriptions work?', 'a' => "Pick a product, a quantity, and how often you'd like it delivered. You can pause, skip, or cancel from your account anytime.", 'order' => 3],
    ['q' => 'Do you offer cash on delivery?', 'a' => 'Yes — cash on delivery is available on every order alongside online payment.', 'order' => 4],
    ['q' => 'How do loyalty points work?', 'a' => 'You earn 1 point per ₹10 spent. Points are worth ₹1 each and can be redeemed at checkout. They expire 12 months after being earned.', 'order' => 5],
];
foreach ($faqs as $f) {
    $exists = $db->selectOne('SELECT id FROM faqs WHERE question = :q', ['q' => $f['q']]);
    if ($exists === null) {
        $db->insert('faqs', ['question' => $f['q'], 'answer' => $f['a'], 'sort_order' => $f['order'], 'is_published' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }
}

$blogCategories = ['Care Tips', 'Product Guides', 'Store News'];
$categoryIds = [];
foreach ($blogCategories as $name) {
    $slug = slugify($name);
    $existing = $db->selectOne('SELECT id FROM blog_categories WHERE slug = :s', ['s' => $slug]);
    $categoryIds[$name] = $existing !== null ? (int) $existing['id'] : $db->insert('blog_categories', ['name' => $name, 'slug' => $slug, 'created_at' => now(), 'updated_at' => now()]);
}

$posts = [
    [
        'title' => 'How Much Should You Really Feed Your Dog?',
        'category' => 'Care Tips',
        'excerpt' => 'Feeding guidelines on the bag are a starting point, not a rule — here\'s how to actually calibrate portions.',
        'body' => "Most feeding charts are calibrated for an average, moderately active dog — which describes almost no dog exactly. Start with the chart's recommendation for your dog's weight, then watch their body condition over 2-3 weeks.\n\nYou should be able to feel (not necessarily see) your dog's ribs with light pressure, and they should have a visible waist when viewed from above. If not, adjust portions by about 10% and reassess in two weeks.\n\nTreats count. A good rule of thumb is that treats shouldn't exceed 10% of daily calorie intake — factor them into the total, not on top of it.",
    ],
    [
        'title' => 'Wet vs Dry Cat Food: What Actually Matters',
        'category' => 'Product Guides',
        'excerpt' => 'The wet-vs-dry debate misses the real question: is your cat drinking enough water?',
        'body' => "Cats evolved as desert animals with a low thirst drive, which means many cats on an all-dry diet run persistently mildly dehydrated — a real risk factor for urinary and kidney issues later in life.\n\nWet food is roughly 70-80% water, which meaningfully boosts total water intake without relying on the cat to drink more from a bowl. That doesn't mean dry food is bad — many cats do fine on it, especially with multiple water sources around the house.\n\nIf you're not sure which way to go, a mixed approach — wet food once or twice a day, dry available for grazing — gets most of the hydration benefit while keeping cost and convenience reasonable.",
    ],
    [
        'title' => "We've Launched Subscriptions",
        'category' => 'Store News',
        'excerpt' => 'Never run out of food again — set it, forget it, and we\'ll remind you before it ships.',
        'body' => "Starting this month, any food product can be set up as a subscription right from the product page. Pick your usual size, choose how often you'd like it delivered, and we'll handle the rest.\n\nYou're always in control — pause before a trip, skip a delivery if you've still got stock, or cancel anytime from your account. No phone calls, no forms.",
    ],
    [
        'title' => 'Puppy to Adult: When to Switch Food',
        'category' => 'Care Tips',
        'excerpt' => "The switch usually happens later than most owners think — here's how to time it.",
        'body' => "Most owners assume the puppy-to-adult switch happens around six months, but for medium and large breeds it's usually closer to 12-18 months — small breeds mature faster and can switch around 9-12 months.\n\nThe signal to watch isn't age alone, it's growth rate. Once weight gain visibly slows and your vet confirms growth plates are closing, that's the real trigger, not a date on a calendar.\n\nMake the switch gradually over 7-10 days, mixing increasing amounts of the new food into the old, to avoid digestive upset from a sudden change.",
    ],
    [
        'title' => 'Setting Up Your First Aquarium the Right Way',
        'category' => 'Product Guides',
        'excerpt' => "The most common first-tank mistake isn't equipment — it's timing.",
        'body' => "The single biggest mistake first-time aquarium owners make is adding fish too soon. A tank needs 2-4 weeks to cycle — building up the beneficial bacteria that process fish waste — before it's safe to add livestock.\n\nRun the filter and heater with just water (and a source of ammonia, even a pinch of fish food) for that window, testing water parameters periodically. Adding fish to an uncycled tank is the leading cause of early fish loss for new owners.\n\nOnce ammonia and nitrite both read zero and nitrate is present, the tank is cycled and ready for its first residents — added gradually, not all at once.",
    ],
    [
        'title' => 'Now Booking: Grooming and Wellness Appointments',
        'category' => 'Store News',
        'excerpt' => "Book, reschedule, or cancel appointments online — no phone calls required.",
        'body' => "You can now book grooming and wellness appointments directly through your account, see real staff availability, and get a confirmation with a reschedule link if plans change.\n\nAppointments more than 24 hours out can be rescheduled or cancelled online anytime; anything closer than that, just give us a call and we'll sort it out.",
    ],
];

foreach ($posts as $p) {
    $slug = slugify($p['title']);
    $exists = $db->selectOne('SELECT id FROM blog_posts WHERE slug = :s', ['s' => $slug]);
    if ($exists === null) {
        $db->insert('blog_posts', [
            'blog_category_id' => $categoryIds[$p['category']],
            'title' => $p['title'],
            'slug' => $slug,
            'excerpt' => $p['excerpt'],
            'body' => $p['body'],
            'status' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

$testimonials = [
    ['name' => 'Priya R.', 'pet' => 'Milo, Labrador', 'quote' => "The reminder texted me two days before Milo's food ran out. I didn't have to think about it.", 'rating' => 5],
    ['name' => 'Arjun K.', 'pet' => 'Simba, Persian cat', 'quote' => 'Booked a grooming slot in under a minute and got a reschedule link when I needed to move it.', 'rating' => 5],
    ['name' => 'Fatima S.', 'pet' => 'Coco, Budgie', 'quote' => 'The subscription pauses itself when I travel. No calls, no forgetting to cancel.', 'rating' => 5],
];
foreach ($testimonials as $i => $t) {
    $exists = $db->selectOne('SELECT id FROM testimonials WHERE customer_name = :n AND quote = :q', ['n' => $t['name'], 'q' => $t['quote']]);
    if ($exists === null) {
        $db->insert('testimonials', [
            'customer_name' => $t['name'], 'pet_description' => $t['pet'], 'quote' => $t['quote'],
            'rating' => $t['rating'], 'is_published' => 1, 'sort_order' => $i,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

echo "  Content: legal pages, FAQs, blog posts, and testimonials seeded.\n";
