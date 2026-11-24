# Yalla — Arab Kulturfestival Hamburg

Klassisches WordPress-Theme für das Arab Kulturfestival Hamburg. Dreisprachig
(Deutsch, Englisch, Arabisch mit RTL), eigenständig, ohne Page-Builder und ohne
Multilingual-Plugin. Gestaltung angelehnt an die visuelle Sprache von
yalladabka.de, Inhalte und Fakten vom Arab Kulturfestival.

- **Version:** 1.2.5
- **Textdomain:** `yalla`
- **Getestet mit:** WordPress 7.1.3, PHP 8.1+
- **Empfohlenes Plugin:** Rank Math SEO (Titel, Description, Canonical, Sitemaps)

---

## 1. Installation

1. Ordner `yalla` als ZIP packen und unter **Design → Themes → Theme hinzufügen →
   Theme hochladen** installieren, dann aktivieren.
   Alternativ den Ordner per FTP nach `wp-content/themes/yalla` kopieren.
2. **Einstellungen → Permalinks** einmal auf *Beitragsname* speichern
   (Rewrite-Regeln für `/event/…` und die Sprachpfade).
3. **Rank Math SEO** installieren und aktivieren. Das Theme erkennt das Plugin
   automatisch und übergibt ihm Titel, Description, Social-Tags, Canonical und
   Sitemaps. Ohne Rank Math gibt das Theme eigene Fallback-Tags aus.
4. Unter **Rank Math SEO → Sitemap Settings** prüfen, dass Seite, Beitrag und
   `yd_event` aktiv sind (die mitgelieferte Konfiguration setzt das bereits).
5. Startseite: `front-page.php` greift automatisch, es muss keine statische
   Seite zugewiesen werden.

Beim Aktivieren legt das Theme die Inhalte an (Seiten in drei Sprachen, vier
Termine, Artikel, Team, Partner, Formate). Das passiert nur einmal; ein zweiter
Lauf überschreibt keine Änderungen.

Der Import ist **wiederholbar**: Seiten werden über ihre Übersetzungsgruppe
erkannt, alle anderen Inhalte über Titel und Sprache. Gibt es auf der Website
schon eine Seite mit demselben Slug (etwa bei einem Relaunch), übernimmt das
Theme sie, statt eine zweite mit der Endung `-2` anzulegen.

---

## 2. Sprachen und URLs

| Sprache | Startseite | Beispiel Programm | Richtung |
| --- | --- | --- | --- |
| Deutsch | `/` | `/programm/` | LTR |
| English | `/en/` | `/en/programme/` | LTR |
| العربية | `/ar/` | `/ar/program/` | RTL |

- Jede Seite existiert dreimal, verknüpft über die Meta-Angabe `_yd_group`.
- Beiträge und Termine liegen in **allen** Sprachen unter derselben URL-Struktur
  (`/die-dabke-…/`, `/event/dabke-kurs-hamburg-2026-11-05/`), nicht unter `/en/`
  oder `/ar/`. Die Sprache steckt im Beitrag selbst (Taxonomie `yd_lang`) und in
  `hreflang`/`canonical`.
- `hreflang` (de, en, ar, x-default) und Canonical werden pro Übersetzung
  ausgegeben. Die Zuordnung kommt aus dem Theme, nicht aus Rank Math.
- Neue Seite in einer Sprache: Seite anlegen, Elternelement `en` bzw. `ar`
  wählen, damit die Sprache erkannt wird, und dieselbe `_yd_group` wie die
  anderen Übersetzungen setzen. Für Beiträge/Termine stattdessen die
  Sprach-Taxonomie setzen und `_yd_translation_de|en|ar` mit den IDs der
  Übersetzungen füllen.

---

## 3. Inhalte pflegen

### Seiten
**Seiten → Seite bearbeiten**. Der Inhalt ist normaler Gutenberg-/Classic-Content.
Die drei Sprachversionen sind separate Seiten.

### Termine (`Termine` im Backend)
Eigener Beitragstyp `yd_event` mit Feldern für Datum, Uhrzeit, Ende, Ort, Adresse
und Preis/Hinweis. Der Preis steht aktuell überall auf

- DE: `Eintritt frei · Spenden willkommen`
- EN: `Free entry · donations welcome`
- AR: `الدخول مجاني · التبرعات مرحّب بها`

