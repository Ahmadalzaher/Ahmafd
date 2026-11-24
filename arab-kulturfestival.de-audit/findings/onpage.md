# On-Page SEO Findings — arab-kulturfestival.de

**Pages analysed:** 91
**Duplicate titles:** 2 pairs
**Duplicate meta descriptions:** 3 groups (18 URLs)
**H1 issues:** 2 pages

---

## Scorecard

| Element | Status | Count |
|---|---|---|
| Title present | ✅ | 91/91 |
| Title length 30–60 chars | ⚠️ | 63/91 |
| Title > 60 chars | ⚠️ | 19 |
| Title < 30 chars | ⚠️ | 9 |
| Meta description present | ⚠️ | 88/91 |
| Description 70–160 chars | ⚠️ | 70/91 |
| Description < 70 chars | ⚠️ | 19 |
| Description > 160 chars | ⚠️ | 2 |
| Exactly one H1 | ⚠️ | 89/91 |
| Canonical self-referencing | ✅ | 91/91 |
| OG + Twitter card | ✅ | homepage verified |

---

## Finding 1 — Placeholder and broken meta descriptions — **High**

Three pages ship a description that is not a description. These are the highest-confidence on-page defects because they are visibly unfinished text.

| URL | Current description | Fix |
|---|---|---|
| `/festivalprogramm/` | `Your Attractive Heading` | Write a real 140–155 char description, or 301 the page to `/programm/` |
| `/kulturartikel/` | `Your Attractive Heading Kulturartikel Kunst und Kultur ⇢ 01 Articles …` (auto-scraped body) | Set a manual description, or 301 to `/artikel/` |
| `/privacy-policy/` | `datenschutz` | 301 to `/datenschutz/` |
| `/2147-2/` | `Haftungsausschluss:` | 301 to `/impressum/` |
| `/newsletter/` | *(missing)* | Add a description |

`/kulturartikel/` is the clearest tell: Rank Math fell back to scraping the first body text because no description was entered, so the H2 "Your Attractive Heading" is being served to Google as the snippet.

## Finding 2 — 19 titles exceed 60 characters — **Medium**

Google truncates around 580 px (~60 chars). The worst offenders:

| Chars | URL |
|---|---|
| 179 | `/das-waren-die-tage-des-arabischen-kulturfestivals-2023-in-deutschland-und-wir-freuen-uns-auf-mehr-kuenstlerische-und-kulturelle-interaktionen-in-der-zukunft/` |
| 108 | `/abschluss-der-veranstaltungen-des-arabischen-kulturfestivals-2023-in-der-stadt-hamburg/` |
| 67 | `/event/dabke-hamburg-2026-11-14/` |
| 65 | `/die-arabische-kultur-westliche-vorurteile/` |
| 63 | `/ar/cookies/` |
| 62 | 12 × `/event/dabke-kurs-hamburg-*` |
| 61–62 | `/calligraphy/`, `/arabic-music-ar/` |

**Fix:** rewrite the two 2023 archive titles to ~55 chars. For the 12 event pages, shorten `Dabke Tanzen — offener Kurs – 08.10.2026 | Arab Kulturfestival` to `Dabke Kurs Hamburg · 08.10.2026 | Arab Kulturfestival` (48 chars) and keep the date unique.

## Finding 3 — 9 titles under 30 characters — **Medium**

| Chars | Title | URL |
|---|---|---|
| 19 | `Arab Kulturfestival` | `/arabische-kultur-im-exil-2/` |
| 27 | `Press - Arab Kulturfestival` | `/en/press/` |
| 28 | `Artikel -arab-kulturfestival` | `/category/arab-kulturfestival/` |
| 28 | `Events - Arab Kulturfestival` | `/en/events/` |
| 29 | `Magazin - Arab Kulturfestival` | `/category/magazin/` |
| 29 | `Contact - Arab Kulturfestival` | `/en/contact/` |
| 29 | `Cookies - Arab Kulturfestival` | `/en/cookies/` |
| 29 | `Privacy - Arab Kulturfestival` | `/en/privacy/` |
| 29 | `Kontakt - Arab Kulturfestival` | `/kontakt/` |

Note `Artikel -arab-kulturfestival` is also malformed (missing space, lowercase brand, hyphen stuck to the word).

