# Orientalischer Tanz â€“ OTW Ensemble (Katrin Welten)

Recurring course, published 2026-11-12 as **1 Angebot page + dated Termine**, in three languages.
No information was changed: all texts are Katrin's original wording, only translated.

## Angebot (yd_service, published)

| Lang | ID | URL |
| --- | --- | --- |
| DE | 3163 | https://arab-kulturfestival.de/angebot/orientalischer-tanz-otw-ensemble/ |
| EN | 3164 | https://arab-kulturfestival.de/angebot/oriental-dance-otw-ensemble/ |
| AR | 3165 | https://arab-kulturfestival.de/angebot/Ø§Ù„Ø±Ù‚Øµ-Ø§Ù„Ø´Ø±Ù‚ÙŠ-ÙØ±Ù‚Ø©-otw/ |

Featured image: attachment **3161** (OTW Ensemble photo, alt text set).

## Termine (yd_event, published)

| Date | Lang | ID | URL |
| --- | --- | --- | --- |
| 2026-11-23 | DE | 3166 | /event/orientalischer-tanz-2026-11-23/ |
| 2026-11-23 | EN | 3167 | /event/oriental-dance-2026-11-23/ |
| 2026-11-23 | AR | 3168 | /event/Ø§Ù„Ø±Ù‚Øµ-Ø§Ù„Ø´Ø±Ù‚ÙŠ-2026-11-23/ |
| 2026-12-14 | DE | 3169 | /event/orientalischer-tanz-2026-12-14/ |
| 2026-12-14 | EN | 3170 | /event/oriental-dance-2026-12-14/ |
| 2026-12-14 | AR | 3171 | /event/Ø§Ù„Ø±Ù‚Øµ-Ø§Ù„Ø´Ø±Ù‚ÙŠ-2026-12-14/ |

Dates = 2nd and 4th Monday of the month (recurring course, further dates to be added).

### Event meta (identical for all six)

- `_yd_date` 2026-11-23 / 2026-12-14
- `_yd_time` 19:30, `_yd_end_time` 21:30
- `_yd_venue` Kulturladen St. Georg, `_yd_city` Hamburg
- `_yd_address` AlexanderstraÃŸe 16, 20099 Hamburg
- `_yd_price` DE `15 â‚¬ Â· Mitgliedsbeitrag 48 â‚¬ / Quartal` Â· EN `â‚¬15 Â· Membership â‚¬48 / quarter` Â· AR `15 ÙŠÙˆØ±Ùˆ Â· Ø§Ù„Ø¹Ø¶ÙˆÙŠØ© 48 ÙŠÙˆØ±Ùˆ / Ø±Ø¨Ø¹ Ø³Ù†Ø©`
- `_yd_cta` `mailto:orienttanzwelten@gmail.com`
- `_yd_cta_label` DE `Anmelden` Â· EN `Register` Â· AR `Ø§Ù„ØªØ³Ø¬ÙŠÙ„`
- `_yd_status` upcoming
- `_yd_lang` 71 (DE) / 72 (EN) / 73 (AR)
- `featured_media` 3161

## Verification (2026-11-12)

- All 9 URLs: HTTP 200, correct `lang` attribute (de/en/ar), no PHP errors.
- Events: Event + Offer + BreadcrumbList schema; Service pages: Service + BreadcrumbList schema.
- Listings: `/events/`, `/en/events/`, `/ar/events/`, `/programm/` show the entries.
- `/yd_event-sitemap.xml` = 25 URLs, `/yd_service-sitemap.xml` = 15 URLs (was 12 â€” the sitemap cache only flushed after re-saving service 3163).
- Google Search Console: erledigt am 2026-11-12 — Property ist **URL-Präfix** `https://arab-kulturfestival.de/` (Domain-Property existiert nicht, `sc-domain:` → kein Zugriff). Alle 9 URLs per URL-Prüfung beantragt („تم طلب الفهرسة“), Sitemaps am 12.11. erneut eingereicht (Index + yd_service + yd_event), letzter Google-Leselauf 11.11., 88 erkannte URLs (12+19+20+37). Abdeckung laut Bericht vom 9.11.: 51 indexiert, 4 nicht indexiert (nur Feeds + Alt-URL `/1554/`).