**Ein Datum, drei Sprachen:** Unter **Termine → Schnell anlegen** trägst du
Datum, Uhrzeit und Ort einmal ein. Das Theme erzeugt daraus drei
veröffentlichte Termine (DE/EN/AR) mit passendem Titel, Text, Slug, Preis,
Übersetzungs-Verknüpfung und SEO-Feldern. Doppelte Datum+Uhrzeit-Kombinationen
werden gemeldet, aber nicht blockiert. Danach lässt sich jeder der drei Termine
einzeln nachbearbeiten.

Es gibt **keinen Teilnahmebeitrag**. Wenn sich das ändert, nur das Feld
*Preis / Hinweis* anpassen – das Schema (`Event`) liest Datum, Ort und
Beschreibung, nicht den Preis.

### Artikel, Team, Partner, Formate
Eigene Beitragstypen (`post`, `yd_team`, `yd_partner`, `yd_service`,
`yd_testimonial`). Team und Partner erscheinen auf „Über uns“, Formate auf
„Programm“.

### Schnell arbeiten (seit 1.2.3)
- **Termin-Liste:** Datum, Zeit, Preis und Sprache stehen als Spalten da; die
  Datumsspalte ist sortierbar, oben filtert ein Dropdown nach Sprache.
- **Duplizieren:** In der Termin-Liste unter jedem Eintrag. Erzeugt einen
  Entwurf mit allen Feldern und demselben Datum — Datum ändern, fertig.
- **Slug automatisch:** Neue Termine bekommen den Link
  `dabke-kurs-hamburg-JJJJ-MM-TT` (EN `dabke-class-…`, AR `dabke-hamburg-…`).
  Ändert sich das Datum, wandert der Slug mit. Ein selbst geschriebener Slug
  bleibt unangetastet.
- **Übersetzungen:** Rechts in der Seitenleiste steht, welche Sprachversionen
  verknüpft sind. Ein Klick springt direkt in die andere Sprache.
- **Vorbelegte Felder:** Neuer Termin = 18:15–19:45, Goldbekhaus, Moorfuhrtweg 9,
  „Eintritt frei · Spenden willkommen“. Nur Datum eintragen und veröffentlichen.
- **Dashboard:** Widget „Yalla – Schnellzugriff“ mit den nächsten vier Terminen
  und Links zu Termine, Seiten, Artikel, Menü und Kontakt.
- **Hilfe:** Oben rechts im Bearbeiten-Screen unter „Hilfe“ steht die
  Kurzanleitung; bei vergangenen, noch veröffentlichten Terminen erscheint ein
  Hinweis.

### Name, Kontakt, NAP
**Design → Customizer → Festival-Identität → Name & Kontakt.** Diese Felder
speisen Footer, Kontaktseite und das Organization-Schema (Name, E-Mail, Telefon,
Straße, PLZ, Stadt). Für konsistente NAP-Angaben bitte nur hier ändern und die
Impressums-Seite separat nachziehen.

### Menüs
**Design → Menüs**: Kopfmenü (`primary`) und Fußmenü (`footer`). Das Theme legt
beim Aktivieren ein passendes Kopfmenü an.

---

## 4. YouTube-Einwilligung

- Der Dialog ist **immer deutsch** und **immer LTR**, auch auf `/en/` und `/ar/`.
  Nur der Seiteninhalt und die SEO-Texte sind übersetzt.
- Er erscheint beim ersten Besuch automatisch. Vor der Einwilligung werden
  **keine** YouTube-Anfragen gesendet: keine iFrames, keine Thumbnails, kein
  Player. Platzhalter sind reine Theme-Grafiken.
- Entscheidung: `localStorage["yd_consent_youtube"]` = `granted` | `denied`.
  Schließen ohne Klick ist **keine** Einwilligung; ein Session-Flag verhindert,
  dass der Dialog bei jedem Seitenaufruf erneut aufspringt.
- Fußzeile: dauerhafter Link **„Datenschutzeinstellungen“** öffnet die Optionen
  wieder. „Ablehnen“ bzw. Widerruf entfernt bereits geladene Player.
- Tastatur und Screenreader: Fokusfalle im Dialog, `Escape` schließt, Fokus
  kehrt zum auslösenden Element zurück.
- Video: `https://www.youtube.com/watch?v=BV2Cdy1ieYc`, eingebunden über
  `youtube-nocookie.com` und erst nach Zustimmung.
- Kurzcode für weitere Videos: `[yd_video id="VIDEO_ID" title="Beschreibung"]`.
  YouTube-iFrames im Redaktionstext werden automatisch in Platzhalter umgebaut.

Zum Testen der Ersteinwilligung im Browser:

