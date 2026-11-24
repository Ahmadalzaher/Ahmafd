# Hreflang & International SEO Findings — arab-kulturfestival.de

**Languages:** German (`de`, default), English (`en`), Arabic (`ar`, RTL)
**Pages emitting hreflang:** 91/91 (100%)
**Reciprocity failures:** 18 links across 8 pages + 18 event pages with incomplete sets

---

## What works

- `lang` attribute correct on every page (`de`, `en`, `ar`).
- Arabic pages set `dir="rtl"`.
- Every page emits a self-referencing hreflang plus `x-default`.
- Core navigation pages (`/`, `/festival/`, `/programm/`, `/events/`, `/artikel/`, `/presse/`, `/ueber-uns/`, `/kontakt/`) have complete 4-entry sets (`de`, `en`, `ar`, `x-default`).
- URLs are stable and locale-prefixed (`/en/...`, `/ar/...`) rather than parameter-based.

## Finding 1 — Event translations are not linked to each other — **High**

Each event exists in 2–3 languages as separate URLs, but each URL declares only its own language.

```
DE  /event/dabke-kurs-hamburg-2026-10-08/     hreflang: de, x-default
EN  /event/dabke-kurs-hamburg-2026-10-08-2/   hreflang: en
AR  /event/dabke-kurs-hamburg-2026-10-08-4/   hreflang: ar
```

This repeats for all six course dates (Oct 08, Oct 22, Nov 05, Nov 19, Dec 03, Dec 17) — 18 URLs. `/event/dabke-hamburg-2026-11-14/` is Arabic-only and correctly self-references.

**Impact:** Google cannot cluster the translations, so it may treat the EN and AR duplicates as separate thin pages rather than language alternates. Arabic and English event queries lose the German page's authority.

**Fix (Rank Math):** for each event, open the translated event and set the translation group so all three URLs emit the full set:

```html
<link rel="alternate" hreflang="de" href="https://arab-kulturfestival.de/event/dabke-kurs-hamburg-2026-10-08/">
<link rel="alternate" hreflang="en" href="https://arab-kulturfestival.de/event/dabke-kurs-hamburg-2026-10-08-2/">
<link rel="alternate" hreflang="ar" href="https://arab-kulturfestival.de/event/dabke-kurs-hamburg-2026-10-08-4/">
<link rel="alternate" hreflang="x-default" href="https://arab-kulturfestival.de/event/dabke-kurs-hamburg-2026-10-08/">
```

## Finding 2 — Legacy pages point at homepage translations that do not point back — **High**

Eight legacy/duplicate pages claim `/en/` and `/ar/` as their translations, but the homepage translations do not list them.

| Source page | Declared `en` | Declared `ar` | Problem |
|---|---|---|---|
| `/2147-2/` | `/en/` | `/ar/` | Wrong target — should be `/en/legal-notice/`, `/ar/imprint/` |
| `/festivalprogramm/` | `/en/` | `/ar/` | Wrong target — should be `/en/program/`, `/ar/program/` |
| `/kulturartikel/` | `/en/` | `/ar/` | Wrong target — should be `/en/articles/`, `/ar/articles/` |
| `/newsletter/` | `/en/` | `/ar/` | No EN/AR newsletter page exists |
| `/privacy-policy/` | `/en/` | `/ar/` | Wrong target — should be `/en/privacy/`, `/ar/privacy/` |

**Impact:** Google sees an asymmetric annotation and ignores the cluster. Because these are also duplicate pages, the annotation actively muddies which URL is canonical.

**Fix:** 301 the legacy pages (see `technical.md`). If any must stay, point hreflang at the real translated equivalent, not the homepage.

## Finding 3 — Category archives declare article-page translations — **Medium**

`/category/arab-kulturfestival/` and `/category/magazin/` declare `/artikel/`, `/en/articles/`, `/ar/articles/` as translations. Those are listing pages, not translations of a category archive, and they do not link back.

**Fix:** remove hreflang from category archives entirely, or noindex the archives and drop them from the sitemap.

## Finding 4 — `x-default` consistency — **Low**

`x-default` is present on 100% of pages, which is good. On the 18 event pages it currently points to the page itself instead of the German master. After Finding 1 is fixed, keep `x-default` on the German URL.

## Validation commands

After fixes, re-run:

```bash
py crawl.py crawl-results.json
py -c "import json;d=json.load(open('crawl-results.json'));print(sum(1 for x in d if len(x['hreflang'] or [])<2))"
```

Target: 0 pages with fewer than 2 alternates except Arabic-only content, and 0 reciprocity failures.