## Poster in der Termin-Karte (2026-11-12)

Request: the event poster (= featured image) must appear inside the sidebar card of the single event page, below "Preis / Hinweis".

Implemented in the live theme (via Appearance â†’ Theme File Editor, not via zip upload):

- `single-yd_event.php`: after the `soldout` tag, before `</aside>`:
  `<?php if ( has_post_thumbnail() ) : ?><figure class="event-poster"><?php the_post_thumbnail( 'large', array( 'class' => 'event-poster__img', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(min-width: 900px) 340px, 100vw' ) ); ?></figure><?php endif; ?>`
- `assets/css/theme.css`: `.event-poster` (border, radius, overflow hidden) + `.event-poster__img` (block, 100% width).
- `functions.php`: `YALLA_VERSION` 1.6.1 â†’ **1.6.2** (cache-busting for the CSS).

Verified live: DE/EN/AR event pages render `aside.card figure.event-poster > img`; Dabke events (no featured image) render nothing, no PHP errors.

Note: attachment 3161 is only 280Ã—210 px, so the poster is upscaled in the wide sidebar â€” a higher-resolution file would look sharper.

âš ï¸ The local copy under `D:\oben code\wordpress\wp-content\themes\yalla` is **stale** (1.2.7) compared to the live theme (1.6.1 â†’ now 1.6.2). All recent theme changes were made directly on the server through the Theme File Editor.

## Dabke-Poster (2026-11-12)

Final, correct file: `C:\Users\ahmad\Downloads\dabka_04 copy.webp` (2000Ã—2000, WebP) â€” the same artwork as `D:\Dabka\instgram\9\dabka_04.jpg` but **without** the Instagram â€ž4/4â€œ marker.

- Uploaded as attachment **3202** â†’ `/wp-content/uploads/2026/11/dabka_04-copy.webp`
  - Title: â€žDabke Tanzen â€” Poster (kostenloser Kurs in Hamburg)â€œ
  - Alt: â€žPlakat â€šTanz mit uns in Hamburgâ€˜ â€” Tanzende Gruppe in Tracht beim Dabke, kostenloser Kurs im Goldbekhausâ€œ
  - Caption: â€žDabke im Goldbekhaus â€” jeden zweiten Donnerstag.â€œ
- Set as `featured_media` on **all 18 Dabke events** (6 dates Ã— 3 languages):
  2618, 2615, 2612, 2608, 2604, 2601, 2598, 2594, 2591, 2588, 2585, 2582, 2579, 2576, 2573, 2570, 2567, 2564.
- Verified live: all 18 DE/EN/AR Dabke event pages return 200, render `aside.card figure.event-poster img` with `dabka_04-copy-1024x1024.webp`, no PHP errors, 0 failures.
- Caveat: the poster artwork is German only; it is also shown on the EN and AR event pages.

### Superseded (history)

- Attempt 1: `C:\Users\ahmad\Downloads\Dabka_Dance_300dpi_upscaled.webp` (1680Ã—1260, WebP) â†’ attachment **3179**. That file was **not** the poster (rendered as the oriental-dance veil photo). Replaced and force-deleted 2026-11-12.
- Attempt 2: `D:\Dabka\instgram\9\dabka_04.jpg` (2000Ã—2000, JPEG) â†’ attachment **3199**. Correct artwork but carried the Instagram carousel marker â€ž4/4â€œ top-right. Replaced by 3202 and force-deleted 2026-11-12.
## Team-Seite „Dossier“ (2026-11-12, live)

Design D aus `preview/team-design-d-dossier.html` / `team-design-d-ar.html` umgesetzt.