```js
localStorage.removeItem('yd_consent_youtube');
sessionStorage.removeItem('yd_consent_prompt_seen');
location.reload();
```

---

## 5. Rhythmus-Spiel

Sechs Dabke-Grundbewegungen, jede mit Nummer, Bild und Namen:

| # | DE | EN | AR | Bild |
| --- | --- | --- | --- | --- |
| 1 | Einzelschritt | Single step | خطوة مفردة | `move-1.jpg` |
| 2 | Doppelschritt | Double step | خطوة مزدوجة | `move-2.jpg` |
| 3 | Kreistanz | Circle | الدائرة | `move-3.jpg` |
| 4 | Hüpfschritt | Hop step | خطوة النطّ | `move-4.jpg` |
| 5 | Stampfen | Stomp | الدقّ بالأرض | `move-5.jpg` |
| 6 | Yalla-Sprung | Yalla jump | قفزة يلا | `move-6.jpg` |

Bilder liegen in `assets/img/moves/`. Zum Austauschen dieselben Dateinamen
verwenden (Seitenverhältnis 16:9, mindestens 960 × 540 px). Namen und Alt-Texte
stehen in `inc/i18n/de.php`, `en.php`, `ar.php` unter `move_1` … `move_6` und
`move_N_alt`. Die Schlagzeug-Sounds erzeugt der Browser per Web Audio API, es
gibt keine Audiodateien.

---

## 6. SEO

Aufteilung:

| Zuständig | Was |
| --- | --- |
| Rank Math | `<title>`, Meta-Description, Open Graph, Twitter, Canonical, Robots, XML-Sitemaps |
| Theme | hreflang-Cluster, JSON-LD (Organization, WebSite, BreadcrumbList, Article, Event) |

Rank Maths eigenes JSON-LD ist über `inc/rank-math.php` deaktiviert, damit kein
Schema doppelt im Quelltext steht. Die vollständige Tabelle mit Fokus-Keyword,
SEO-Titel, Description und Schema pro Seite und Sprache liegt in
[`docs/seo.md`](docs/seo.md).

Sitemaps: `/sitemap_index.xml` → `page-sitemap.xml` (33), `post-sitemap.xml` (12),
`yd_event-sitemap.xml` (12). Suche und 404 sind auf `noindex` gesetzt,
Kategorien und Schlagwörter ebenfalls.

Nach Redaktionsänderungen die Sitemap neu erzeugen:
**Rank Math SEO → Sitemap Settings → Regenerate** oder
`wp rank-math sitemap generate`.

Es gibt keine Garantie auf Ranking 1 – die Konfiguration ist die technische
Grundlage, kein Versprechen.

---

## 7. Vor dem Livegang prüfen

- [x] **Termine:** 05.11.2026, 19.11.2026, 03.12.2026, 17.12.2026 sind
      bestätigt (Datum, Uhrzeit 18:15–19:45, Ort Goldbekhaus).
- [ ] **Preis:** bleibt „Eintritt frei“, Spenden werden im Text genannt. Kein
      Teilnahmebeitrag auf keiner Seite (Stand 1.2.2: nirgends ein Preis).
- [ ] **Presse-Links** auf der Presseseite gegenprüfen (extern, öffnen in neuem Tab).
- [ ] **PDF-Programmhefte** (`assets/pdf/programm-2022.pdf`, `programm-2024.pdf`)
      auf Aktualität und Rechte prüfen.
- [ ] **Kontaktformular:** nutzt `wp_mail()`. Auf dem Live-Server SMTP
      konfigurieren, sonst landen Mails im Spam oder gar nicht.
- [x] **Schriftarten** sind lokal eingebunden (`assets/fonts/`, SIL OFL 1.1).
      Beim Aktualisieren `fonts/OFL.txt` mitliefern; keine externen Font-Requests.
- [ ] **Datenschutz, Impressum, Cookies:** Texte fachlich prüfen lassen
      (§ 5 DDG, DSGVO, YouTube-Einwilligung).
- [ ] **Social-Links** in `inc/template-tags.php` (`yd_socials()`) prüfen.
- [ ] **Bildrechte:** alle Fotos müssen für die Website freigegeben sein.
- [ ] **Backups** und automatische Updates einrichten.
- [ ] In der **Search Console** die Domain-Property anlegen und
      `https://domain.tld/sitemap_index.xml` einreichen.

### Doppelte Inhalte (nach einem Relaunch)

