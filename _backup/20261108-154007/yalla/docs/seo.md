# SEO-Konfiguration / SEO configuration / إعدادات SEO

Alle Titel, Beschreibungen und Fokus-Keywords liegen in Rank Math und sind pro Seite und pro Sprache einzeln gepflegt. Diese Tabelle ist die Quelle der Wahrheit für die Redaktion: sie beschreibt, was aktuell ausgeliefert wird.

All titles, descriptions and focus keywords live in Rank Math and are maintained per page and per language. This table is the editorial reference for what the site currently outputs.

جميع العناوين والأوصاف والكلمات المفتاحية محفوظة في إضافة Rank Math وتُدار لكل صفحة ولكل لغة على حدة. هذا الجدول مرجع التحرير لما يُخرجه الموقع حالياً.

## Aufteilung / Division of labour

| Zuständig | Was |
| --- | --- |
| Rank Math | `<title>`, Meta-Description, Open Graph, Twitter Cards, Canonical, Robots-Meta, XML-Sitemaps |
| Theme (`inc/seo.php`) | hreflang-Cluster (DE/EN/AR + x-default), JSON-LD-Graph (Organization, WebSite, BreadcrumbList, Article, Event) |

Rank Maths eigenes JSON-LD ist über `inc/rank-math.php` abgeschaltet (`rank_math/json_ld`), damit kein Schema doppelt erscheint. Die Sitemap-Konfiguration liegt in Rank Math, nicht im Theme.

## Organization / NAP (alle Sprachen)

| Feld | Wert | Pflege |
| --- | --- | --- |
| name | Arab Kulturfestival | Customizer → Festival-Identität → Name & Kontakt |
| alternateName | Arap Culture | Theme (`inc/seo.php`) |
| email | info@arab-kulturfestival.de | Customizer → Name & Kontakt |
| telephone | +49 178 1116220 | Customizer → Name & Kontakt |
| streetAddress | Julius-Vosseler-Straße 106b | Customizer → Name & Kontakt |
| postalCode | 22527 | Customizer → Name & Kontakt |
| addressLocality | Hamburg | Customizer → Name & Kontakt |
| addressCountry | DE | Theme (`inc/seo.php`) |
| founder | Ahmad AL Zaher | Theme (`inc/seo.php`) |
| sameAs | Instagram, Facebook, YouTube | `yd_socials()` in `inc/template-tags.php` |

## DE — Seiten / Pages