- `page.php`: Team-Block (`about`-Gruppe) ersetzt — `.team-dossier` > `article.dossier` mit `aside.dossier__rail` (Nummer, Foto, Rolle, Name, optional Ensemble) und `div.dossier__read` mit dem **vollständigen** `post_content` (kein Kürzen, kein Aufklappen).
- `assets/css/theme.css`: Regeln `.team-dossier`, `.dossier`, `.dossier__rail`, `.dossier__num`, `.dossier__shot`, `.dossier__role`, `.dossier__name`, `.dossier__ensemble`, `.dossier__read`, `.link-arrow`-Pillen, RTL-Spiegelung, Mobile-Breakpoints 880/560.
- `functions.php`: `YALLA_VERSION` 1.6.2 → **1.6.6**.
- Seitentext („Über uns", „Wer sind wir?", „Unsere Vision", „Die gemeinsame Sprache", Zitat, „Unser Team") bleibt unverändert — nur das Team-Grid wurde getauscht.
- Sonderfall Katrin Welten: die Zeile `-OrientTanzWelten-` wird per Regex aus dem ersten Absatz gelöst und als `.dossier__ensemble` unter dem Namen ausgegeben (Text bleibt erhalten, wird nur verschoben).
- Kein `position: sticky` für die linke Spalte: der Grid-Container ist kaum höher als die Spalte, dadurch hätte Sticky praktisch keinen Spielraum (getestet, verworfen).

Verifiziert live: /ueber-uns/ 3 Personen, /en/about/ 2, /ar/about/ 2 — alle 200, kein PHP-Fehler, Intro vorhanden, CSS `?ver=1.6.6`.

### Katrin Welten in EN + AR (2026-11-12, live)

- EN `yd_team` **3207** „Katrin Welten" (term 72), AR **3208** „كاترين ويلتن" (term 73). Beide: `featured_media` 3158, `menu_order` 3, Status publish, Inhalt 1:1 die übersetzten vier Absätze + dieselben drei Link-Pillen (Instagram/Facebook/Blog, identische URLs).
- Rolle (`_yd_role`) ist **nicht** über REST schreibbar (`meta` fehlt in `context=edit`; REST ignoriert das Feld). Gelöst über den Meta-Box-Save des Block-Editors: FormData aus `form.metabox-base-form` + `form.metabox-location-normal` (enthält `yd_meta_nonce`) an `/wp-admin/post.php` posten, dabei `_yd_role` setzen **und** `post_title`, `content`, `post_status`, `menu_order`, `_thumbnail_id` mitschicken (sonst würde `edit_post()` die Felder leeren). Antwort ist die Redirect-Seite (Editor) — Erfolg erst im Formular/REST prüfen.
- Rollen: EN „Dancer, Dance Teacher, Occupational Therapist", AR „راقصة، ومدرّبة رقص، وأخصائية علاج وظيفي".
- Verifiziert live: alle drei Sprachen zeigen **3** Dossiers (Nummern 01/02/03), Rolle + vollständiger Text + `-OrientTanzWelten-` in der Rail, AR `dir="rtl"` korrekt gespiegelt, 200, kein PHP-Fehler, CSS `?ver=1.6.6`.

### Mobile-Fix Rail (2026-11-12, live, 1.6.7)

Problem: auf ≤880px spanned Foto und Nummer zwei Grid-Zeilen; das größere Foto verteilt die Höhe auf beide `auto`-Zeilen, dadurch rutschte die Rolle nach oben und der Name nach unten (große Lücke), Foto nur 84px breit.

- `page.php`: Rolle/Name/Ensemble in `.dossier__meta` gepackt (einzige Zelle neben dem Foto) — kein Zeilen-Span mehr.
- `theme.css`: `.dossier__meta` (flex column, `min-width: 0`); Breakpoint 880 → Raster `auto 150px minmax(0,1fr)`, `align-items: center`, kein `grid-row`-Span, Rolle `margin: 0 0 .3rem`; Breakpoint 560 → `minmax(118px, 38%) minmax(0,1fr)`, Nummer aus, Name 1.34rem.
- Foto mobil 84px → ca. 38% der Rail-Breite (≈127px bei 375px, ≈178px bei 470px).
- `YALLA_VERSION` 1.6.6 → **1.6.7**, Cache gepurged.
- Verifiziert (500px / 760px / 1280px, DE + AR): Rolle und Name stehen eng beieinander, mittig zum Foto, Desktop unverändert, alle drei Sprachen 200 + 3 Dossiers, kein PHP-Fehler.
