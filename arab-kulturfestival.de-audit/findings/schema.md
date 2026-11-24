# Structured Data Findings — arab-kulturfestival.de

**Pages with JSON-LD:** 91/91 (100%)
**Parse errors:** 0
**Format:** single `@graph` per page (Rank Math)

## Coverage

| Schema type | Pages | Notes |
|---|---|---|
| `Organization` | 91 | Global, includes address, phone, email, founder, `sameAs` |
| `WebSite` | 91 | Global, `inLanguage: de` |
| `BreadcrumbList` | 86 | Present on all non-home pages |
| `Article` | 20 | All blog posts |
| `Event` | 19 | All event pages |
| `WebPage` | 0 (standalone) | Only referenced via `mainEntityOfPage` |
| `LocalBusiness` / `NGO` | 0 | **Missing** |
| `Service` | 0 | **Missing** — 12 service pages have no Service markup |
| `FAQPage` | 0 | Missing |
| `Person` | 0 | Author is the Organization, not a human |

---

## Finding 1 — No `LocalBusiness` or `NGO` markup on a physical cultural venue — **High**

The site is a Hamburg-based cultural organisation with a real address, phone, and opening events. `Organization` alone does not feed the local knowledge panel or map pack.

**Add** (either `NGO` or `LocalBusiness` → `PerformingArtsTheater` / `CulturalCenter`):

```json
{
  "@type": ["NGO", "LocalBusiness"],
  "@id": "https://arab-kulturfestival.de/#organization",
  "name": "Arab Kulturfestival",
  "url": "https://arab-kulturfestival.de/",
  "logo": "https://arab-kulturfestival.de/wp-content/themes/yalla/assets/img/logo-mark.png",
  "image": "https://arab-kulturfestival.de/wp-content/uploads/2023/09/Logo-2.png",
  "email": "info@arab-kulturfestival.de",
  "telephone": "+49 178 1116220",
  "foundingDate": "2022",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Julius-Vosseler-Straße 106b",
    "postalCode": "22527",
    "addressLocality": "Hamburg",
    "addressCountry": "DE"
  },
  "areaServed": { "@type": "City", "name": "Hamburg" },
  "sameAs": [
    "https://www.instagram.com/arap.culture/",
    "https://www.facebook.com/Arap.cultur",
    "https://www.youtube.com/channel/UC8QljYPQaRs5YrBjsurD_3g"
  ]
}
```

Note the current `streetAddress` is `Julius-Vosseler-Straße` in the JSON-LD but the rendered page shows a mojibake variant in one fetch (`Stra�e`). Verify the UTF-8 encoding of the address string — a corrupted `ß` in structured data is a validation warning.

## Finding 2 — `Event` schema is missing `offers` — **High**

Every event has name, description, startDate, endDate, location, organizer, eventStatus, and attendance mode. Missing:

- `offers` (price, priceCurrency, availability, url) — **required for Event rich results**
- `performer` (the artists are named in titles, e.g. "أحمد أزرق وسلطنة")
- `isAccessibleForFree`
- `eventSchedule` for recurring courses
- `image` is a theme placeholder (`music.jpg`), not event-specific

```json
"offers": {
  "@type": "Offer",
  "price": "0",
  "priceCurrency": "EUR",
  "availability": "https://schema.org/InStock",
  "url": "https://arab-kulturfestival.de/event/dabke-kurs-hamburg-2026-10-08/",
  "validFrom": "2026-09-01T00:00:00+02:00"
}
```

Without `offers`, Google will not show the event in the Events rich result even though the markup is otherwise valid.

## Finding 3 — `Article` author is the Organization — **Medium**

```json
"author": { "@id": "https://arab-kulturfestival.de/#organization" }
```

Google's E-E-A-T guidance for article content expects a named person with a bio. For a cultural publication, attribute posts to a real author:

```json
"author": {
  "@type": "Person",
  "name": "Ahmad AL Zaher",
  "url": "https://arab-kulturfestival.de/ueber-uns/",
  "sameAs": ["https://www.instagram.com/arap.culture/"]
}
```

Add a visible byline on the article template to match.

## Finding 4 — No `Service` schema on the 12 `/angebot/` pages — **Medium**

These pages describe bookable cultural services (music, calligraphy, dabke, storytelling). `Service` markup with `provider`, `areaServed`, `serviceType`, and `offers` would make them eligible for service rich results and clarify the entity relationship to the Organization.

## Finding 5 — No `FAQPage` markup — **Medium**

The site answers implicit questions (registration, price, location, language) in body copy but never marks them up. Adding 3–5 Q&As per service page and per event series is the highest-leverage schema addition after `offers`.

## Finding 6 — `WebSite` lacks `potentialAction` (SearchAction) — **Low**

If site search exists (`/?s=`), add:

```json
"potentialAction": {
  "@type": "SearchAction",
  "target": "https://arab-kulturfestival.de/?s={search_term_string}",
  "query-input": "required name=search_term_string"
}
```

## Validation

All pages parse as valid JSON. Recommend running each template through the Rich Results Test after changes:

- Home → Organization/NGO + WebSite
- Event → Event with offers
- Article → Article with Person author
- Service → Service + FAQPage
- Category → remove BreadcrumbList if archives are noindexed