| Seite | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Startseite / Home | `/` | Arabisches Kulturfestival Hamburg | Arabisches Kulturfestival Hamburg – Termine & Dabke | Das Arab Kulturfestival bringt Dabke, arabische Musik, Kalligrafie und Literatur nach Hamburg. Alle Termine, das Programm und Infos zum Mitmachen. | Organization, WebSite, BreadcrumbList |
| Festival | `/festival/` | Interkulturelles Festival Hamburg | Festival in Hamburg: Arabische Kultur erleben | Musik, Dabke, Kalligrafie und Literatur: Das Arab Kulturfestival zeigt die Vielfalt arabischer Kultur in Hamburg – interkulturell und nahbar. | Organization, WebSite, BreadcrumbList |
| Programm / Programme | `/programm/` | Dabke Kurs Hamburg | Dabke Kurs Hamburg & Festivalprogramm | Dabke lernen in Hamburg: offene Kurse, Musik und Mitmachangebote. Hier findest du das Programm des Arab Kulturfestivals und alle Termine. | Organization, WebSite, BreadcrumbList |
| Termine / Events | `/termine/` | Arabische Veranstaltungen Hamburg | Arabische Veranstaltungen Hamburg: Termine 2026 | Alle Termine des Arab Kulturfestivals in Hamburg: Dabke-Kurse, Konzerte und Begegnungen. Datum, Uhrzeit und Ort auf einen Blick. | Organization, WebSite, BreadcrumbList |
| Artikel / Articles | `/artikel/` | Arabische Musik Hamburg | Arabische Musik & Kultur: Artikel und Hintergründe | Geschichten über Dabke, arabische Musik, Kalligrafie und Kultur im Exil – gelesen und geschrieben von und mit der Community in Hamburg. | Organization, WebSite, BreadcrumbList |
| Über uns / About us | `/ueber-uns/` | Arabische Kultur Hamburg | Arabische Kultur Hamburg: Über uns | Wer hinter dem Arab Kulturfestival steht: Ahmad AL Zaher und Arap Culture fördern arabische Kultur, Dabke und interkulturellen Austausch in Hamburg. | Organization, WebSite, BreadcrumbList |
| Kontakt / Contact | `/kontakt/` | Arab Kulturfestival Hamburg Kontakt | Kontakt – Arab Kulturfestival Hamburg | Fragen zu Terminen, Dabke-Kursen oder Presse? Schreib uns an info@arab-kulturfestival.de oder nutze das Kontaktformular. | Organization, WebSite, BreadcrumbList |
| Presse / Press | `/presse/` | Arab Kulturfestival Hamburg Presse | Presse – Arab Kulturfestival Hamburg | Pressematerial, Berichte und Ansprechpartner zum Arab Kulturfestival Hamburg: hier findest du Medienberichte und Kontakt für Redaktionen. | Organization, WebSite, BreadcrumbList |
| Datenschutz / Privacy | `/datenschutz/` | Datenschutz Arab Kulturfestival | Datenschutz – Arab Kulturfestival Hamburg | Wie wir personenbezogene Daten verarbeiten, welche Dienste wir nutzen und wie du deine YouTube-Einwilligung jederzeit ändern oder widerrufen kannst. | Organization, WebSite, BreadcrumbList |
| Impressum / Legal notice | `/impressum/` | Impressum Arab Kulturfestival | Impressum – Arab Kulturfestival Hamburg | Anbieterkennzeichnung nach § 5 DDG: Arab Kulturfestival, Julius-Vosseler-Straße 106b, 22527 Hamburg. Kontakt und Verantwortliche. | Organization, WebSite, BreadcrumbList |
| Cookies | `/cookies/` | Cookies Arab Kulturfestival | Cookies – Arab Kulturfestival Hamburg | Welche Cookies und lokalen Speicher wir verwenden: keine Analyse- oder Werbecookies, YouTube nur nach ausdrücklicher Einwilligung. | Organization, WebSite, BreadcrumbList |

## DE — Artikel / Articles

| Artikel | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Artikel: Dabke braucht keine Einladung | `/die-dabke-braucht-keine-einladung/` | Dabke Hamburg | Dabke in Hamburg: Warum der Tanz keine Einladung braucht | Dabke ist mehr als ein Tanz: Er entsteht im Kreis, ohne Bühne und ohne Einladung. Ein Blick auf Herkunft, Rhythmus und Gemeinschaft. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Kultur im Exil | `/arabische-kultur-im-exil/` | Arabische Kultur Hamburg | Arabische Kultur im Exil: Identität, die bleibt | Was bleibt, wenn die Heimat fehlt? Wie arabische Kultur im Exil weiterlebt – in Musik, Sprache, Essen und gemeinsamen Abenden. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Kalligrafie | `/kalligrafie/` | Arabische Kalligrafie Hamburg | Arabische Kalligrafie: Wenn Buchstaben Bilder werden | Arabische Kalligrafie verbindet Schrift und Bild. Wie Buchstaben zu Kunst werden und warum die Handschrift bis heute fasziniert. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Arabische Musik | `/arabische-musik/` | Arabische Musik Hamburg | Arabische Musik: Maqam, Rhythmus und Stimme | Maqam, Iqaa und Stimme: Was arabische Musik ausmacht und wie sie in Hamburg zwischen Tradition und neuen Klängen weiterlebt. | Organization, WebSite, BreadcrumbList, Article |

## DE — Termine / Events