Wenn Inhalte auf der Website zweimal erscheinen — etwa jeder Partner oder jedes
Teammitglied doppelt, oder Seiten mit `-2` in der URL — hat eine frühere
Theme-Version den Import mehrfach ausgeführt. Seit **1.2.5** passiert das nicht
mehr.

1. **Werkzeuge → Yalla Duplikate** öffnen.
2. Die Liste zeigt pro Gruppe, welcher Eintrag bleibt (der älteste) und welche
   in den Papierkorb wandern. Die Häkchen sind gesetzt; ein Klick genügt.
3. Seiten mit `-2` werden nur **angezeigt**, nicht gelöscht — dort entscheidet
   das Menü, welche Kopie gilt.
4. Papierkorb leeren, wenn die Website wieder stimmt.

Bei Bedarf vorher ein Backup ziehen: die Einträge landen zuerst im Papierkorb.

---

## 8. Rechte und Herkunft der Inhalte

Texte, Fakten, Logo und Fotos stammen vom Arab Kulturfestival
(arab-kulturfestival.de) und von Arap Culture. Die Gestaltung orientiert sich an
yalladabka.de. **Vor der Veröffentlichung muss geklärt sein, dass alle Texte,
Bilder, Logos und PDFs für diese Website verwendet werden dürfen.** Das Theme
selbst ist GPL-konform lizenziert; die Inhalte sind es nicht automatisch.

---

## 9. Dateistruktur

```
yalla/
├── functions.php            Bootstrap, Version, Includes
├── style.css                Theme-Header (Design liegt in assets/css)
├── front-page.php           Startseite
├── page.php                 Standardseite
├── single.php               Artikel
├── single-yd_event.php      Termin-Detailseite
├── archive.php, search.php, 404.php, index.php
├── header.php, footer.php   Kopf/Fuß inkl. Consent-Dialog und Video-Modal
├── inc/
│   ├── setup.php            Assets, Menüs, Bildgrößen
│   ├── i18n.php             Sprachen, Übersetzungs-Mapping, Rhythmus-Daten
│   ├── i18n/{de,en,ar}.php  Alle UI-Texte
│   ├── content-types.php    CPTs, Taxonomie `yd_lang`, Sprach-Query
│   ├── meta-boxes.php       Felder für Termine und Formate
│   ├── customizer.php       Festival-Identität, NAP, Bilder
│   ├── seo.php              hreflang, JSON-LD
│   ├── rank-math.php        Rank-Math-Integration
│   ├── consent.php          YouTube-Einwilligung (Markup, Filter, Shortcode)
│   ├── forms.php            Kontaktformular (wp_mail)
│   ├── admin-ux.php         Termin-Liste, Duplizieren, Übersetzungen, Hilfe
│   ├── quick-event.php      Ein Datum → Termin in DE/EN/AR
│   ├── duplicates.php       Werkzeuge → Yalla Duplikate (Doppelungen finden)
│   ├── seed.php             Erstinstallation der Inhalte
│   ├── demo-pages.php, demo-items.php, demo-articles.php
│   └── template-tags.php    Hilfsfunktionen (yd_t, yd_link, yd_image, …)
├── template-parts/          Hero, Programm, Termine, Spiel, Presse, …
└── assets/
    ├── css/theme.css        Designsystem
    ├── css/consent.css      Consent-Dialog
    ├── css/fonts.css        Lokale @font-face-Regeln
    ├── fonts/               26 woff2-Dateien + OFL.txt (SIL OFL 1.1)
    ├── js/main.js           Navigation, Video-Modal, Rhythmus-Spiel
    ├── js/consent.js        Einwilligung
    ├── img/moves/           Sechs Bewegungen des Spiels
    └── pdf/                 Programmhefte
```

Kein Build-Schritt: CSS und JS werden unkompiliert ausgeliefert. Die
Asset-Version hängt an `YALLA_VERSION` in `functions.php` – bei Änderungen an
CSS/JS hochzählen, damit Browser nicht die alte Datei aus dem Cache ziehen.

---

## 10. Changelog

### 1.2.5
- Import ist wiederholbar: Team, Partner, Formate, Stimmen und Artikel werden
  über Titel und Sprache erkannt und nicht erneut angelegt; Termine zusätzlich
  über Datum und Uhrzeit.
- Seiten mit bereits vorhandenem Slug werden übernommen, statt eine zweite
  Kopie mit `-2` anzulegen (Relaunch-Fall).
- Neu: **Werkzeuge → Yalla Duplikate** findet doppelte Inhalte, behält den
  ältesten Eintrag und verschiebt den Rest in den Papierkorb. Doppelte Seiten
  werden nur gemeldet.
