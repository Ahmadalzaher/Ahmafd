# Full SEO Audit — arab-kulturfestival.de

**Audit date:** 8 November 2026
**Site:** https://arab-kulturfestival.de/
**Platform:** WordPress 7.1.4 · Rank Math SEO · Yalla theme
**Languages:** German (default), English, Arabic (RTL)
**Pages crawled:** 91 of 91 sitemap URLs (100%)
**Tooling:** Claude SEO skill suite v2.5.0

---

## SEO Health Score

# 74 / 100

| Category | Weight | Score | Weighted |
|---|---:|---:|---:|
| Technical SEO | 22% | 78 | 17.2 |
| Content Quality | 23% | 80 | 18.4 |
| On-Page SEO | 20% | 74 | 14.8 |
| Schema / Structured Data | 10% | 78 | 7.8 |
| Performance (CWV) | 10% | 68 | 6.8 |
| AI Search Readiness | 10% | 62 | 6.2 |
| Images | 5% | 62 | 3.1 |
| **Total** | **100%** | | **74.3** |

**Verdict:** A technically sound, well-written site with a clean crawl and excellent copy — held back by a long tail of legacy duplicate URLs, orphaned service pages, incomplete event structured data, and unoptimised image delivery. Most issues are configuration and cleanup, not rebuilds.

---

## Executive summary

The site does the hard things well. All 91 sitemap URLs return 200, every page has a self-referencing canonical and correct `lang`, robots.txt and the sitemap index are healthy, redirects are clean, there are zero broken links or images, and the homepage copy scores 96/100 on quality with no AI-filler patterns. The Organization schema is unusually complete for a small cultural site.

The problems are concentrated in four areas:

1. **Duplicate and legacy URLs.** Nine legacy/duplicate page pairs are live, self-canonical, and in the sitemap — about 10% of the index. Two of them (`/festivalprogramm/`, `/kulturartikel/`) still carry the placeholder text `Your Attractive Heading` in their meta descriptions.
2. **Orphaned service pages.** All 12 `/angebot/*` pages have zero inbound internal links. They are the commercial core of the site (Dabke, music, calligraphy, literature) and cannot be reached by crawling from the homepage.
3. **Broken hreflang on events.** The 18 event URLs (6 dates × 3 languages) declare only their own language. Google cannot cluster them, so the EN and AR versions compete as separate thin pages.
4. **Image delivery.** 3.47 MB of images on the homepage, a 180 KB unoptimised hero JPEG as the likely LCP element, and 184 of 618 images (30%) without alt text.

### Top 5 critical issues

| # | Issue | Severity | Effort |
|---|---|---|---|
| 1 | 9 duplicate/legacy URLs indexable and self-canonical | High | 2–3 h |
| 2 | 12 service pages orphaned (no internal links) | High | 1 h |
| 3 | Event hreflang not reciprocal (18 URLs) | High | 1–2 h |
| 4 | Hero image 180 KB JPEG, no dimensions, no `fetchpriority` | High | 1 h |
| 5 | 184 images missing alt text (30%) | High | 2–3 h |

### Top 5 quick wins

| # | Win | Effort | Impact |
|---|---|---|---|
| 1 | Replace 3 placeholder meta descriptions | 15 min | High |
| 2 | Add `offers` to Event schema (unlocks event rich results) | 30 min | High |
| 3 | Add services block to homepage → fixes 12 orphans | 30 min | High |
| 4 | 301 the 5 `/uncategorized/` internal links | 15 min | Medium |
| 5 | Add 1200×630 OG image (current is a 639×219 logo) | 30 min | Medium |

---

## 1. Technical SEO — 78/100

**Works:** HTTPS with correct `http→https` and `www→non-www` 301s; robots.txt allows crawling and declares the sitemap; sitemap index with 5 children and 91 valid URLs; 91/91 self-canonical; no accidental noindex; real 404s; server-rendered content; correct `lang`/`dir`.

**Issues:**