| Termin | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Dabke 2026-11-05 | `/event/dabke-kurs-hamburg-2026-11-05/` | Dabke Kurs Hamburg 5. November 2026 | Dabke Kurs Hamburg am 5. November 2026 | Offener Dabke-Kurs in Hamburg am 5. November 2026, 18:15–19:45 Uhr im Goldbekhaus. Eintritt frei, Spenden willkommen – keine Vorkenntnisse nötig. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-11-19 | `/event/dabke-kurs-hamburg-2026-11-19/` | Dabke Kurs Hamburg 19. November 2026 | Dabke Kurs Hamburg am 19. November 2026 | Offener Dabke-Kurs in Hamburg am 19. November 2026, 18:15–19:45 Uhr im Goldbekhaus. Eintritt frei, Spenden willkommen – keine Vorkenntnisse nötig. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-12-03 | `/event/dabke-kurs-hamburg-2026-12-03/` | Dabke Kurs Hamburg 3. Dezember 2026 | Dabke Kurs Hamburg am 3. Dezember 2026 | Offener Dabke-Kurs in Hamburg am 3. Dezember 2026, 18:15–19:45 Uhr im Goldbekhaus. Eintritt frei, Spenden willkommen – keine Vorkenntnisse nötig. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-12-17 | `/event/dabke-kurs-hamburg-2026-12-17/` | Dabke Kurs Hamburg 17. Dezember 2026 | Dabke Kurs Hamburg am 17. Dezember 2026 | Offener Dabke-Kurs in Hamburg am 17. Dezember 2026, 18:15–19:45 Uhr im Goldbekhaus. Eintritt frei, Spenden willkommen – keine Vorkenntnisse nötig. | Organization, WebSite, BreadcrumbList, Event |

## EN — Seiten / Pages

| Seite | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Startseite / Home | `/en/` | Arab Cultural Festival Hamburg | Arab Cultural Festival Hamburg – Events & Dabke | The Arab Cultural Festival brings dabke, Arabic music, calligraphy and literature to Hamburg. See the dates, the programme and how to join in. | Organization, WebSite, BreadcrumbList |
| Festival | `/en/festival/` | Intercultural Festival Hamburg | Intercultural Festival Hamburg: Arab Culture | Music, dabke, calligraphy and literature: the Arab Cultural Festival shows the diversity of Arab culture in Hamburg – open and intercultural. | Organization, WebSite, BreadcrumbList |
| Programm / Programme | `/en/programme/` | Dabke Classes Hamburg | Dabke Classes Hamburg & Festival Programme | Learn dabke in Hamburg: open classes, music and hands-on workshops. Find the Arab Cultural Festival programme and all upcoming dates. | Organization, WebSite, BreadcrumbList |
| Termine / Events | `/en/events/` | Arab Events Hamburg | Arab Events Hamburg: Dates 2026 | All Arab Cultural Festival dates in Hamburg: dabke classes, concerts and community meetups. Date, time and venue at a glance. | Organization, WebSite, BreadcrumbList |
| Artikel / Articles | `/en/articles/` | Arabic Music Hamburg | Arabic Music & Culture: Articles and Stories | Stories about dabke, Arabic music, calligraphy and culture in exile – read and written by and with the community in Hamburg. | Organization, WebSite, BreadcrumbList |
| Über uns / About us | `/en/about/` | Arab Culture Hamburg | Arab Culture Hamburg: About us | Who is behind the Arab Cultural Festival: Ahmad AL Zaher and Arap Culture promote Arab culture, dabke and intercultural exchange in Hamburg. | Organization, WebSite, BreadcrumbList |
| Kontakt / Contact | `/en/contact/` | Arab Cultural Festival Hamburg contact | Contact – Arab Cultural Festival Hamburg | Questions about dates, dabke classes or press? Write to info@arab-kulturfestival.de or use the contact form. | Organization, WebSite, BreadcrumbList |
| Presse / Press | `/en/press/` | Arab Cultural Festival Hamburg press | Press – Arab Cultural Festival Hamburg | Press material, media reports and contacts for the Arab Cultural Festival Hamburg: find coverage and get in touch with our team. | Organization, WebSite, BreadcrumbList |
| Datenschutz / Privacy | `/en/privacy/` | privacy Arab Cultural Festival | Privacy – Arab Cultural Festival Hamburg | How we process personal data, which services we use and how you can change or withdraw your YouTube consent at any time. | Organization, WebSite, BreadcrumbList |
| Impressum / Legal notice | `/en/legal-notice/` | legal notice Arab Cultural Festival | Legal notice – Arab Cultural Festival Hamburg | Provider information: Arab Cultural Festival, Julius-Vosseler-Straße 106b, 22527 Hamburg, Germany. Contact details and responsibility. | Organization, WebSite, BreadcrumbList |
| Cookies | `/en/cookies/` | cookies Arab Cultural Festival | Cookies – Arab Cultural Festival Hamburg | Which cookies and local storage we use: no analytics or advertising cookies, YouTube only after your explicit consent. | Organization, WebSite, BreadcrumbList |

## EN — Artikel / Articles

