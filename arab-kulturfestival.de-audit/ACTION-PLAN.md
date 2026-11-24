# Action Plan — arab-kulturfestival.de

**Audit:** 8 Nov 2026 · **Health score:** 74/100
Ordered by severity, then effort. Each item has an owner-agnostic description, the evidence, and a verification step.

---

## Phase 1 — Critical fixes (Week 1)

### 1.1 Redirect the 9 duplicate / legacy URLs — High · 2–3 h
301 each legacy slug to its current equivalent, then remove it from the sitemap. Do not leave it live with `noindex` — redirect preserves equity.

| From | To |
|---|---|
| `/2147-2/` | `/impressum/` |
| `/privacy-policy/` | `/datenschutz/` |
| `/festivalprogramm/` | `/programm/` |
| `/kulturartikel/` | `/artikel/` |
| `/arab-kulturfestival-2/` | `/festival/` |
| `/arabische-musik/` | `/angebot/arabische-musik/` |
| `/arabische-musik-2/` | `/angebot/arabische-musik/` |
| `/kalligrafie/` | `/angebot/kalligrafie/` |
| `/kalligrafie-2/` | `/angebot/kalligrafie/` |
| `/arabische-kultur-im-exil-2/` | `/arabische-kultur-im-exil/` |

**Verify:** each old URL returns 301, final URL 200, old URL absent from `page-sitemap.xml`.

### 1.2 Fix event hreflang reciprocity — High · 1–2 h
For each of the 6 course dates, link DE/EN/AR in one translation group so all three emit the full set (see `findings/hreflang.md` for the exact block).

**Verify:** `py crawl.py` → 0 pages with `len(hreflang) < 2` except Arabic-only content; 0 reciprocity failures.

### 1.3 Add `offers` to Event schema — High · 30 min
Unlocks Event rich results. Include `price`, `priceCurrency`, `availability`, `url`, `validFrom`. Add `performer` and `isAccessibleForFree` where applicable.

**Verify:** Rich Results Test passes for one event URL with 0 errors.

### 1.4 Replace placeholder meta descriptions — High · 15 min

| URL | Action |
|---|---|
| `/festivalprogramm/` | 301 (see 1.1) — otherwise write a real description |
| `/kulturartikel/` | 301 (see 1.1) |
| `/privacy-policy/` | 301 (see 1.1) |
| `/2147-2/` | 301 (see 1.1) |
| `/newsletter/` | Add a 140–155 char description |

**Verify:** no description matches `Your Attractive Heading`, `datenschutz`, or `Haftungsausschluss:`.

### 1.5 Fix duplicate H1s — High · 30 min
- `/arabische-kultur-im-exil-2/`: delete the leftover `RechnungRechnung` heading block.
- `/kulturartikel/`: demote the Spectra heading to H2 (or 301 the page).

**Verify:** exactly one H1 per page across the crawl.

### 1.6 Add a services block to the homepage — High · 30 min
Link all 12 `/angebot/*` pages from the homepage (and `/festival/`). This single change fixes 12 of the 19 orphans.

**Verify:** `py linkgraph.py` → orphans drop from 19 to ~7.

### 1.7 Optimise the hero image — High · 1 h
Convert `hero-dabke.jpg` to WebP (~1600 px), add `width`/`height`, `fetchpriority="high"`, `decoding="async"`, real alt, and preload it.

**Verify:** hero is < 80 KB, has dimensions, and is preloaded.

### 1.8 Add alt text to theme images — High · 2–3 h
11 theme files cover ~120 instances: `hero-dabke.jpg`, `stage-group.jpg`, `calligraphy.jpg`, `music.jpg`, `community-circle.jpg`, `dabke-step-1..6.svg`. Alt text suggestions in `findings/images.md`.

**Verify:** missing-alt count drops below 40.

---

## Phase 2 — High-impact improvements (Weeks 2–3)

### 2.1 Build the Dabke hub page — High · 3–4 h
Rewrite `/angebot/folkloretanz-dabke/` as the durable landing page: all upcoming dates, venue, price, level, language, what to bring, 4 FAQs. Link every event instance to it.

### 2.2 Expand the 12 service pages — High · 2–3 days
700–1,000 words each, unique content, activity-specific photos, `Service` + `FAQPage` schema. Target < 40% shingle overlap with siblings.

### 2.3 Add `LocalBusiness` / `NGO` schema — High · 45 min
Use the JSON-LD block in `findings/schema.md`. Add `foundingDate`, `areaServed`, `nonprofitStatus`.

