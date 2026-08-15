<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

/**
 * The catalogue seeders never attach product_images, so every product falls
 * back to the generic pet-type placeholder everywhere in the storefront
 * (shop grid, PDP gallery, cart, wishlist, order confirmation). This seeder
 * generates a product-specific illustration per product — matching the
 * palette/border language already used across the site — and registers it
 * through the real product_images -> /media pipeline, the same path a real
 * uploaded photo would take.
 */

const PS_INK = '#12141C';
const PS_PAPER = '#F6F7F2';
const PS_FERN = '#1F5F4A';
const PS_LEASH = '#E8492A';
const PS_PLUM = '#7c3aed';
const PS_SKY = '#2f7fb8';

function ps_pet_gradient(string $petType): array
{
    return match ($petType) {
        'dog' => ['#fbcfe8', '#fde68a'],
        'cat' => ['#ddd6fe', '#fbcfe8'],
        'bird' => ['#bae6fd', '#ddd6fe'],
        'fish' => ['#a7f3d0', '#bae6fd'],
        'small_pet' => ['#fde68a', '#a7f3d0'],
        default => ['#e2e8f0', '#ddd6fe'],
    };
}

function ps_category_accent(string $category): string
{
    return match ($category) {
        'Dog Food', 'Cat Food', 'Bird Supplies' => PS_FERN,
        'Dog Accessories', 'Cat Accessories' => PS_LEASH,
        'Grooming' => PS_PLUM,
        'Fish & Aquarium', 'Health & Wellness' => PS_SKY,
        default => PS_PLUM,
    };
}

/** Pick an illustration motif from the product name, falling back to its category. */
function ps_pick_motif(string $name, string $category): string
{
    $n = mb_strtolower($name);

    $rules = [
        'lead' => 'leash', 'leash' => 'leash',
        'harness' => 'harness',
        'bed' => 'bed',
        'carrier' => 'carrier',
        'bowl' => 'bowl',
        'puzzle' => 'toy_ring', 'tug' => 'toy_ring', 'wand' => 'toy_ring', 'toy' => 'toy_ring',
        'raincoat' => 'raincoat',
        'litter box' => 'litter_box', 'covered litter' => 'litter_box',
        'litter' => 'litter_bag',
        'scratch' => 'cat_tree', 'cat tree' => 'cat_tree',
        'cage' => 'cage',
        'bath' => 'bird_bath',
        'perch' => 'perch',
        'aquarium' => 'aquarium', 'tank' => 'aquarium', 'gravel' => 'aquarium', 'led' => 'aquarium',
        'clipper' => 'clipper',
        'brush' => 'brush', 'glove' => 'brush',
        'shampoo' => 'bottle', 'conditioner' => 'bottle', 'spray' => 'bottle', 'fish oil' => 'bottle', 'flea' => 'bottle', 'tick' => 'bottle',
        'wet food' => 'wet_can', 'pate' => 'wet_can', 'multipack' => 'wet_can',
        'powder' => 'tub', 'probiotic' => 'tub', 'paste' => 'tub', 'pellets' => 'tub', 'flakes' => 'tub',
        'chew' => 'treat_pouch', 'treat' => 'treat_pouch', 'stick' => 'treat_pouch', 'seed' => 'treat_pouch', 'millet' => 'treat_pouch', 'cuttlebone' => 'treat_pouch',
        'food' => 'kibble_bag', 'formula' => 'kibble_bag', 'recipe' => 'kibble_bag',
    ];

    foreach ($rules as $needle => $motif) {
        if (str_contains($n, $needle)) {
            return $motif;
        }
    }

    return match ($category) {
        'Dog Food', 'Cat Food' => 'kibble_bag',
        'Dog Accessories', 'Cat Accessories' => 'bowl',
        'Bird Supplies' => 'treat_pouch',
        'Fish & Aquarium' => 'aquarium',
        'Grooming' => 'bottle',
        'Health & Wellness' => 'tub',
        default => 'kibble_bag',
    };
}