| Artikel | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Artikel: Dabke braucht keine Einladung | `/dabke-needs-no-invitation/` | Dabke Hamburg | Dabke in Hamburg: why the dance needs no invitation | Dabke is more than a dance: it happens in a circle, without a stage and without an invitation. A look at origins, rhythm and community. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Kultur im Exil | `/arabic-culture-in-exile/` | Arab culture Hamburg | Arabic culture in exile: identity that stays | What remains when home is missing? How Arabic culture lives on in exile – through music, language, food and shared evenings. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Kalligrafie | `/calligraphy/` | Arabic calligraphy Hamburg | Arabic calligraphy: when letters become images | Arabic calligraphy connects writing and image. How letters become art and why handwriting still fascinates today. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Arabische Musik | `/arabic-music/` | Arabic music Hamburg | Arabic music: maqam, rhythm and voice | Maqam, iqaa and voice: what defines Arabic music and how it lives on in Hamburg between tradition and new sounds. | Organization, WebSite, BreadcrumbList, Article |

## EN — Termine / Events

| Termin | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Dabke 2026-11-05 | `/event/dabke-class-hamburg-2026-11-05/` | Dabke class Hamburg 5 November 2026 | Dabke class Hamburg on 5 November 2026 | Open dabke class in Hamburg on 5 November 2026, 6:15–7:45 pm at Goldbekhaus. Free entry, donations welcome – no experience needed. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-11-19 | `/event/dabke-class-hamburg-2026-11-19/` | Dabke class Hamburg 19 November 2026 | Dabke class Hamburg on 19 November 2026 | Open dabke class in Hamburg on 19 November 2026, 6:15–7:45 pm at Goldbekhaus. Free entry, donations welcome – no experience needed. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-12-03 | `/event/dabke-class-hamburg-2026-12-03/` | Dabke class Hamburg 3 December 2026 | Dabke class Hamburg on 3 December 2026 | Open dabke class in Hamburg on 3 December 2026, 6:15–7:45 pm at Goldbekhaus. Free entry, donations welcome – no experience needed. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-12-17 | `/event/dabke-class-hamburg-2026-12-17/` | Dabke class Hamburg 17 December 2026 | Dabke class Hamburg on 17 December 2026 | Open dabke class in Hamburg on 17 December 2026, 6:15–7:45 pm at Goldbekhaus. Free entry, donations welcome – no experience needed. | Organization, WebSite, BreadcrumbList, Event |

## AR — Seiten / Pages