**Fix:** add the differentiator to each — e.g. `Kontakt & Anfahrt | Arab Kulturfestival Hamburg`, `English Events in Hamburg | Arab Kulturfestival`.

## Finding 4 — Duplicate titles and descriptions — **Medium**

**Duplicate titles (2 pairs):**

```
Arabische Musik - Arab Kulturfestival
  /angebot/arabische-musik/  ←  /arabische-musik/

Kalligrafie - Arab Kulturfestival
  /angebot/kalligrafie/      ←  /kalligrafie/
```

**Duplicate descriptions (3 groups):**

- 6 × EN event pages share `Arabic folk dance for beginners and advanced dancers — no experience, no registration needed.`
- 6 × AR event pages share `رقص شعبي عربي للمبتدئين والمتقدمين — بلا خبرة سابقة وبلا تسجيل.`
- The German event pages repeat the same pattern.

For recurring events, per-date descriptions are correct — but add the date and venue so the snippet is unique:

```
Dabke für Anfänger & Fortgeschrittene im Goldbekhaus, Moorfuhrtweg 9 — 08.10.2026, 18:15. Ohne Anmeldung.
```

## Finding 5 — Two pages have duplicate H1s — **High**

| URL | H1s found |
|---|---|
| `/arabische-kultur-im-exil-2/` | `RechnungRechnung` (theme container) + `Arabische Kultur im Exil` (article) |
| `/kulturartikel/` | `Kulturartikel` (theme) + `Kulturartikel` (Spectra heading block) |

`RechnungRechnung` is leftover demo content from a page builder and is the first H1 on the page — it is what Google sees as the primary heading.

**Fix:** remove the theme-level H1 on article templates (it should be the post title only once), delete the leftover `RechnungRechnung` block, and demote the Spectra heading on `/kulturartikel/` to H2.

## Finding 6 — 19 meta descriptions under 70 characters — **Medium**

Short descriptions waste snippet real estate. Worst:

| Chars | URL |
|---|---|
| 11 | `/privacy-policy/` — `datenschutz` |
| 19 | `/2147-2/` — `Haftungsausschluss:` |
| 23 | `/festivalprogramm/` |
| 35 | `/en/contact/` |
| 39 | `/kontakt/` |
| 49 | `/event/dabke-hamburg-2026-11-14/` |
| 51–63 | 6 AR service pages, 6 AR event pages, `/en/about/`, `/en/events/` |

## Finding 7 — Thin landing pages — **Medium**

24 pages are under 350 words. Excluding legal pages (expected), the content pages that matter:

| Words | URL |
|---|---|
| 206 | `/kulturartikel/` (duplicate, should be removed) |
| 302–345 | 12 × `/angebot/*` service pages |
| 327 | `/artikel/` (article index) |
| 319 | `/en/articles/` |
| 312 | `/ar/articles/` |

The 12 `/angebot/` pages are ~310 words each and are near-duplicates of each other (0.66–0.74 shingle similarity). They are the pages that should rank for "Dabke Kurs Hamburg", "arabische Musik Hamburg", "Kalligrafie Workshop Hamburg".

**Fix:** expand each service page to 700–1,000 words with unique content — instructor, schedule, price, venue, what to bring, FAQ — and add `Service` + `FAQPage` schema.

## Finding 8 — Internal anchor text is generic — **Low**

Navigation and cards use `Mehr erfahren`, `⇢`, and image-only links. Descriptive anchors (`Dabke-Kurs in Hamburg buchen`) would strengthen the `/angebot/` cluster, which is currently orphaned.

## Finding 9 — Open Graph image is a logo, not a share card — **Medium**

```
og:image = /wp-content/uploads/2023/09/Logo-2.png   (639 × 219)
twitter:card = summary_large_image
```

`summary_large_image` expects 1200×630 (1.91:1). A 639×219 logo (2.92:1) will be letterboxed or cropped in every social and messaging preview. The site has strong festival photography (`akf-day1-*.webp`) available.

**Fix:** create a 1200×630 OG image per key page type (home, event, article) and set it in Rank Math's social tab.

Also remove `twitter:label1 = Written by` / `twitter:data1 = admin` — it exposes the WordPress username.