function ps_motif_markup(string $motif, string $accent): string
{
    $ink = PS_INK;
    $paper = PS_PAPER;

    return match ($motif) {
        'kibble_bag' => <<<SVG
            <path d="M78,64 L162,64 L172,110 L172,186 Q172,202 156,202 L84,202 Q68,202 68,186 L68,110 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4" stroke-linejoin="round"/>
            <rect x="74" y="50" width="92" height="20" rx="6" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <circle cx="120" cy="150" r="30" fill="{$accent}" opacity="0.18"/>
            <circle cx="106" cy="150" r="7" fill="{$accent}"/>
            <circle cx="120" cy="138" r="7" fill="{$accent}"/>
            <circle cx="134" cy="150" r="7" fill="{$accent}"/>
            <circle cx="120" cy="162" r="7" fill="{$accent}"/>
            SVG,
        'treat_pouch' => <<<SVG
            <path d="M92,80 Q120,58 148,80 L156,100 L158,180 Q158,196 142,196 L98,196 Q82,196 82,180 L84,100 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4" stroke-linejoin="round"/>
            <path d="M92,80 Q120,66 148,80" fill="none" stroke="{$ink}" stroke-width="4" stroke-linecap="round"/>
            <circle cx="106" cy="140" r="9" fill="{$accent}"/>
            <circle cx="132" cy="150" r="9" fill="{$accent}"/>
            <circle cx="118" cy="168" r="9" fill="{$accent}"/>
            SVG,
        'wet_can' => <<<SVG
            <rect x="90" y="80" width="60" height="100" rx="8" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <ellipse cx="120" cy="80" rx="30" ry="10" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <ellipse cx="120" cy="180" rx="30" ry="10" fill="{$accent}" opacity="0.25" stroke="{$ink}" stroke-width="4"/>
            <rect x="112" y="66" width="16" height="10" rx="3" fill="{$accent}"/>
            <rect x="98" y="110" width="44" height="30" rx="4" fill="{$accent}" opacity="0.3"/>
            SVG,
        'leash' => <<<SVG
            <path d="M60,190 C90,150 70,110 110,100 C150,90 130,60 170,55" fill="none" stroke="{$ink}" stroke-width="8" stroke-linecap="round"/>
            <circle cx="60" cy="190" r="14" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <rect x="160" y="46" width="20" height="18" rx="4" fill="{$accent}" stroke="{$ink}" stroke-width="3"/>
            SVG,
        'harness' => <<<SVG
            <ellipse cx="120" cy="132" rx="52" ry="36" fill="none" stroke="{$ink}" stroke-width="4"/>
            <path d="M120,98 L120,132" stroke="{$accent}" stroke-width="11" stroke-linecap="round"/>
            <path d="M120,132 L88,154" stroke="{$accent}" stroke-width="11" stroke-linecap="round"/>
            <path d="M120,132 L152,154" stroke="{$accent}" stroke-width="11" stroke-linecap="round"/>
            <circle cx="120" cy="90" r="11" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            SVG,
        'bed' => <<<SVG
            <ellipse cx="120" cy="140" rx="80" ry="42" fill="{$accent}" opacity="0.25" stroke="{$ink}" stroke-width="4"/>
            <ellipse cx="120" cy="132" rx="52" ry="24" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            SVG,
        'carrier' => <<<SVG
            <rect x="66" y="96" width="108" height="86" rx="14" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <path d="M90,96 Q120,56 150,96" fill="none" stroke="{$ink}" stroke-width="6" stroke-linecap="round"/>
            <line x1="90" y1="120" x2="90" y2="170" stroke="{$accent}" stroke-width="4"/>
            <line x1="106" y1="120" x2="106" y2="170" stroke="{$accent}" stroke-width="4"/>
            <line x1="122" y1="120" x2="122" y2="170" stroke="{$accent}" stroke-width="4"/>
            <line x1="138" y1="120" x2="138" y2="170" stroke="{$accent}" stroke-width="4"/>
            <line x1="154" y1="120" x2="154" y2="170" stroke="{$accent}" stroke-width="4"/>
            SVG,
        'bowl' => <<<SVG
            <ellipse cx="120" cy="102" rx="56" ry="16" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <path d="M64,102 Q64,160 120,160 Q176,160 176,102" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <ellipse cx="120" cy="102" rx="40" ry="10" fill="{$accent}" opacity="0.35"/>
            SVG,
        'toy_ring' => <<<SVG
            <circle cx="104" cy="120" r="34" fill="none" stroke="{$accent}" stroke-width="12"/>
            <circle cx="146" cy="120" r="34" fill="none" stroke="{$ink}" stroke-width="12"/>
            SVG,
        'raincoat' => <<<SVG
            <path d="M120,52 L166,88 L156,190 Q156,200 146,200 L94,200 Q84,200 84,190 L74,88 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4" stroke-linejoin="round"/>
            <path d="M104,52 Q120,36 136,52" fill="none" stroke="{$ink}" stroke-width="4" stroke-linecap="round"/>
            <rect x="94" y="128" width="52" height="14" rx="6" fill="{$accent}"/>
            SVG,
        'litter_bag' => <<<SVG
            <path d="M78,64 L162,64 L172,110 L172,186 Q172,202 156,202 L84,202 Q68,202 68,186 L68,110 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4" stroke-linejoin="round"/>
            <rect x="74" y="50" width="92" height="20" rx="6" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <circle cx="120" cy="150" r="30" fill="{$accent}" opacity="0.18"/>
            <path d="M104,140 L104,165 Q104,172 112,172 L128,172 Q136,172 136,165 L136,150" fill="none" stroke="{$accent}" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="120" y1="130" x2="120" y2="140" stroke="{$accent}" stroke-width="6" stroke-linecap="round"/>
            SVG,
        'litter_box' => <<<SVG
            <path d="M70,120 Q70,84 120,84 Q170,84 170,120 L170,168 Q170,180 158,180 L82,180 Q70,180 70,168 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <ellipse cx="120" cy="120" rx="26" ry="18" fill="{$accent}" opacity="0.3" stroke="{$ink}" stroke-width="4"/>
            <rect x="66" y="176" width="108" height="14" rx="6" fill="{$accent}" opacity="0.5" stroke="{$ink}" stroke-width="3"/>
            SVG,
        'cat_tree' => <<<SVG
            <rect x="112" y="70" width="16" height="120" rx="6" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <rect x="78" y="110" width="70" height="16" rx="6" fill="{$accent}" stroke="{$ink}" stroke-width="3"/>
            <rect x="92" y="150" width="70" height="16" rx="6" fill="{$accent}" stroke="{$ink}" stroke-width="3"/>
            <circle cx="120" cy="60" r="16" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            SVG,
        'cage' => <<<SVG
            <path d="M70,150 Q70,80 120,68 Q170,80 170,150 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <rect x="66" y="150" width="108" height="20" rx="6" fill="{$accent}" opacity="0.4" stroke="{$ink}" stroke-width="4"/>
            <line x1="90" y1="80" x2="90" y2="150" stroke="{$ink}" stroke-width="3"/>
            <line x1="105" y1="72" x2="105" y2="150" stroke="{$ink}" stroke-width="3"/>
            <line x1="120" y1="68" x2="120" y2="150" stroke="{$ink}" stroke-width="3"/>
            <line x1="135" y1="72" x2="135" y2="150" stroke="{$ink}" stroke-width="3"/>
            <line x1="150" y1="80" x2="150" y2="150" stroke="{$ink}" stroke-width="3"/>
            <circle cx="120" cy="55" r="8" fill="{$accent}" stroke="{$ink}" stroke-width="3"/>
            SVG,
        'bird_bath' => <<<SVG
            <ellipse cx="120" cy="150" rx="62" ry="20" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <ellipse cx="120" cy="146" rx="44" ry="12" fill="{$accent}" opacity="0.35"/>
            <rect x="112" y="170" width="16" height="26" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <circle cx="100" cy="110" r="5" fill="{$accent}"/>
            <circle cx="120" cy="98" r="5" fill="{$accent}"/>
            <circle cx="140" cy="112" r="5" fill="{$accent}"/>
            SVG,
        'perch' => <<<SVG
            <rect x="60" y="150" width="130" height="18" rx="9" fill="{$paper}" stroke="{$ink}" stroke-width="4" transform="rotate(-18 120 120)"/>
            <line x1="90" y1="140" x2="98" y2="152" stroke="{$accent}" stroke-width="4" stroke-linecap="round" transform="rotate(-18 120 120)"/>
            <line x1="130" y1="150" x2="138" y2="162" stroke="{$accent}" stroke-width="4" stroke-linecap="round" transform="rotate(-18 120 120)"/>
            SVG,
        'aquarium' => <<<SVG
            <rect x="64" y="76" width="112" height="96" rx="8" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <rect x="68" y="106" width="104" height="62" fill="{$accent}" opacity="0.3"/>
            <circle cx="150" cy="130" r="4" fill="{$accent}"/>
            <circle cx="140" cy="118" r="3" fill="{$accent}"/>
            <path d="M92,140 Q106,130 118,140 Q112,148 104,148 Q96,148 92,140 Z" fill="{$ink}" opacity="0.7"/>
            SVG,
        'bottle' => <<<SVG
            <rect x="100" y="60" width="20" height="16" rx="3" fill="{$paper}" stroke="{$ink}" stroke-width="3"/>
            <path d="M96,76 L124,76 L132,100 L132,186 Q132,196 122,196 L98,196 Q88,196 88,186 L88,100 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4" stroke-linejoin="round"/>
            <rect x="88" y="126" width="44" height="34" fill="{$accent}" opacity="0.35"/>
            SVG,
        'tub' => <<<SVG
            <path d="M76,110 L164,110 L158,186 Q158,196 148,196 L92,196 Q82,196 76,186 Z" fill="{$paper}" stroke="{$ink}" stroke-width="4" stroke-linejoin="round"/>
            <ellipse cx="120" cy="110" rx="44" ry="12" fill="{$accent}" stroke="{$ink}" stroke-width="4"/>
            <rect x="104" y="150" width="32" height="8" rx="4" fill="{$accent}" opacity="0.6"/>
            SVG,
        'brush' => <<<SVG
            <rect x="82" y="68" width="76" height="46" rx="14" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <line x1="96" y1="114" x2="92" y2="130" stroke="{$accent}" stroke-width="4" stroke-linecap="round"/>
            <line x1="112" y1="114" x2="110" y2="132" stroke="{$accent}" stroke-width="4" stroke-linecap="round"/>
            <line x1="128" y1="114" x2="130" y2="132" stroke="{$accent}" stroke-width="4" stroke-linecap="round"/>
            <line x1="144" y1="114" x2="148" y2="130" stroke="{$accent}" stroke-width="4" stroke-linecap="round"/>
            <path d="M108,68 Q120,30 132,68" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            SVG,
        'clipper' => <<<SVG
            <rect x="106" y="120" width="28" height="70" rx="10" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            <path d="M90,80 L120,120 L150,80 Q150,64 132,64 Q120,64 120,78 Q120,64 108,64 Q90,64 90,80 Z" fill="{$accent}" stroke="{$ink}" stroke-width="4" stroke-linejoin="round"/>
            <circle cx="120" cy="80" r="8" fill="{$paper}" stroke="{$ink}" stroke-width="3"/>
            SVG,
        default => <<<SVG
            <circle cx="120" cy="120" r="60" fill="{$paper}" stroke="{$ink}" stroke-width="4"/>
            SVG,
    };
}