- **9 duplicate/legacy URLs live and self-canonical** (`/2147-2/`, `/privacy-policy/`, `/festivalprogramm/`, `/kulturartikel/`, `/arab-kulturfestival-2/`, `/arabische-musik/`, `/arabische-musik-2/`, `/kalligrafie/`, `/kalligrafie-2/`, plus `/arabische-kultur-im-exil-2/`). **High.**
- **19 orphan pages, 27 unreachable from the homepage.** **High.**
- **Security headers missing** — no HSTS, CSP, `X-Content-Type-Options`, `X-Frame-Options`, or `Referrer-Policy`; `X-Powered-By: PHP/8.4.27` exposed. **Medium.**
- **5 internal redirects** from `/uncategorized/…`. **Low.**
- **Category archives in the sitemap** with no unique value. **Low.**

Details: `findings/technical.md`

---

## 2. Content Quality — 80/100

**Works:** 96/100 quality score, zero AI-pattern flags, zero filler, information density 1.0. Real entity signals (address, phone, founder, socials). Specific venue and schedule detail. Native Arabic copy.

**Issues:**

- **12 `/angebot/*` pages are 55–74% shingle-duplicates** of each other at ~310 words. **High.**
- **9 duplicate/legacy article pairs** dilute topical authority. **High.**
- **Thin pages:** `/newsletter/` 183 words, `/kulturartikel/` 206, legal pages 277–298 (acceptable), article indexes 312–327 with no intro. **Medium.**
- **E-E-A-T gaps:** Article author is the Organization, not a Person; no author bios; no visible `dateModified`; no press citations; Organization lacks `foundingDate`/`nonprofitStatus`. **Medium.**
- **No Arabic webfont subset** — Arabic pages fall back to system fonts. **Medium.**

Details: `findings/content.md`

---

## 3. On-Page SEO — 74/100

**Works:** Title on 91/91, meta description on 88/91, canonical on 91/91, OG + Twitter cards present, keyword-relevant homepage title (`Arabisches Kulturfestival Hamburg - Termine & Dabke`).

**Issues:**

- **Placeholder descriptions:** `Your Attractive Heading` on `/festivalprogramm/`; auto-scraped body text on `/kulturartikel/`; `datenschutz` on `/privacy-policy/`; `Haftungsausschluss:` on `/2147-2/`. **High.**
- **Duplicate H1s:** `/arabische-kultur-im-exil-2/` (`RechnungRechnung` + real title), `/kulturartikel/` (same H1 twice). **High.**
- **19 titles > 60 chars** (worst: 179 and 108 chars on 2023 archive posts; 12 event titles at 62). **Medium.**
- **9 titles < 30 chars** (worst: `Arab Kulturfestival` at 19; `Artikel -arab-kulturfestival` malformed). **Medium.**
- **19 descriptions < 70 chars**, 2 > 160. **Medium.**
- **3 missing descriptions** (2 category archives, `/newsletter/`). **Medium.**
- **2 duplicate title pairs** (`/angebot/arabische-musik/` ↔ `/arabische-musik/`, same for Kalligrafie). **Medium.**
- **OG image is a 639×219 logo** used with `summary_large_image`; `twitter:data1 = admin` leaks the username. **Medium.**
- **Hreflang reciprocity broken on 18 links** — see §7. **High.**

Details: `findings/onpage.md`

---

## 4. Schema & Structured Data — 78/100

**Works:** JSON-LD on 91/91 with zero parse errors. `Organization` (address, phone, email, founder, `sameAs`), `WebSite`, `BreadcrumbList` (86), `Article` (20), `Event` (19).

**Issues:**

- **No `LocalBusiness` / `NGO` markup** despite a physical Hamburg address. **High.**
- **`Event` has no `offers`** — blocks Event rich results entirely. Also missing `performer`, `isAccessibleForFree`, `eventSchedule`; `image` is a theme placeholder. **High.**
- **`Article.author` is the Organization**, not a Person. **Medium.**
- **No `Service` schema** on 12 service pages. **Medium.**
- **No `FAQPage`** anywhere. **Medium.**
- `WebSite` has no `SearchAction`. **Low.**
- `streetAddress` shows a mojibake `ß` in one fetch — verify UTF-8 encoding. **Low.**

Details: `findings/schema.md`

---

## 5. Performance — 68/100

> ⚠️ **Core Web Vitals not measured.** PageSpeed Insights returned `PSI rate limit exceeded` on every attempt. Findings are origin-measured proxies.

**Measured:**

| Metric | Value |
|---|---|
| TTFB (median) | 524–676 ms |
| Homepage HTML (gzip) | 107 KB |
| Homepage images | 61 files / **3.47 MB** |
| JS / CSS | 11 files / 55 KB · 9 files / 33 KB |
| Inline script/style blocks | 12 / 9 |
| Font preloads | 2 (Latin only) |

