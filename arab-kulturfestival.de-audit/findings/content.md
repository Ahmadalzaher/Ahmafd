# Content Quality & E-E-A-T Findings — arab-kulturfestival.de

**Content quality score (homepage):** 96/100
**AI-pattern flags:** 0
**Filler score:** 0
**Information density:** 1.0
**Repetition score:** 27 (low)
**Homepage word count:** 824

---

## What works

- **No AI-generated filler.** Zero matches on the filler and AI-pattern dictionaries, 0 repetition flags. The German copy reads like it was written by the organisation, not templated.
- **Real entity signals.** Named founder (Ahmad AL Zaher), physical address (Julius-Vosseler-Straße 106b, 22527 Hamburg), phone (+49 178 1116220), email, and three social profiles.
- **Specific, non-generic content.** Venue names (Goldbekhaus, Moorfuhrtweg 9), exact times, named artists, course dates — these are strong local relevance signals.
- **Trilingual coverage.** DE/EN/AR with native Arabic copy, not machine translation.
- **Good semantic structure.** 11 H2 and 9 H3 on the homepage; article pages use breadcrumbs and Article schema.

## Finding 1 — The 12 service pages are near-duplicates — **High**

Every `/angebot/*` page shares 55–74% of its 5-word shingles with at least one sibling. At 0.74 (`الأدب والحكواتي` vs `الموسيقى العربية`) the pages are substantially the same text.

Cause: all 12 use the same hero, the same "Was dich erwartet" block, the same "Für wen" block, and the same CTA. Only the first paragraph differs.

**Impact:** Google picks one page and suppresses the rest. Since these are the pages that should rank for service-intent queries ("Dabke Kurs Hamburg", "Kalligrafie Workshop"), the whole cluster underperforms.

**Fix:** rewrite each service page around unique substance:

- instructor name and background
- exact schedule and location
- price / free entry
- group size and language of instruction
- 3–4 unique FAQs
- photos specific to that activity (currently `calligraphy.jpg` and `music.jpg` are reused across pages)

Target 700–1,000 words per page, <40% shingle overlap with siblings.

## Finding 2 — Duplicate and legacy articles dilute topical authority — **High**

19 non-event duplicate pairs were detected. Outside `/angebot/`, the substantive duplicates are:

| Pair | Similarity | Action |
|---|---|---|
| `/arabische-kultur-im-exil/` ↔ `/arabische-kultur-im-exil-2/` | high | Keep one, 301 the other |
| `/arabische-musik/` ↔ `/angebot/arabische-musik/` | identical title | 301 to the service page |
| `/kalligrafie/` ↔ `/angebot/kalligrafie/` | identical title | 301 to the service page |
| `/festivalprogramm/` ↔ `/programm/` | same programme | 301 |
| `/kulturartikel/` ↔ `/artikel/` | same listing | 301 |
| `/privacy-policy/` ↔ `/datenschutz/` | old 3,378-word version | 301 |
| `/2147-2/` ↔ `/impressum/` | duplicate Impressum | 301 |

**Why it matters:** the site has ~91 indexable URLs, so 9 duplicate pages is ~10% of the index. For a local cultural site competing on a narrow set of German/Arabic queries, that is a meaningful share of crawl and link equity pointing at the wrong URL.

## Finding 3 — Thin legal and index pages — **Medium**

| Words | URL | Verdict |
|---|---|---|
| 183 | `/newsletter/` | Too thin for a standalone page — merge into `/kontakt/` or add real value (archive, frequency, privacy note) |
| 206 | `/kulturartikel/` | Duplicate listing — delete |
| 277 | `/ar/imprint/` | Acceptable for legal, but AR version is shorter than DE |
| 298 | `/impressum/` | Acceptable |
| 298 | `/en/legal-notice/` | Acceptable |
| 312–327 | `/artikel/`, `/en/articles/`, `/ar/articles/` | Index pages with no introductory copy |

Legal pages being short is normal and not a ranking problem. The index pages, however, have no descriptive text — a 100-word intro on `/artikel/` would help them rank for "arabische Kultur Artikel".

## Finding 4 — E-E-A-T gaps — **Medium**

Strengths: real address, founder, social profiles, event dates.

Gaps:

1. **No author pages or bylines.** Article schema has `Article` but no `author` Person with `url`/`sameAs`. For cultural journalism, a named author with a bio is the cheapest E-E-A-T win.
2. **No `About` depth in Arabic.** `/ar/` exists but the about page content is thinner than the German.
3. **No reviews or press citations on-page.** A "Presse" page exists (`/presse/`); link actual press mentions with outlet names and dates.
4. **No `foundingDate`, `numberOfEmployees`, or `nonprofitStatus`** in the Organization schema — relevant for a cultural NGO.
5. **No visible last-updated date on articles.** `article:modified_time` exists in OG tags but is not shown to users.

## Finding 5 — Readability — **Low**

German copy uses long compound sentences (typical for the language) and averages ~20 words/sentence on article pages. Not a defect, but for the Arabic pages, check line-height and font-size for RTL — the theme loads `fraunces` and `work-sans` latin subsets only:

```
/wp-content/themes/yalla/assets/fonts/fraunces-normal-latin.woff2
/wp-content/themes/yalla/assets/fonts/work-sans-normal-latin.woff2
```

**There is no Arabic (`arabic` subset) webfont preloaded.** Arabic text will fall back to a system font, which often renders smaller and with inconsistent line-height. Add an Arabic subset or a dedicated Arabic family.

## Finding 6 — Content opportunities — **Info**

The site has strong raw material but few query-targeted pages. Missing page types:

- `Dabke lernen Hamburg` (course landing page — currently only event instances)
- `Arabische Musik live Hamburg` (concert landing page)
- `Arabisch Kalligrafie Workshop Hamburg`
- `Arabisches Kulturfestival 2026 Programm` (the `/programm/` page should own this)
- FAQ hub: `Was ist Dabke?`, `Wo findet das Festival statt?`, `Ist der Eintritt frei?`

Each of these is a single page with real content, not programmatic filler.