function ps_build_svg(string $motif, string $petType, string $accent): string
{
    [$from, $to] = ps_pet_gradient($petType);
    $gid = 'g' . substr(md5($motif . $petType . $accent), 0, 10);
    $inner = ps_motif_markup($motif, $accent);

    return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
          <defs>
            <linearGradient id="{$gid}" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="{$from}"/>
              <stop offset="1" stop-color="{$to}"/>
            </linearGradient>
          </defs>
          <rect width="240" height="240" fill="url(#{$gid})"/>
          {$inner}
        </svg>
        SVG;
}

$dir = storage_path('uploads/products');
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

$products = $db->select(
    'SELECT p.id, p.slug, p.name, p.pet_type, c.name AS category_name
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.deleted_at IS NULL',
);

$created = 0;
foreach ($products as $p) {
    $existing = $db->selectOne('SELECT id FROM product_images WHERE product_id = :pid', ['pid' => $p['id']]);
    if ($existing !== null) {
        continue;
    }

    $category = (string) ($p['category_name'] ?? '');
    $motif = ps_pick_motif((string) $p['name'], $category);
    $accent = ps_category_accent($category);
    $svg = ps_build_svg($motif, (string) $p['pet_type'], $accent);

    $relativePath = 'products/' . $p['slug'] . '.svg';
    file_put_contents(storage_path('uploads/' . $relativePath), $svg);

    $db->insert('product_images', [
        'product_id' => $p['id'],
        'variant_id' => null,
        'path' => $relativePath,
        'alt_text' => $p['name'],
        'sort_order' => 0,
        'created_at' => now(),
    ]);

    $created++;
}

echo "  Product images: {$created} illustrations generated and linked.\n";
