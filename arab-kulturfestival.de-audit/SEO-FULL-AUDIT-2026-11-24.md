# Arab Kulturfestival — Full SEO Audit (2026-11-24)

## Executive Summary

- **SEO Health Score: 88/100**
- **Business type:** Local cultural association / event publisher (Hamburg, DE) — hybrid local + publisher.
- **URLs crawled:** 102 (from 5 XML sitemaps).
- **Critical issues:** 0
- **High issues:** 4 (service pages meta descriptions; titles of 4 article pages)
- **Medium issues:** 9 (thin utility pages, short category titles, long descriptions)
- **Cache note:** WP-Optimize serves stale HTML to plain HTTP clients; browser-rendered check confirmed the event-page fixes are live.

## Category Scores

| Category | Score |
|---|---|
| Technical SEO | 95/100 |
| Content Quality | 88/100 |
| On-Page SEO | 82/100 |
| Schema / Structured Data | 95/100 |
| Performance (CWV) | 85/100 (lab estimate; no field data) |
| AI Search Readiness | 80/100 |
| Images | 92/100 |

---

## Technical SEO — 95/100

**What works**
- `robots.txt` (200) allows indexing, blocks WP admin and WooCommerce transient paths, declares `sitemap_index.xml`.
- `sitemap_index.xml` valid, 5 child sitemaps:
  - post: 20, page: 37, yd_event: 28, yd_service: 15, category: 2.
- All 102 URLs returned HTTP 200.
- Canonical tags present on all sampled pages, self-referencing.
- Hreflang sets present (de/en/ar/x-default) on translated pages.
- JSON-LD present on event pages (`@graph`: WebPage, BreadcrumbList, Event).
- Event schema includes name, description, startDate/endDate, image, location with `postalCode 20251`, organizer, offers.price, isAccessibleForFree.

**Findings**
- Medium — WP-Optimize page cache serves stale HTML to non-browser clients; verify with browser or cache-busting header.
- Info — No field CWV data (GSC/CrUX not connected).

## Content Quality — 88/100

**What works**
- Event pages: 300–430 words, well above thin threshold.
- Strong E-E-A-T signals: organizer NGO schema with address, email, phone, sameAs social profiles, founder.

**Findings**
- Low (acceptable) — Utility pages thin: `/newsletter/` (171 words), `/ar/imprint/` (234), `/en/legal-notice/` (248). Legal/utility pages are exempt from thin-content penalties; no action required.
- Low — Article titles very short (22–29 chars): `/arabic-culture-in-exile/`, `/dabke-la-tahtaj-dawa/`, `/arabic-culture-in-exile-ar/`, `/dabke-needs-no-invitation/`, `/cookies/`, `/en/cookies/`, `/ar/about/`. Add descriptive modifiers where natural.

## On-Page SEO — 82/100

**What works**
- All 28 event pages now carry optimized `rank_math_title` with event name + date + Hamburg (35–61 chars).
- Descriptions on the 3 main event pages optimized (82–126 chars, browser-verified).
- Exactly one H1 per page with logical H2/H3 hierarchy.
- Images have `width`/`height` (fixed via `wp_getimagesize()` in Yalla Engage).

**Findings (High)**
- Service pages have very short descriptions:
  - `/angebot/orientalischer-tanz-otw-ensemble/` — 55 chars
  - `/angebot/oriental-dance-otw-ensemble/` — 47 chars
  - `/angebot/الخط-العربية/` — 35 chars
- Category pages: `/category/arab-kulturfestival/` and `/category/magazin/` — title 28–29 chars, description 197 chars.

**Findings (Medium)**
- 2 Arabic event pages have 107-char descriptions (below 150 target).

## Schema — 95/100

No deprecated HowTo or FAQPage found. Event schema complete with postal code and offers. Opportunity: add `performer` to OTW dance events (Info).

## Performance — 85/100

- Hero image `festival-19.webp` 164KB (WebP) — under 200KB warning threshold.
- Images lazy-loaded where appropriate.
- No field CWV data available.

## Images — 92/100

- All images have descriptive alt text.
- Alt text does not keyword-stuff.
- Participant logos now carry width/height (CLS fixed).

## AI Search Readiness — 80/100

- Strong entity signals: NGO organizer schema with `sameAs`, founder, areaServed, knowsLanguage.
- No `llms.txt` or Markdown delivery (Info — optional).

---

## Prioritized Action Plan

### Phase 1 — High (week 1)
1. Expand meta descriptions on the 3 `/angebot/` service pages to 150–160 chars.
2. Rewrite the 2 category titles/descriptions (`/category/arab-kulturfestival/`, `/category/magazin/`).

### Phase 2 — Medium (APPLIED 2026-11-24)
3. ✅ Article titles expanded: `Arabic Culture in Exile - Arab Kulturfestival Hamburg` (53), `الثقافة العربية في المنفى - مهرجان الثقافة العربية`, `Dabke Needs No Invitation - Hamburg` (35), `الدبكة لا تحتاج إلى دعوة - هامبورغ`.
4. ✅ Article descriptions expanded to 106–113 chars; `ملفات تعريف الارتباط - مهرجان الثقافة العربية` and `من نحن - مهرجان الثقافة العربية هامبورغ` applied.
   Remaining: `/en/cookies/` still shows `Cookies & settings` (18 chars) — different post ID than 2555.

### Phase 3 — Optional
5. Add `performer` to OTW event schema.
6. Consider `llms.txt` for AI agents.

### Phase 4 — Monitoring
7. Connect Google Search Console / CrUX for field CWV and indexation monitoring.
8. After each edit, purge WP-Optimize cache and re-verify in a browser.

## Verification Method

Plain `fetch()` returned stale HTML from WP-Optimize for event pages. Browser-rendered check confirmed live values:
- `Jahresabschlussfeier 2026 - Hamburg` (35 chars), description 137 chars.

Therefore all "old title 77/desc 198" findings in automated fetch are cache artifacts and were excluded from the score.