- Der Import-Report zählt nur noch wirklich neue Seiten.

### 1.2.4
- Neu: **Termine → Schnell anlegen** legt einen Termin mit einem Formular in
  allen drei Sprachen an (Titel, Text, Slug, Preis, Übersetzungs-Links und
  Rank-Math-Felder inklusive). Duplikat-Prüfung für Datum + Uhrzeit.
- Hinweis auf dem „Neuer Termin“-Screen verweist auf das Formular.
- Termin-Texte liegen jetzt in `yd_event_copy()` und werden von Seed und
  Formular gemeinsam genutzt.

### 1.2.3
- Bearbeiten vereinfacht: Datum/Zeit/Preis/Sprache als Spalten in der
  Termin-Liste (sortierbar), Sprachfilter, „Duplizieren“-Aktion, automatische
  Termin-Slugs aus Datum und Sprache, Übersetzungs-Box in der Seitenleiste,
  vorbelegte Terminfelder, Dashboard-Widget, Hilfe-Tab und Hinweis bei
  vergangenen Terminen.
- Zeitspalten sind echte Zeitfelder (`<input type="time">`).
- Sprachspalte in Listen nicht mehr doppelt (Taxonomie-Spalte wird genutzt).

### 1.2.2
- Schriftarten lokal eingebunden: 26 woff2-Dateien in `assets/fonts/`
  (Fraunces, Work Sans, Barlow Condensed, Cairo, Amiri; Subsets latin,
  latin-ext, arabic) plus `assets/css/fonts.css`. Keine Requests mehr an
  `fonts.googleapis.com` oder `fonts.gstatic.com`, Preconnect entfernt.
  Lizenz: SIL OFL 1.1 in `assets/fonts/OFL.txt`.
- Termine 05.11., 19.11., 03.12., 17.12.2026 als bestätigt markiert.

### 1.2.1
- Fix: Skip-Link verursachte in RTL-Ansichten eine riesige horizontale
  Scrollbreite (Screenshot-/Layoutfehler in WebKit). Jetzt per `transform`
  versteckt, Fokus zeigt ihn weiterhin an.

### 1.2.0
- SEO: Rank-Math-Integration, hreflang für alle Übersetzungen, JSON-LD-Graph
  (Organization, WebSite, BreadcrumbList, Article, Event), XML-Sitemaps,
  Noindex für Suche/404/Kategorien.
- Termine: saubere ASCII-Slugs, Zeitangaben mit Zeitzone im Schema,
  Adresse im Schema korrekt aufgeteilt (Straße, PLZ, Stadt).
- NAP im Customizer editierbar.
- Rhythmus-Spiel auf sechs Bewegungen mit Bild, Nummer und Namen erweitert.
- Teilnahme an allen Kursen kostenfrei, Spenden willkommen (DE/EN/AR).

### 1.1.x
- YouTube-Einwilligungsdialog (deutsch, LTR, blockiert alle YouTube-Anfragen).
- Trilinguale Inhalte, Presseseite, Cookie-/Datenschutzseiten,
  Datenschutzeinstellungen in der Fußzeile.

---

## نظرة سريعة (العربية)

قالب ووردبريس ثلاثي اللغة (ألماني في الجذر، إنجليزي تحت `/en/`، عربي تحت `/ar/`
مع دعم RTL) لموقع مهرجان الثقافة العربية في هامبورغ.

- **المشاركة مجانية** في جميع الدورات، ولا يوجد أي سعر على الموقع.
  التبرعات مرحّب بها، وهي مذكورة في صفحة البرنامج وصفحة المواعيد وصفحة كل موعد.
- نافذة موافقة يوتيوب تظهر **بالألمانية فقط** وفي اتجاه LTR في كل اللغات،
  ولا يتم تحميل أي شيء من يوتيوب قبل الموافقة.
- بيانات SEO (العنوان والوصف والكلمة المفتاحية) لكل صفحة ولكل لغة موجودة في
  إضافة Rank Math، والجدول الكامل في `docs/seo.md`.
- **المواعيد مؤكدة**: 05.11 / 19.11 / 03.12 / 17.12.2026.
- الخطوط مستضافة **محلياً** داخل القالب (`assets/fonts/`، رخصة OFL)، ولا
  يُرسل المتصفح أي طلب إلى Google Fonts.
- قبل النشر: مراجعة روابط الصحافة، تفعيل SMTP للبريد، ومراجعة نصوص
  الخصوصية والطباعة.