**Issues:**

- **Hero LCP image:** 180 KB JPEG, no `width`/`height`, no `fetchpriority`, empty alt. **High.**
- **3.47 MB of images** on the homepage; 45-slide gallery at 80–102 KB each. **High.**
- **TTFB borderline** (676 ms home) and `Cache-Control: max-age=0` prevents any caching. **Medium.**
- **Arabic pages have no Arabic webfont.** **Medium.**
- **45 gallery slides in the initial DOM.** **Medium.**

Details: `findings/performance.md`

---

## 6. Images — 62/100

| Metric | Value |
|---|---|
| Images | 618 |
| Missing alt | **184 (29.8%)** on 40 pages |
| Missing width/height | 274 (44.3%) |
| Lazy-loaded | 488 (79%) |
| Broken | 0 |
| Homepage weight | 3.47 MB |

**Issues:**

- **184 missing alt**, concentrated in theme templates (`hero-dabke.jpg`, `stage-group.jpg`, `calligraphy.jpg`, `music.jpg`, `community-circle.jpg`, 6 SVG step diagrams) — fixing 11 files fixes ~120 instances. **High.**
- **Wrong image on music pages:** `calligraphy.jpg` appears twice on `/angebot/arabische-musik/`. **High.**
- **Gallery alt is auto-numbered** (`Bilder vom Arab Kulturfestival — 34`). **Medium.**
- **274 images without dimensions** → CLS risk. **Medium.**

Details: `findings/images.md`

---

## 7. International SEO / Hreflang — High priority

- 91/91 pages emit hreflang with self-reference and `x-default`. ✅
- **18 event URLs declare only their own language** (6 dates × DE/EN/AR). The translations are not linked to each other at all.
- **5 legacy pages point hreflang at `/en/` and `/ar/`** which do not link back (and are the wrong targets).
- **2 category archives** declare article listing pages as translations.

Details: `findings/hreflang.md`

---

## 8. AI Search Readiness — 62/100

**Works:** server-rendered content, AI crawlers allowed, real 404s, semantic HTML, complete Organization entity.

**Issues:**

- **No `llms.txt`** (404). **Medium.**
- **No `Content-Signal`** in robots.txt. **Low.**
- **No Markdown delivery.** **Low.**
- **Weak citability:** no definition paragraph, no FAQ blocks, no visible freshness date, price/registration not stated early. **Medium.**
- **No `WebMCP`** — not applicable (no booking flow). **N/A.**

Details: `findings/ai-search.md`

---

## 9. Local SEO — 72/100

**Works:** NAP consistent across all three legal pages and schema; named responsible person; venue-level event detail; city in the homepage title/H1.

**Issues:** no LocalBusiness schema, no location landing pages, no GBP link, no map/directions, no reviews. Details: `findings/local.md`

---

## 10. Search Experience (SXO) — 70/100

**Issues:** service intent lands on expiring dated event pages instead of a durable hub; event pages lack price/CTA above the fold; the H1 is poetic rather than entity-matching; no FAQ blocks. Details: `findings/sxo.md`

---

## What was not assessed

| Area | Reason |
|---|---|
| Core Web Vitals field/lab data | PSI quota exceeded on every attempt |
| Rendered mobile/desktop screenshots | Playwright not installed |
| Backlink profile | No Moz/Bing/DataForSEO credentials |
| Search Console / GA4 traffic | No Google API credentials |
| Live SERP positions | No SERP API configured |
| Visual above-fold analysis | No rendering engine available |

These are environment limitations, not site defects. Retry with credentials to complete the picture.

---

## Recommended sequencing

| Phase | Timeframe | Focus |
|---|---|---|
| 1 — Critical | Week 1 | 301 duplicates, fix hreflang, add `offers`, fix placeholders |
| 2 — High impact | Weeks 2–3 | Service hub, internal links, hero image, alt text |
| 3 — Content & authority | Month 2 | Expand service pages, FAQ, E-E-A-T, llms.txt |
| 4 — Monitoring | Ongoing | GSC, CWV, drift baseline |

Full task list: `ACTION-PLAN.md`
Structured data: `audit-data.json`
Per-category detail: `findings/`
