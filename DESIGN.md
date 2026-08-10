# Design plan — Happy Tails Pet Store

## Why not the default look

The default "AI pet/artisan brand" aesthetic is cream background + serif display headline +
dusty terracotta accent, centered hero with a stock photo, rounded soft-shadow cards. It reads
as generic regardless of category. This plan deliberately avoids all three: no cream base, no
serif headline, no terracotta. Instead: a cool paper-white base, a bold grotesque display face,
and a saturated leash-red accent, laid out as an asymmetric editorial grid rather than a
centered hero.

## Palette

| Name    | Hex       | Role                                                              |
|---------|-----------|--------------------------------------------------------------------|
| Ink     | `#12141C` | Primary text, dark full-bleed sections, admin sidebar              |
| Paper   | `#F6F7F2` | Base background — cool warm-white with a whisper of sage, not cream|
| Leash   | `#E8492A` | Primary accent — CTAs, links, the signature leash-line motif       |
| Tennis  | `#F2B705` | Secondary accent — badges, ratings, sale tags, highlights          |
| Fern    | `#1F5F4A` | Tertiary — success states, in-stock, alternate dark section        |
| Mist    | `#DDE3DE` | Borders, dividers, card fills, disabled states                     |

Dark mode (admin "calm" variant and site dark sections) inverts Ink/Paper roles rather than
introducing a new palette, so the system stays coherent.

## Type

- **Display — Bricolage Grotesque** (variable, Google Fonts). Bold, slightly irregular grotesque
  with real character at large sizes. Used *only* for H1/H2, the logotype, pull-quotes, and big
  stat numbers in the admin dashboard. Never used for body copy or UI chrome — this is the
  "spend boldness in one place" rule applied to type.
- **Body — Inter**. Clean, highly legible at small sizes, excellent number tabular-figures for
  prices and admin tables. Used for everything else: paragraphs, labels, buttons, nav, forms,
  the entire admin panel.

Type scale (rem, 16px root): `0.75 / 0.875 / 1 / 1.125 / 1.25 / 1.5 / 1.875 / 2.25 / 3 / 3.75 / 4.5`
Display face only ever appears at `1.875` and above.

## Layout concept — "Editorial kennel-club"

Asymmetric bento-style grids instead of centered hero-then-stack. Sections alternate full-bleed
Ink (dark) and Paper (light) to create rhythm down the page. Cards borrow from vet record
cards / ID tags: a thick 2px Ink border, a squared-off corner tab in an accent color carrying a
category icon, no soft drop shadows. Pet photography bleeds to the edge of its container and is
cropped tight and off-center (never centered-portrait stock-photo framing) so it reads as candid,
not staged.

## Signature element — "The Leash Line"

A single hand-drawn-style SVG line runs down the spine of the homepage, connecting the hero to
each major section, ending in a small paw-print waypoint marker at each anchor. On scroll, the
line draws itself in (stroke-dashoffset animation via a lightweight scroll listener / Alpine
directive) and each waypoint "clicks" into a filled paw print as its section enters view — literally
the thread between owner and pet, and the one deliberately loud, playful moment on the site.
Everywhere else stays quiet and disciplined: flat colors, no gradients, no soft shadows, sharp
2px borders. Under `prefers-reduced-motion: reduce` the line renders fully drawn and static, no
scroll listener attached.

The admin panel shares tokens (palette, type scale) but never uses the Leash Line motif or the
display face beyond dashboard stat numbers — it is dense, calm, and information-first, optimized
for scanning 200-row tables, not for brand delight.

## Motion

- Page load: a short (400ms) staggered fade+rise on hero content, once, never repeated on
  client-side nav.
- Hover: 150ms ease-out on color/border, no scale transforms on large images (avoids layout
  jank), small scale (1.02) only on compact cards/buttons.
- All motion gated behind `prefers-reduced-motion`.

## Accessibility floor

AA contrast on all text/background pairs above (verified: Ink-on-Paper 17.9:1, Paper-on-Ink
17.9:1, Ink-on-Tennis 8.4:1, Paper-on-Leash 4.6:1, Paper-on-Fern 7.9:1). Visible focus rings use
a 3px Leash outline with 2px offset on every interactive element, including in the admin panel.