| Seite | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Startseite / Home | `/ar/` | مهرجان الثقافة العربية في هامبورغ | مهرجان الثقافة العربية في هامبورغ – فعاليات ودبكة | يجمع مهرجان الثقافة العربية في هامبورغ الدبكة والموسيقى والخط العربي والأدب. تعرّف على المواعيد والبرنامج وكيف تشارك. | Organization, WebSite, BreadcrumbList |
| Festival | `/ar/festival/` | مهرجان ثقافي في هامبورغ | مهرجان ثقافي في هامبورغ: الثقافة العربية | موسيقى ودبكة وخط عربي وأدب: يقدّم مهرجان الثقافة العربية تنوّع الثقافة العربية في هامبورغ بروح منفتحة وتفاعلية. | Organization, WebSite, BreadcrumbList |
| Programm / Programme | `/ar/program/` | دورة دبكة في هامبورغ | دورة دبكة في هامبورغ وبرنامج المهرجان | تعلّم الدبكة في هامبورغ: دورات مفتوحة وموسيقى وفعاليات تفاعلية. اكتشف برنامج مهرجان الثقافة العربية وجميع المواعيد. | Organization, WebSite, BreadcrumbList |
| Termine / Events | `/ar/events/` | فعاليات عربية في هامبورغ | فعاليات عربية في هامبورغ: مواعيد 2026 | جميع مواعيد مهرجان الثقافة العربية في هامبورغ: دورات دبكة وحفلات ولقاءات. التاريخ والوقت والمكان في نظرة واحدة. | Organization, WebSite, BreadcrumbList |
| Artikel / Articles | `/ar/articles/` | موسيقى عربية في هامبورغ | موسيقى عربية وثقافة: مقالات وحكايات | حكايات عن الدبكة والموسيقى العربية والخط العربي والثقافة في المنفى — بقلم المجتمع وفي هامبورغ. | Organization, WebSite, BreadcrumbList |
| Über uns / About us | `/ar/about/` | الثقافة العربية في هامبورغ | الثقافة العربية في هامبورغ: من نحن | من يقف خلف مهرجان الثقافة العربية: أحمد الظاهر وآراب كالتشر، من أجل الثقافة العربية والدبكة والتبادل الثقافي في هامبورغ. | Organization, WebSite, BreadcrumbList |
| Kontakt / Contact | `/ar/contact/` | مهرجان الثقافة العربية في هامبورغ تواصل | اتصل بنا – مهرجان الثقافة العربية في هامبورغ | أسئلة عن المواعيد أو دورات الدبكة أو الصحافة؟ راسلنا على info@arab-kulturfestival.de أو استخدم نموذج التواصل. | Organization, WebSite, BreadcrumbList |
| Presse / Press | `/ar/press/` | مهرجان الثقافة العربية في هامبورغ صحافة | الصحافة – مهرجان الثقافة العربية في هامبورغ | مواد صحفية وتقارير وجهات اتصال لمهرجان الثقافة العربية في هامبورغ: تغطية إعلامية وتواصل مباشر مع الفريق. | Organization, WebSite, BreadcrumbList |
| Datenschutz / Privacy | `/ar/privacy/` | خصوصية مهرجان الثقافة العربية | إعدادات الخصوصية – مهرجان الثقافة العربية في هامبورغ | كيف نعالج البيانات الشخصية، وما الخدمات التي نستخدمها، وكيف يمكنك تغيير موافقتك على يوتيوب أو سحبها في أي وقت. | Organization, WebSite, BreadcrumbList |
| Impressum / Legal notice | `/ar/imprint/` | بيانات الناشر مهرجان الثقافة العربية | بيانات الناشر – مهرجان الثقافة العربية في هامبورغ | بيانات الجهة المسؤولة: مهرجان الثقافة العربية، Julius-Vosseler-Straße 106b، 22527 هامبورغ، ألمانيا. بيانات التواصل والمسؤولية. | Organization, WebSite, BreadcrumbList |
| Cookies | `/ar/cookies/` | ملفات تعريف الارتباط مهرجان الثقافة العربية | ملفات تعريف الارتباط – مهرجان الثقافة العربية في هامبورغ | ما ملفات تعريف الارتباط والتخزين المحلي التي نستخدمها: لا ملفات تحليل أو إعلان، ويوتيوب فقط بعد موافقتك الصريحة. | Organization, WebSite, BreadcrumbList |

## AR — Artikel / Articles

| Artikel | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Artikel: Dabke braucht keine Einladung | `/dabke-la-tahtaj-dawa/` | دبكة في هامبورغ | الدبكة في هامبورغ: رقصة لا تحتاج إلى دعوة | الدبكة أكثر من رقص: تُرقص في حلقة، بلا مسرح وبلا دعوة. نظرة على الجذور والإيقاع والجماعة. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Kultur im Exil | `/arabic-culture-in-exile-ar/` | الثقافة العربية في هامبورغ | الثقافة العربية في المنفى: هوية تبقى | ماذا يبقى عندما يغيب الوطن؟ كيف تعيش الثقافة العربية في المنفى عبر الموسيقى واللغة والطعام والأمسيات المشتركة. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Kalligrafie | `/calligraphy-ar/` | الخط العربي هامبورغ | الخط العربي: حين تصبح الحروف صوراً | الخط العربي يجمع بين الكتابة والصورة. كيف تتحول الحروف إلى فن ولماذا ما زال الخط يأسرنا حتى اليوم. | Organization, WebSite, BreadcrumbList, Article |
| Artikel: Arabische Musik | `/arabic-music-ar/` | موسيقى عربية في هامبورغ | الموسيقى العربية: المقام والإيقاع والصوت | المقام والإيقاع والصوت: ما يميّز الموسيقى العربية وكيف تعيش في هامبورغ بين التقليد والأصوات الجديدة. | Organization, WebSite, BreadcrumbList, Article |

## AR — Termine / Events

