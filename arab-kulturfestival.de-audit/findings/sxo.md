# Search Experience (SXO) Findings — arab-kulturfestival.de

**Method:** SERP page-type inference from query intent + on-site journey mapping. Live SERP data was not pulled (no DataForSEO/SerpApi credentials configured).

---

## Query → page-type map

| Query cluster | What Google typically ranks | What the site offers | Match |
|---|---|---|---|
| `Arabisches Kulturfestival Hamburg` | Homepage / event hub | Homepage ✅ | ✅ Strong |
| `Dabke Kurs Hamburg` | Local service page + event listings | `/event/dabke-kurs-*` (dated instances) + thin `/angebot/` | ⚠️ Partial |
| `Dabke lernen Hamburg` | How-to / class page | None | ❌ Missing |
| `Arabische Musik Hamburg` | Event / concert listing | `/angebot/arabische-musik/` (323 words, no dates) | ⚠️ Partial |
| `Kalligrafie Workshop Hamburg` | Local class page | `/angebot/kalligrafie/` (309 words) | ⚠️ Partial |
| `Arabisches Festival 2026 Hamburg` | Event hub with dates | `/programm/`, `/events/` | ✅ Good |
| `Was ist Dabke?` | Informational article | `/die-dabke-braucht-keine-einladung/` | ✅ Good |

## Finding 1 — Service intent lands on dated event instances — **High**

Someone searching `Dabke Kurs Hamburg` wants a class they can attend, not a specific 8 October instance. The site's durable answer (`/angebot/folkloretanz-dabke/`, 345 words) is thin and orphaned, while the rankable dated pages (`/event/dabke-kurs-hamburg-2026-*`) expire.

**Fix:** make `/angebot/folkloretanz-dabke/` the hub — schedule table of all upcoming dates, what to expect, price, venue, FAQ — and link every event instance to it and back. The event pages then feed the hub rather than competing with it.

## Finding 2 — No conversion path on event pages — **High**

Event pages have date, time, venue, and description, but:

- no price or "Eintritt frei" statement in the first screen
- no registration/booking button (even "Keine Anmeldung nötig" is buried)
- no "Zum Kalender hinzufügen" visible CTA (the ICS endpoint exists at `/wp-admin/admin-post.php?action=yd_event_ics&event_id=…` but is not surfaced)
- no map or transit info

A user arriving from search has to scroll to confirm the event is free and open. That is a bounce risk on mobile.

**Fix:** above the fold on every event page — date, time, venue, price, and one primary CTA (`Zum Kalender hinzufügen` / `Route planen`).

## Finding 3 — The homepage H1 is poetic, not query-matched — **Medium**

```
H1: "Wo arabische Kultur in Bewegung kommt"
Title: "Arabisches Kulturfestival Hamburg - Termine & Dabke"
```

The title is excellent. The H1 contains no entity ("Arabisches Kulturfestival") and no city. A first-time visitor from search cannot confirm in one glance that they are on the right page.

**Fix:** keep the poetic line as a subheading and use:

```
H1: Arabisches Kulturfestival Hamburg
Sub: Wo arabische Kultur in Bewegung kommt.
```

## Finding 4 — No FAQ blocks — **Medium**

The site answers real questions in prose but never in Q→A form, so it cannot win People Also Ask or AI Overview citations.

High-value questions already answered somewhere on the site:

- Muss ich mich für den Dabke-Kurs anmelden? (no)
- Kostet das Festival Eintritt? (free)
- Wo findet es statt? (Goldbekhaus, Moorfuhrtweg 9)
- In welcher Sprache wird unterrichtet? (DE/AR)
- Für wen ist Dabke geeignet? (all levels)

**Fix:** add a 5-question FAQ block to `/angebot/folkloretanz-dabke/`, `/programm/`, and the homepage, with `FAQPage` schema.

## Finding 5 — Mobile journey gaps — **Medium** (inferred, not rendered)

Without Playwright, the rendered mobile view could not be captured. From the HTML:

- 61 images on the homepage → long scroll before the programme is reachable.
- Navigation has 8 top-level items plus a 3-language switcher — tight on a 360 px viewport.
- Gallery carousel with 45 slides is a heavy mobile interaction.

**Recommendation:** capture mobile screenshots (Playwright) before changing layout, then prioritise: programme above the gallery, language switcher in the footer on mobile, gallery capped at 12 slides.

## Finding 6 — Persona fit — **Medium**

| Persona | Need | Current experience |
|---|---|---|
| Arabic-speaking resident in Hamburg | Find community events in Arabic | `/ar/` exists, but event translations are unlinked and Arabic font falls back |
| German local curious about Arab culture | Understand what/where/when | Strong homepage, but programme requires scrolling past a 45-image gallery |
| Visitor from outside Hamburg | Plan a trip around a date | Event pages lack map/transit and a calendar CTA |
| Journalist / partner | Press assets and contact | `/presse/` exists; no press kit download detected |

## Priority SXO actions

1. Build a proper Dabke hub page with all dates (fixes the biggest intent mismatch).
2. Add price + CTA above the fold on event pages.
3. Add the entity to the H1.
4. Add FAQ blocks with schema.
5. Fix Arabic font and event hreflang for the Arabic-speaking persona.
