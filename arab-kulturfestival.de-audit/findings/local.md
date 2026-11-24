# Local SEO Findings — arab-kulturfestival.de

**Business type:** Local cultural organisation / event series (brick-and-mortar + recurring events)
**City:** Hamburg, Germany
**NAP status:** Consistent across DE/EN legal pages and Organization schema

---

## What works

- **Consistent NAP.** The same name, street, postcode, city, phone, and email appear in `/impressum/`, `/en/legal-notice/`, `/ar/imprint/`, and the `Organization` JSON-LD:

  ```
  Arab Kulturfestival
  Julius-Vosseler-Straße 106b
  22527 Hamburg, Deutschland
  +49 178 1116220
  info@arab-kulturfestival.de
  ```

- **Named responsible person** (Ahmad AL Zaher) with a role description — a real E-E-A-T and local-trust signal.
- **Venue-level detail in events** (Goldbekhaus, Moorfuhrtweg 9, 22301 Hamburg) — this is the kind of specificity that maps to local queries.
- **Hamburg is named in the homepage title**, H1, and body copy.
- **Social profiles** in `sameAs` (Instagram, Facebook, YouTube).

## Finding 1 — No `LocalBusiness` schema — **High**

Covered in `schema.md`. The single biggest local gap: the site has no machine-readable local business entity, so it cannot feed a knowledge panel or map pack result.

## Finding 2 — No location landing pages — **Medium**

Everything local lives on the homepage and on event pages. There is no page targeting:

- `Dabke Kurs Hamburg`
- `Arabische Musik Hamburg`
- `Kalligrafie Workshop Hamburg`
- `Arabisches Kulturfestival Hamburg 2026`

The `/angebot/*` pages target the activity but not the city. Adding "Hamburg" to the H1 and title of each service page (`Dabke Kurs Hamburg — offener Volkstanzabend`) is a one-line change with direct local intent value.

## Finding 3 — No embedded map or directions — **Low**

`/kontakt/` gives an address but no map embed, no directions, no transit note. For a venue-based event series, a map plus "So erreichst du uns" (U/S-Bahn, parking) improves both UX and local relevance.

## Finding 4 — Google Business Profile not linked — **Medium**

No `hasMap`, no GBP URL, no `sameAs` entry for the business profile. If a GBP exists, add it to `Organization.sameAs`. If not, create one for "Arab Kulturfestival" at the Hamburg address — event series qualify and it is the strongest local visibility lever available.

## Finding 5 — No reviews or ratings on-site — **Medium**

No `AggregateRating` or `Review` markup, and no testimonial section. Cultural events rely on social proof; a "Stimmen" block with 3–5 participant quotes (with `Review` schema only if genuinely collected) would help both conversion and local trust.

## Finding 6 — Event location is a third-party venue — **Info**

Events happen at Goldbekhaus, not at the organisation's registered address. Keep the `Organization` address as the registered office and the `Event.location` as the venue — do not merge them. If the festival runs at multiple venues, list each in `Event.location` per event (already correct).

## Local checklist

| Item | Status |
|---|---|
| NAP consistent | ✅ |
| LocalBusiness/NGO schema | ❌ |
| City in title/H1 | ✅ homepage, ❌ service pages |
| Location landing pages | ❌ |
| Google Business Profile linked | ❌ |
| Map / directions | ❌ |
| Reviews / testimonials | ❌ |
| `areaServed` in schema | ❌ |
| Event venue address | ✅ |
| Local citations (YellowMap, Bing Places, etc.) | Not checked — no access |