| Termin | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |
| --- | --- | --- | --- | --- | --- |
| Dabke 2026-11-05 | `/event/dabke-hamburg-2026-11-05/` | دورة دبكة في هامبورغ 5 نوفمبر 2026 | دورة دبكة في هامبورغ 5 نوفمبر 2026 | دورة دبكة مفتوحة في هامبورغ يوم 5 نوفمبر 2026 من 18:15 إلى 19:45 في غولدبيك هاوس. الدخول مجاني والتبرعات مرحّب بها — دون خبرة سابقة. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-11-19 | `/event/dabke-hamburg-2026-11-19/` | دورة دبكة في هامبورغ 19 نوفمبر 2026 | دورة دبكة في هامبورغ 19 نوفمبر 2026 | دورة دبكة مفتوحة في هامبورغ يوم 19 نوفمبر 2026 من 18:15 إلى 19:45 في غولدبيك هاوس. الدخول مجاني والتبرعات مرحّب بها — دون خبرة سابقة. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-12-03 | `/event/dabke-hamburg-2026-12-03/` | دورة دبكة في هامبورغ 3 ديسمبر 2026 | دورة دبكة في هامبورغ 3 ديسمبر 2026 | دورة دبكة مفتوحة في هامبورغ يوم 3 ديسمبر 2026 من 18:15 إلى 19:45 في غولدبيك هاوس. الدخول مجاني والتبرعات مرحّب بها — دون خبرة سابقة. | Organization, WebSite, BreadcrumbList, Event |
| Dabke 2026-12-17 | `/event/dabke-hamburg-2026-12-17/` | دورة دبكة في هامبورغ 17 ديسمبر 2026 | دورة دبكة في هامبورغ 17 ديسمبر 2026 | دورة دبكة مفتوحة في هامبورغ يوم 17 ديسمبر 2026 من 18:15 إلى 19:45 في غولدبيك هاوس. الدخول مجاني والتبرعات مرحّب بها — دون خبرة سابقة. | Organization, WebSite, BreadcrumbList, Event |

## Pflege im Backend / Editing in wp-admin

1. `Beiträge` → Seite/Artikel/Termin öffnen.
2. Im Block **Rank Math SEO** (unter dem Editor) das Feld **Focus Keyword** prüfen und Titel sowie Description überschreiben. Die Felder sind pro Übersetzung eigene Beiträge, also dreimal pflegen.
3. Titel-Templates unter `Rank Math SEO → Titles & Meta` ändern, wenn der Aufbau (`%title% %sep% %sitename%`) für alle Seiten gelten soll. Die hier dokumentierten Titel sind individuelle Overrides und gewinnen immer.
4. Nach dem Speichern cached Rank Math die Sitemap. Unter `Rank Math SEO → Sitemap Settings` auf **Regenerate** klicken (oder `wp rank-math sitemap generate`).

## Google Search Console

1. In der Search Console eine Property für die Domain anlegen (Domain-Property, nicht URL-Präfix), damit DE, EN und AR gemeinsam gemessen werden.
2. `Sitemap` → `https://domain.tld/sitemap_index.xml` einreichen. Die Indexdatei listet `page-sitemap.xml`, `post-sitemap.xml` und `yd_event-sitemap.xml`.
3. Unter `Internationale Targeting` prüfen, dass Google die hreflang-Angaben erkennt. Die Zuordnung kommt aus dem Theme, nicht aus Rank Math.
4. Nach dem Launch `URL-Prüfung` für die drei Startseiten (`/`, `/en/`, `/ar/`) und je einen Termin nutzen und die Indexierung beantragen.

## Was hier bewusst nicht steht

- Keine erfundenen Bewertungen (`aggregateRating`) und keine Fake-Rezensionen im Event-Schema.
- Keine Platzhalter-Termine: die vier Kurse (05.11., 19.11., 03.12., 17.12.2026, 18:15–19:45, Goldbekhaus) sind bestätigt (Stand 28.10.2026); zwei ältere Seed-Termine liegen als Entwurf vor.
- Beiträge und Termine liegen sprachübergreifend unter derselben URL-Struktur (`/dabke-…/`, `/event/…/`), nicht unter `/en/` oder `/ar/`. Die Sprachzuordnung passiert über hreflang und Canonical, nicht über den Pfad.
- Keine Drittanbieter-Requests beim Laden: Schriften liegen lokal (`assets/fonts/`, OFL), YouTube lädt erst nach Einwilligung. Google Fonts, `gstatic` und ähnliche Hosts kommen im Quelltext nicht vor.
- Keine Garantie auf Ranking 1. Die Tabelle dokumentiert die technische Grundlage, nicht ein Versprechen.