### 2.4 Add price + CTA above the fold on event pages — High · 1 day
Date, time, venue, price, "Keine Anmeldung nötig", and `Zum Kalender hinzufügen` / `Route planen`.

### 2.5 Trim the homepage gallery — High · 1 h
45 slides → 8–12 curated photos. Add 480 w / 768 w `srcset` candidates.

### 2.6 Fix the wrong image on music pages — Medium · 15 min
Replace the two `calligraphy.jpg` instances on `/angebot/arabische-musik/` (and EN/AR) with a concert photo.

### 2.7 Fix remaining internal redirects — Medium · 15 min
Update the 5 `/uncategorized/…` links to their final URLs.

### 2.8 Add security headers — Medium · 1 h
HSTS, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, CSP (report-only first). Remove `X-Powered-By`.

### 2.9 Enable page caching — Medium · 1 h
Replace `Cache-Control: max-age=0` with `public, max-age=600, s-maxage=86400` for anonymous HTML; add a CDN.

### 2.10 Create a 1200×630 OG image — Medium · 30 min
Replace the 639×219 logo; remove `twitter:data1 = admin`.

### 2.11 Shorten 19 over-long titles — Medium · 1 h
Focus on the two 2023 archive posts (179 and 108 chars) and the 12 event titles (62 chars).

### 2.12 Lengthen 9 short titles — Medium · 30 min
Add city/differentiator. Fix the malformed `Artikel -arab-kulturfestival`.

### 2.13 Add Arabic webfont subset — Medium · 1 h
Preload only on `lang="ar"` pages.

---

## Phase 3 — Content & authority (Month 2)

### 3.1 Add `llms.txt` — Medium · 30 min
Template in `findings/ai-search.md`.

### 3.2 Add FAQ blocks + `FAQPage` schema — Medium · 1 day
Homepage, `/programm/`, `/angebot/folkloretanz-dabke/`. Questions in `findings/sxo.md`.

### 3.3 Add definition paragraphs — Medium · 2 h
40–60 word self-contained answer at the top of homepage, `/festival/`, `/programm/`, each service page.

### 3.4 Person author + bylines — Medium · 2 h
Change `Article.author` to a Person with bio; show byline and `dateModified` on the template.

### 3.5 Add location landing pages — Medium · 1 day
`Dabke Kurs Hamburg`, `Arabische Musik Hamburg`, `Kalligrafie Workshop Hamburg`. Add "Hamburg" to service H1s at minimum.

### 3.6 Create a Google Business Profile — Medium · 1 h
If not already present. Add the GBP URL to `Organization.sameAs`.

### 3.7 Add map + directions to `/kontakt/` — Low · 1 h
Plus transit and parking notes.

### 3.8 Add `Content-Signal` to robots.txt — Low · 15 min
Choose the policy deliberately: `search=yes, ai-train=yes|no, ai-input=yes`.

### 3.9 Add `SearchAction` to `WebSite` — Low · 15 min
Only if `/?s=` search is enabled.

### 3.10 Add reviews/testimonials — Low · 2 h
3–5 participant quotes; `Review` schema only for genuinely collected reviews.

---

## Phase 4 — Monitoring (Ongoing)

### 4.1 Baseline the crawl
```bash
py crawl.py crawl-results.json
py linkgraph.py
py dupes.py
```
Store outputs as the pre-fix baseline.

### 4.2 Connect Search Console
Verify the property, submit the sitemap index, monitor:
- Pages → "Duplicate, Google chose different canonical" (should fall after 1.1)
- International targeting → hreflang errors (should fall after 1.2)
- Enhancements → Events (should activate after 1.3)

### 4.3 Retry Core Web Vitals
```bash
py scripts/pagespeed_check.py https://arab-kulturfestival.de/ --strategy both --api-key <KEY> --json
```
Run after 1.7 and 2.5 to confirm LCP improvement.

### 4.4 Add drift monitoring
Capture a baseline with the `seo-drift` skill before the next deploy, then diff after each release.

### 4.5 Monthly checks
- Orphan count = 0
- Duplicate title/description groups = 0 (excluding intentional event series)
- Missing alt < 5%
- TTFB < 400 ms
- All sitemap URLs 200

---

## Effort summary

| Phase | Effort | Issues closed |
|---|---|---|
| 1 — Critical | ~1.5 days | 8 |
| 2 — High impact | ~1 week | 13 |
| 3 — Content | ~1 week | 10 |
| 4 — Monitoring | ongoing | — |

**Expected outcome after Phase 1–2:** health score 74 → ~86, duplicate index share eliminated, event rich results eligible, LCP improved by an estimated 0.8–1.5 s on mobile.
