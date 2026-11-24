<?php
/**
 * Demo / editorial content for the three language trees.
 *
 * All texts are taken from, or derived from, the public pages of
 * arab-kulturfestival.de and translated into English and Arabic.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page definitions.
 *
 * @return array
 */
function yd_demo_pages() {
	return array(

		// ------------------------------------------------------------ German.
		array(
			'lang'       => 'de',
			'group'      => 'home',
			'title'      => 'Startseite',
			'slug'       => 'startseite',
			'menu_order' => 0,
			'content'    => '',
		),
		array(
			'lang'       => 'de',
			'group'      => 'festival',
			'title'      => 'Festival',
			'slug'       => 'festival',
			'menu_order' => 1,
			'excerpt'    => 'Drei Tage, vier Kunstformen und eine Stadt: das Arab Kulturfestival in Hamburg.',
			'content'    => '
<p class="lede">Seit 2022 lädt das Arab Kulturfestival in Hamburg dazu ein, die Vielfalt arabischer Kulturen zu erleben — mit Musik, Dabke, Kalligrafie, Literatur und Film. Das Festival findet im Goldbekhaus, an der Universität Hamburg und an wechselnden Orten in der Stadt statt und wird von Arap Culture organisiert.</p>
<h2>Ein Festival, das verbindet</h2>
<p>Das Arab Kulturfestival ist ein jährliches Ereignis, das den kulturellen Austausch stärkt, Vorurteile abbaut und die Kunst, die Musik und die Literatur der arabischen Welt feiert. Arabisch und Deutsch sind die Sprachen des Festivals — und wo Worte fehlen, übernehmen Musik und Tanz.</p>
<p>„Willkommen! اهلا وسهلا بكم" — mit diesen Worten beginnt jedes Jahr unser Programm, und sie gelten allen, die neugierig sind.</p>
<h2>Programm 2024 — Poesie & Musik</h2>
<p>Das Festival 2024 stand im Zeichen der arabischen Poesie. Drei Tage lang trafen sich Dichter:innen, Musiker:innen und ein neugieriges Publikum im Goldbekhaus.</p>
<ul>
<li><strong>Freitag, Bühne zum Hof:</strong> Bauchtanz-Workshop für Frauen, arabischer Kaffee, Oriental Band, Sufi-Tanz.</li>
<li><strong>Samstag, Halle:</strong> Poesie mit Musik und Zeichnung, Nostalgia Band, Dabke-Workshop und Dabke mit Live-Musik.</li>
<li><strong>Sonntag, Bühne zum Hof:</strong> Kinderprogramm, arabischer Kaffee, Hakawati mit Bassam Dawood, arabischer Film und ein Gespräch mit dem Regisseur Mohammed Al-Sheikh.</li>
</ul>
<h2>Programm 2023 — Sprache & Erbe</h2>
<p>2023 verband das Festival Ausstellung, Musik und Tanz: ein arabischer Filmclub, eine Ausstellungseröffnung mit Ali Shibly, „Dein Name auf Arabisch", eine Lesung und Spirit of the East mit Dabke.</p>
<h2>Programm 2022 — der Anfang</h2>
<p>Die erste Ausgabe brachte Dabke, die Shibli-Band, STORYFELD Hamburg und eine Oriental Band auf die Bühne — der Beginn einer jährlichen Tradition.</p>
<h2>Programmhefte</h2>
<p>Die vollständigen Programme mit Fotos der teilnehmenden Gruppen stehen als PDF bereit.</p>
<p><a class="btn btn--sm" href="%PDF2024%">Programmheft 2024 (PDF)</a> <a class="btn btn--sm btn--ghost" href="%PDF2022%">Programmheft 2022 (PDF)</a></p>
',
		),
		array(
			'lang'       => 'de',
			'group'      => 'program',
			'title'      => 'Programm',
			'slug'       => 'programm',
			'menu_order' => 2,
			'excerpt'    => 'Vier Formate, ein Kreis: Dabke, arabische Musik, Kalligrafie und Literatur.',
			'content'    => '
<p class="lede">Vier Formate bilden das Rückgrat des Festivals. Sie alle eint dieselbe Idee: Kultur nicht nur anzusehen, sondern selbst Teil von ihr zu werden.</p>
<h2>Folkloretanz & Dabke</h2>
<p>Wenn Menschen sich an den Händen halten und im Takt der Musik schwingen, ist die Dabke schon da. Der Volkstanz aus der Levante braucht keine Einladung: Wer die Reihe hält, ist Teil des Kreises — Vorkenntnisse sind nicht nötig. Die offenen Kurse sind kostenfrei, über eine Spende für unsere Arbeit freuen wir uns.</p>
<h2>Arabische Musik</h2>
<p>Oud, Violine, Perkussion und Stimme: Arabische Musik wirkt vor allem durch ihre Melodien, ihre Maqam-Tonsysteme und ihre rhythmischen Muster. Von klassischen Ensembles bis zu zeitgenössischen Bands ist alles dabei.</p>
<h2>Kalligrafie</h2>
<p>Buchstaben als Kunstform. In Workshops und Ausstellungen zeigen wir die Geschichte der arabischen Schrift, ihre Formen und die Ruhe, die im Schreiben liegt.</p>
<h2>Literatur & Hakawati</h2>
<p>Lesungen, Gespräche und das traditionelle Geschichtenerzählen — Hakawati — bringen arabische Literatur ins Exil und zurück auf die Bühne.</p>
<h2>Für Schulen, Vereine und Teams</h2>
<p>Alle Formate lassen sich als Workshop buchen — für Schulklassen, Kulturhäuser, Teams und Festivals. Schreib uns mit Wunschtermin und Gruppengröße.</p>
',
		),
		array(
			'lang'       => 'de',
			'group'      => 'events',
			'title'      => 'Termine',
			'slug'       => 'termine',
			'menu_order' => 3,
			'excerpt'    => 'Alle kommenden Workshops, Konzerte und Festivaltage.',
			'content'    => '<p class="lede">Alle Termine auf einen Blick. Die Teilnahme an vielen Formaten ist kostenfrei — über eine Spende freuen wir uns.</p>',
		),
		array(
			'lang'       => 'de',
			'group'      => 'articles',
			'title'      => 'Artikel',
			'slug'       => 'artikel',
			'menu_order' => 4,
			'excerpt'    => 'Texte über Dabke, Kalligrafie, Musik und arabische Kultur im Exil.',
			'content'    => '<p class="lede">Unser Magazin: Geschichten, Hintergründe und Stimmen aus der arabischen Kultur in Hamburg und darüber hinaus.</p>',
		),
		array(
			'lang'       => 'de',
			'group'      => 'press',
			'title'      => 'Presse',
			'slug'       => 'presse',
			'menu_order' => 5,
			'excerpt'    => 'Berichte, Ankündigungen und Porträts über das Arab Kulturfestival in Hamburg.',
			'content'    => '
<p class="lede">Berichte, Ankündigungen und Porträts über das Arab Kulturfestival — aus Hamburger Kulturportalen, von unseren Partner:innen und aus der Presse.</p>
<h2>Aus der Presse</h2>
<ul class="press-list">
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">STADTKULTUR HAMBURG</span><span class="press-item__date">17. Mai 2024</span></p>
<h3 class="press-item__title"><a href="https://www.stadtkultur-hh.de/2024/05/arab-kulturfestival-vom-24-bis-26-mai-2024-im-goldbekhaus" target="_blank" rel="noopener noreferrer">Arab Kulturfestival vom 24. bis 26. Mai 2024 im Goldbekhaus</a></h3>
<p class="press-item__text">Das Festival feiert die Schönheit und Vielfalt der arabischen Kultur: Bauchtanz-Workshop und Oriental Band am Freitag, Poesie, Weltmusik und Dabke am Samstag, Kinderprogramm, Hakawati und Film am Sonntag.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">STADTKULTUR HAMBURG</span><span class="press-item__date">7. September 2022</span></p>
<h3 class="press-item__title"><a href="https://www.stadtkultur-hh.de/2022/09/arab-kulturfestival-vom-9-bis-zum-11-september-2022-im-goldbekhaus" target="_blank" rel="noopener noreferrer">Arab Kulturfestival vom 9. bis zum 11. September 2022 im Goldbekhaus</a></h3>
<p class="press-item__text">Die erste große Ausgabe: Konzerte mit Spirit of the East und der Oriental Band, Tanz mit dem OrientTanzEnsembleHamburg, Film und Kinderprogramm, dazu eine Vernissage im QR134 und eine kleine Buchmesse arabischer Literatur.</p>
</li>
</ul>
<h2>Partner &amp; Kulturportale</h2>
<ul class="press-list">
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">Arab Filmclub</span></p>
<h3 class="press-item__title"><a href="https://arab-filmclub.de/arab-kulturfestival-%d9%85%d9%87%d8%b1%d8%ac%d8%a7%d9%86-%d8%a7%d9%84%d8%ab%d9%82%d8%a7%d9%81%d8%a9-%d8%a7%d9%84%d8%b9%d8%b1%d8%a8%d9%8a%d8%a9/" target="_blank" rel="noopener noreferrer">Arab Kulturfestival مهرجان الثقافة العربية</a></h3>
<p class="press-item__text">Unser Partner Arab Filmclub kündigt das Festival an — mit Konzerten, Tanz, Film und Ausstellung auf Deutsch und Arabisch, dazu ein Programm für Kinder.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">kultur-hamburg.de</span><span class="press-item__date">Kurs im Goldbekhaus</span></p>
<h3 class="press-item__title"><a href="https://www.kultur-hamburg.de/kurse_workshops/dabka-arabischer-tanz-755657" target="_blank" rel="noopener noreferrer">Dabka – Arabischer Tanz</a></h3>
<p class="press-item__text">Der regelmäßige Dabke-Kurs im Goldbekhaus im Veranstaltungskalender von kultur-hamburg.de: donnerstags von 18:15 bis 19:45 Uhr, mit Kursleiter Ahmad.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">Goldbekhaus</span></p>
<h3 class="press-item__title"><a href="https://www.goldbekhaus.de/programm/veranstaltungen" target="_blank" rel="noopener noreferrer">Veranstaltungen im Goldbekhaus</a></h3>
<p class="press-item__text">Unser Veranstaltungsort in Winterhude: Das Goldbekhaus begleitet das Festival seit der ersten Ausgabe als Partner.</p>
</li>
</ul>
<h2>Pressemappe</h2>
<p>Die vollständigen Programmhefte mit Fotos der beteiligten Gruppen stehen als PDF bereit. Bildmaterial und Logo senden wir auf Anfrage zu.</p>
<p><a class="btn btn--sm" href="%PDF2024%">Programmheft 2024 (PDF)</a> <a class="btn btn--sm btn--ghost" href="%PDF2022%">Programmheft 2022 (PDF)</a></p>
<h2>Pressekontakt</h2>
<p>Arap Culture · Arab Kulturfestival<br />Julius-Vosseler-Straße 106b, 22527 Hamburg<br /><a href="mailto:info@arab-kulturfestival.de">info@arab-kulturfestival.de</a> · +49 178 1116220</p>
<p>Für Interviews, Akkreditierungen und Bildmaterial schreib uns bitte mit Medium, Thema und Deadline.</p>
',
		),
		array(
			'lang'       => 'de',
			'group'      => 'about',
			'title'      => 'Über uns',
			'slug'       => 'ueber-uns',
			'menu_order' => 6,
			'excerpt'    => 'Wer wir sind, warum wir das Festival machen und wer dahintersteht.',
			'content'    => '
<p class="lede">Kunst und Kultur spielen eine große Rolle dabei, Menschen in allen sozialen, intellektuellen und kulturellen Fragen zu begleiten — sie berühren die Person selbst, ihre Identität und ihr Handeln.</p>
<h2>Wer sind wir</h2>
<p>Wenn wir etwas über andere Kulturen hören, ist das erste, was uns in den Sinn kommt, die Ethnie, zu der ein Mensch gehört. In Wirklichkeit geht Kultur jedoch viel weiter: Wir alle sind Teil verschiedener kultureller Gruppen, und unsere kulturelle Identität verändert sich, wenn sich die Umstände um uns herum verändern.</p>
<p>Heute leben viele von uns in Hamburg und tragen die arabische Kultur mit sich. Deshalb haben wir das Arab Kulturfestival ins Leben gerufen: um die Vision vieler arabischer Menschen in der Diaspora Wirklichkeit werden zu lassen — Zugehörigkeit.</p>
<h2>Unsere Vision</h2>
<p>Das Arab Kulturfestival in Hamburg möchte Menschen aus verschiedenen Kulturen durch Kunst und Kultur zusammenbringen. Hier sollen Geist und Seele angesprochen werden. Es möchte Bräuche und Traditionen aus der Vielfalt arabischer Kulturen zeigen, die im Einklang mit der Demokratie in Deutschland stehen und die nicht exklusiv, sondern verbindend sind.</p>
<h2>Die gemeinsame Sprache</h2>
<p>Beim Arab Kulturfestival wird Arabisch und Deutsch gesprochen. Die gemeinsame Sprache für alle liegt in der Kunst, in der Musik und im Tanz — Sprachen, die wir alle verstehen, unabhängig von unserer Muttersprache.</p>
<blockquote>„Kunst und Kultur öffnet jede Tür." — Ahmad AL Zaher</blockquote>
<h2>Unser Team</h2>
<p>Getragen wird das Festival von einem Team aus Künstler:innen, Übersetzer:innen, Techniker:innen und Ehrenamtlichen — und von allen, die jedes Jahr wiederkommen.</p>
',
		),
		array(
			'lang'       => 'de',
			'group'      => 'contact',
			'title'      => 'Kontakt',
			'slug'       => 'kontakt',
			'menu_order' => 7,
			'excerpt'    => 'Schreib uns — wir antworten persönlich.',
			'content'    => '<p class="lede">Du planst einen Workshop, einen Auftritt oder eine Zusammenarbeit? Schick uns die wichtigsten Angaben — wir antworten persönlich.</p>',
		),
		array(
			'lang'       => 'de',
			'group'      => 'privacy',
			'title'      => 'Datenschutz',
			'slug'       => 'datenschutz',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p>Verantwortlich im Sinne der Datenschutz-Grundverordnung ist:</p>
<p>Arab Kulturfestival<br />Julius-Vosseler-Straße 106b<br />22527 Hamburg<br />Deutschland<br />E-Mail: info@arab-kulturfestival.de</p>
<h2>Umfang der Verarbeitung</h2>
<p>Wir verarbeiten personenbezogene Daten unserer Nutzer:innen grundsätzlich nur, soweit dies zur Bereitstellung einer funktionsfähigen Website sowie unserer Inhalte und Leistungen erforderlich ist. Die Verarbeitung erfolgt regelmäßig nur nach Einwilligung der Nutzer:innen.</p>
<h2>Kontaktformular</h2>
<p>Wenn du uns über das Kontaktformular schreibst, werden dein Name, deine E-Mail-Adresse und deine Nachricht zur Bearbeitung der Anfrage gespeichert. Rechtsgrundlage ist Art. 6 Abs. 1 lit. a und b DSGVO. Die Daten werden gelöscht, sobald sie für die Bearbeitung nicht mehr erforderlich sind.</p>
<h2>Externe Inhalte</h2>
<p>Videos werden von YouTube (Google Ireland Limited) eingebunden. Sie werden erst geladen, nachdem du ausdrücklich zugestimmt hast. Dabei erhält Google deine IP-Adresse und Wiedergabedaten und kann Cookies setzen.</p>
<h2 id="datenschutzeinstellungen">Datenschutzeinstellungen</h2>
<p>Du kannst deine Einwilligung zu YouTube jederzeit ändern oder widerrufen. Öffne dazu die <a href="#datenschutzeinstellungen" data-yd-consent-open>Datenschutzeinstellungen</a>.</p>
<h2>Deine Rechte</h2>
<p>Du hast das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit und Widerspruch. Wende dich dazu an info@arab-kulturfestival.de.</p>
',
		),
		array(
			'lang'       => 'de',
			'group'      => 'cookies',
			'title'      => 'Cookies',
			'slug'       => 'cookies',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p class="lede">Diese Seite erklärt, welche Cookies und lokalen Speicher wir verwenden — und wie du deine Auswahl änderst.</p>
<h2>Keine Marketing- oder Statistik-Cookies</h2>
<p>Wir setzen keine Analyse-, Werbe- oder Tracking-Cookies ein. Es gibt kein Profiling und keine Weitergabe von Nutzungsdaten an Dritte.</p>
<h2>YouTube</h2>
<p>Videos werden erst geladen, nachdem du im Dialog „YouTube-Videos?“ zugestimmt hast. Vorher wird keine Verbindung zu YouTube oder Google aufgebaut und es werden keine Vorschaubilder geladen. Deine Auswahl speichern wir ausschließlich lokal in deinem Browser (localStorage, Schlüssel <code>yd_consent_youtube</code>). Diese Information verlässt dein Gerät nicht.</p>
<h2>Schriftarten</h2>
<p>Die Schriftarten werden mit der Website ausgeliefert und lokal von unserem Server geladen. Es wird keine Verbindung zu Google Fonts oder einem anderen Font-Dienst aufgebaut und keine IP-Adresse an Dritte übertragen.</p>
<h2>Auswahl ändern oder widerrufen</h2>
<p>Öffne die <a href="#datenschutzeinstellungen" data-yd-consent-open>Datenschutzeinstellungen</a>, um YouTube zu erlauben oder zu deaktivieren. Beim Widerruf werden bereits geladene Player entfernt.</p>
',
		),
		array(
			'lang'       => 'de',
			'group'      => 'imprint',
			'title'      => 'Impressum',
			'slug'       => 'impressum',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p><strong>Arab Kulturfestival</strong><br />Julius-Vosseler-Straße 106b<br />22527 Hamburg<br />Deutschland</p>
<p>E-Mail: info@arab-kulturfestival.de<br />Telefon: +49 178 1116220</p>
<h2>Verantwortlich für den Inhalt</h2>
<p>Ahmad AL Zaher — Gründer und künstlerischer Leiter des Arab Kulturfestivals, Gründer und Chefredakteur von Arap Culture.</p>
<h2>Haftungsausschluss</h2>
<p>Der Autor übernimmt keinerlei Gewähr für die Aktualität, Korrektheit, Vollständigkeit oder Qualität der bereitgestellten Informationen. Haftungsansprüche gegen den Autor, die sich auf Schäden materieller oder ideeller Art beziehen, sind grundsätzlich ausgeschlossen, sofern kein nachweislich vorsätzliches oder grob fahrlässiges Verschulden vorliegt.</p>
<h2>Urheberrecht</h2>
<p>Die Rechte an sämtlichen Grafiken, Bildern, Texten, Tondokumenten und Videosequenzen dieser Seite liegen beim jeweiligen Urheber. Die Veränderung, Vervielfältigung oder Verwendung in anderen elektronischen oder gedruckten Publikationen ist ohne ausdrückliche Zustimmung nicht gestattet.</p>
',
		),

		// ----------------------------------------------------------- English.
		array(
			'lang'       => 'en',
			'group'      => 'home',
			'title'      => 'Home',
			'slug'       => 'en',
			'menu_order' => 0,
			'content'    => '',
		),
		array(
			'lang'       => 'en',
			'group'      => 'festival',
			'title'      => 'Festival',
			'slug'       => 'festival',
			'menu_order' => 1,
			'excerpt'    => 'Three days, four art forms and one city: the Arab Culture Festival in Hamburg.',
			'content'    => '
<p class="lede">Since 2022 the Arab Culture Festival has invited Hamburg to experience the diversity of Arab cultures — with music, dabke, calligraphy, literature and film. The festival takes place at Goldbekhaus, the University of Hamburg and changing venues across the city, organised by Arap Culture.</p>
<h2>A festival that connects</h2>
<p>The Arab Culture Festival is a yearly event that strengthens cultural exchange, breaks down prejudice and celebrates the art, music and literature of the Arab world. Arabic and German are the languages of the festival — and where words run out, music and dance take over.</p>
<p>“Welcome! اهلا وسهلا بكم” — those words open our programme every year, and they are meant for everyone who is curious.</p>
<h2>2024 — poetry & music</h2>
<p>The 2024 edition was dedicated to Arabic poetry. For three days, poets, musicians and a curious audience met at Goldbekhaus.</p>
<ul>
<li><strong>Friday, Bühne zum Hof:</strong> belly-dance workshop for women, Arabic coffee, Oriental Band, Sufi dance.</li>
<li><strong>Saturday, Halle:</strong> poetry with music and drawing, Nostalgia Band, dabke workshop and dabke with live music.</li>
<li><strong>Sunday, Bühne zum Hof:</strong> children’s programme, Arabic coffee, hakawati with Bassam Dawood, an Arabic film and a talk with director Mohammed Al-Sheikh.</li>
</ul>
<h2>2023 — language & heritage</h2>
<p>In 2023 the festival combined exhibition, music and dance: an Arab film club, an exhibition opening with Ali Shibly, “Your name in Arabic”, a reading and Spirit of the East with dabke.</p>
<h2>2022 — the beginning</h2>
<p>The first edition brought dabke, the Shibli Band, STORYFELD Hamburg and an Oriental Band to the stage — the start of a yearly tradition.</p>
<h2>Programme booklets</h2>
<p>The full programmes with photos of the participating groups are available as PDF.</p>
<p><a class="btn btn--sm" href="%PDF2024%">Programme 2024 (PDF)</a> <a class="btn btn--sm btn--ghost" href="%PDF2022%">Programme 2022 (PDF)</a></p>
',
		),
		array(
			'lang'       => 'en',
			'group'      => 'program',
			'title'      => 'Programme',
			'slug'       => 'programme',
			'menu_order' => 2,
			'excerpt'    => 'Four formats, one circle: dabke, Arabic music, calligraphy and literature.',
			'content'    => '
<p class="lede">Four formats form the backbone of the festival. They share one idea: not just to watch culture, but to become part of it.</p>
<h2>Folk dance & dabke</h2>
<p>When people hold hands and sway to the music, dabke is already there. The folk dance from the Levant needs no invitation: whoever holds the line is part of the circle — no experience required. The open classes are free of charge; donations for our work are welcome.</p>
<h2>Arabic music</h2>
<p>Oud, violin, percussion and voice: Arabic music works through its melodies, its maqam systems and its rhythmic patterns. From classical ensembles to contemporary bands, everything is welcome.</p>
<h2>Calligraphy</h2>
<p>Letters as an art form. In workshops and exhibitions we show the history of Arabic script, its forms and the calm that comes with writing.</p>
<h2>Literature & hakawati</h2>
<p>Readings, talks and traditional storytelling — hakawati — bring Arabic literature into exile and back onto the stage.</p>
<h2>For schools, associations and teams</h2>
<p>All formats can be booked as workshops — for school classes, cultural centres, teams and festivals. Write to us with your preferred date and group size.</p>
',
		),
		array(
			'lang'       => 'en',
			'group'      => 'events',
			'title'      => 'Events',
			'slug'       => 'events',
			'menu_order' => 3,
			'excerpt'    => 'All upcoming workshops, concerts and festival days.',
			'content'    => '<p class="lede">Every date at a glance. Many formats are free — donations are welcome.</p>',
		),
		array(
			'lang'       => 'en',
			'group'      => 'articles',
			'title'      => 'Articles',
			'slug'       => 'articles',
			'menu_order' => 4,
			'excerpt'    => 'Texts about dabke, calligraphy, music and Arabic culture in exile.',
			'content'    => '<p class="lede">Our magazine: stories, background and voices from Arabic culture in Hamburg and beyond.</p>',
		),
		array(
			'lang'       => 'en',
			'group'      => 'press',
			'title'      => 'Press',
			'slug'       => 'press',
			'menu_order' => 5,
			'excerpt'    => 'Reports, announcements and portraits of the Arab Culture Festival in Hamburg.',
			'content'    => '
<p class="lede">Reports, announcements and portraits of the Arab Culture Festival — from Hamburg culture portals, from our partners and from the press.</p>
<h2>In the press</h2>
<ul class="press-list">
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">STADTKULTUR HAMBURG</span><span class="press-item__date">17 May 2024</span></p>
<h3 class="press-item__title"><a href="https://www.stadtkultur-hh.de/2024/05/arab-kulturfestival-vom-24-bis-26-mai-2024-im-goldbekhaus" target="_blank" rel="noopener noreferrer">Arab Kulturfestival vom 24. bis 26. Mai 2024 im Goldbekhaus</a></h3>
<p class="press-item__text">The festival celebrates the beauty and diversity of Arabic culture: belly-dance workshop and Oriental Band on Friday, poetry, world music and dabke on Saturday, children’s programme, hakawati and film on Sunday.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">STADTKULTUR HAMBURG</span><span class="press-item__date">7 September 2022</span></p>
<h3 class="press-item__title"><a href="https://www.stadtkultur-hh.de/2022/09/arab-kulturfestival-vom-9-bis-zum-11-september-2022-im-goldbekhaus" target="_blank" rel="noopener noreferrer">Arab Kulturfestival vom 9. bis zum 11. September 2022 im Goldbekhaus</a></h3>
<p class="press-item__text">The first big edition: concerts with Spirit of the East and the Oriental Band, dance with the OrientTanzEnsembleHamburg, film and children’s programme, plus a vernissage at QR134 and a small Arabic book fair.</p>
</li>
</ul>
<h2>Partners &amp; culture portals</h2>
<ul class="press-list">
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">Arab Filmclub</span></p>
<h3 class="press-item__title"><a href="https://arab-filmclub.de/arab-kulturfestival-%d9%85%d9%87%d8%b1%d8%ac%d8%a7%d9%86-%d8%a7%d9%84%d8%ab%d9%82%d8%a7%d9%81%d8%a9-%d8%a7%d9%84%d8%b9%d8%b1%d8%a8%d9%8a%d8%a9/" target="_blank" rel="noopener noreferrer">Arab Kulturfestival مهرجان الثقافة العربية</a></h3>
<p class="press-item__text">Our partner Arab Filmclub announces the festival — with concerts, dance, film and an exhibition in German and Arabic, plus a programme for children.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">kultur-hamburg.de</span><span class="press-item__date">Course at Goldbekhaus</span></p>
<h3 class="press-item__title"><a href="https://www.kultur-hamburg.de/kurse_workshops/dabka-arabischer-tanz-755657" target="_blank" rel="noopener noreferrer">Dabka – Arabischer Tanz</a></h3>
<p class="press-item__text">The regular dabke course at Goldbekhaus in the kultur-hamburg.de listings: Thursdays from 18:15 to 19:45, with course leader Ahmad.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">Goldbekhaus</span></p>
<h3 class="press-item__title"><a href="https://www.goldbekhaus.de/programm/veranstaltungen" target="_blank" rel="noopener noreferrer">Events at Goldbekhaus</a></h3>
<p class="press-item__text">Our venue in Winterhude: Goldbekhaus has accompanied the festival as a partner since the first edition.</p>
</li>
</ul>
<h2>Press kit</h2>
<p>The full programme booklets with photos of the participating groups are available as PDF. Images and the logo are available on request.</p>
<p><a class="btn btn--sm" href="%PDF2024%">Programme booklet 2024 (PDF)</a> <a class="btn btn--sm btn--ghost" href="%PDF2022%">Programme booklet 2022 (PDF)</a></p>
<h2>Press contact</h2>
<p>Arap Culture · Arab Culture Festival<br />Julius-Vosseler-Straße 106b, 22527 Hamburg<br /><a href="mailto:info@arab-kulturfestival.de">info@arab-kulturfestival.de</a> · +49 178 1116220</p>
<p>For interviews, accreditation and image material, please write to us with your outlet, topic and deadline.</p>
',
		),
		array(
			'lang'       => 'en',
			'group'      => 'about',
			'title'      => 'About us',
			'slug'       => 'about',
			'menu_order' => 6,
			'excerpt'    => 'Who we are, why we run the festival and who is behind it.',
			'content'    => '
<p class="lede">Art and culture play a major role in accompanying people in all social, intellectual and cultural questions — they touch the person, their identity and their actions.</p>
<h2>Who we are</h2>
<p>When we hear about other cultures, the first thing that comes to mind is the ethnicity a person belongs to. In reality culture goes much further: we are all part of different cultural groups, and our cultural identity changes when the circumstances around us change.</p>
<p>Today many of us live in Hamburg and carry Arabic culture with us. That is why we created the Arab Culture Festival: to make the vision of many Arab people in the diaspora real — belonging.</p>
<h2>Our vision</h2>
<p>The Arab Culture Festival in Hamburg wants to bring people from different cultures together through art and culture. It wants to speak to mind and soul, and to show customs and traditions from the diversity of Arab cultures that are in harmony with democracy in Germany and that are not exclusive but connecting.</p>
<h2>The shared language</h2>
<p>Arabic and German are spoken at the festival. The shared language for everyone lies in art, in music and in dance — languages we all understand, whatever our mother tongue.</p>
<blockquote>“Art and culture opens every door.” — Ahmad AL Zaher</blockquote>
<h2>Our team</h2>
<p>The festival is carried by a team of artists, translators, technicians and volunteers — and by everyone who comes back every year.</p>
',
		),
		array(
			'lang'       => 'en',
			'group'      => 'contact',
			'title'      => 'Contact',
			'slug'       => 'contact',
			'menu_order' => 7,
			'excerpt'    => 'Write to us — we answer personally.',
			'content'    => '<p class="lede">Planning a workshop, a performance or a collaboration? Send us the key details — we answer personally.</p>',
		),
		array(
			'lang'       => 'en',
			'group'      => 'privacy',
			'title'      => 'Privacy',
			'slug'       => 'privacy',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p>Responsible for data processing within the meaning of the GDPR:</p>
<p>Arab Kulturfestival<br />Julius-Vosseler-Straße 106b<br />22527 Hamburg<br />Germany<br />Email: info@arab-kulturfestival.de</p>
<h2>Scope</h2>
<p>We process personal data only as far as necessary to provide a functioning website and our content and services. Processing generally happens only with the consent of our users.</p>
<h2>Contact form</h2>
<p>If you write to us via the contact form, your name, email address and message are stored to process the request. The legal basis is Art. 6 (1) a and b GDPR. The data is deleted as soon as it is no longer needed.</p>
<h2>External content</h2>
<p>Videos are embedded from YouTube (Google Ireland Limited). They load only after you have given explicit consent. Google then receives your IP address and playback data and may set cookies.</p>
<h2 id="datenschutzeinstellungen">Privacy settings</h2>
<p>You can change or withdraw your YouTube consent at any time. Open the <a href="#datenschutzeinstellungen" data-yd-consent-open>Datenschutzeinstellungen</a> (in German).</p>
<h2>Your rights</h2>
<p>You have the right to information, correction, deletion, restriction of processing, data portability and objection. Contact info@arab-kulturfestival.de.</p>
',
		),
		array(
			'lang'       => 'en',
			'group'      => 'cookies',
			'title'      => 'Cookies',
			'slug'       => 'cookies',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p class="lede">This page explains which cookies and local storage we use — and how to change your choice.</p>
<h2>No marketing or analytics cookies</h2>
<p>We do not use analytics, advertising or tracking cookies. There is no profiling and no sharing of usage data with third parties.</p>
<h2>YouTube</h2>
<p>Videos load only after you agree in the “YouTube-Videos?” dialog. Until then no connection to YouTube or Google is made and no thumbnails are loaded. Your choice is stored only in your browser (localStorage, key <code>yd_consent_youtube</code>). This information never leaves your device.</p>
<h2>Fonts</h2>
<p>The fonts are delivered with the website and loaded from our own server. No connection to Google Fonts or any other font service is made, and no IP address is transferred to third parties.</p>
<h2>Change or withdraw your choice</h2>
<p>Open the <a href="#datenschutzeinstellungen" data-yd-consent-open>Datenschutzeinstellungen</a> to allow or disable YouTube. Withdrawing consent removes players that are already loaded.</p>
',
		),
		array(
			'lang'       => 'en',
			'group'      => 'imprint',
			'title'      => 'Legal notice',
			'slug'       => 'legal-notice',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p><strong>Arab Kulturfestival</strong><br />Julius-Vosseler-Straße 106b<br />22527 Hamburg<br />Germany</p>
<p>Email: info@arab-kulturfestival.de<br />Phone: +49 178 1116220</p>
<h2>Responsible for content</h2>
<p>Ahmad AL Zaher — founder and artistic director of the Arab Culture Festival, founder and editor-in-chief of Arap Culture.</p>
<h2>Disclaimer</h2>
<p>The author assumes no liability for the topicality, correctness, completeness or quality of the information provided. Liability claims are excluded unless there is proven intentional or grossly negligent fault.</p>
<h2>Copyright</h2>
<p>The rights to all graphics, images, texts, audio and video sequences on this site belong to the respective author. Modification, reproduction or use in other electronic or printed publications is not permitted without express consent.</p>
',
		),

		// ------------------------------------------------------------ Arabic.
		array(
			'lang'       => 'ar',
			'group'      => 'home',
			'title'      => 'الرئيسية',
			'slug'       => 'ar',
			'menu_order' => 0,
			'content'    => '',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'festival',
			'title'      => 'المهرجان',
			'slug'       => 'festival',
			'menu_order' => 1,
			'excerpt'    => 'ثلاثة أيام وأربعة أشكال فنية ومدينة واحدة: مهرجان الثقافة العربية في هامبورغ.',
			'content'    => '
<p class="lede">منذ عام 2022 يدعو مهرجان الثقافة العربية في هامبورغ إلى اختبار تنوّع الثقافات العربية — بالموسيقى والدبكة والخط العربي والأدب والسينما. يُقام المهرجان في غولدبيكهاوس وجامعة هامبورغ وأماكن متغيّرة في المدينة، وتنظّمه مؤسسة آراب.</p>
<h2>مهرجان يجمع</h2>
<p>مهرجان الثقافة العربية حدث سنوي يعزّز التبادل الثقافي، ويكسر الصور النمطية، ويحتفي بفنون العالم العربي وموسيقاه وأدبه. العربية والألمانية هما لغتا المهرجان — وحين تعجز الكلمات تتولّى الموسيقى والرقص المهمة.</p>
<p>«أهلاً وسهلاً بكم» — بهذه الكلمات يبدأ برنامجنا كل عام، وهي موجّهة إلى كل من لديه فضول للمعرفة.</p>
<h2>برنامج 2024 — الشعر والموسيقى</h2>
<p>كانت دورة 2024 مكرّسة للشعر العربي. وعلى مدى ثلاثة أيام التقى الشعراء والموسيقيون وجمهور فضولي في غولدبيكهاوس.</p>
<ul>
<li><strong>الجمعة، مسرح الحوش:</strong> ورشة رقص شرقي للنساء، قهوة عربية، فرقة شرقية، رقص صوفي.</li>
<li><strong>السبت، القاعة:</strong> شعر مع موسيقى ورسم، فرقة نوستالجيا، ورشة دبكة، ودبكة مع موسيقى حيّة.</li>
<li><strong>الأحد، مسرح الحوش:</strong> برنامج أطفال، قهوة عربية، حكواتي مع بسام داود، فيلم عربي، وحوار مع المخرج محمد الشيخ.</li>
</ul>
<h2>برنامج 2023 — اللغة والتراث</h2>
<p>جمع مهرجان 2023 بين المعرض والموسيقى والرقص: نادي سينما عربي، افتتاح معرض مع علي شبلي، «اسمك بالعربي»، قراءة شعرية، وفرقة روح الشرق مع الدبكة.</p>
<h2>برنامج 2022 — البداية</h2>
<p>قدّمت الدورة الأولى الدبكة وفرقة شبلي وSTORYFELD هامبورغ وفرقة شرقية على المسرح — وكانت بداية تقليد سنوي.</p>
<h2>كراسات البرنامج</h2>
<p>البرامج الكاملة مع صور الفرق المشاركة متاحة بصيغة PDF.</p>
<p><a class="btn btn--sm" href="%PDF2024%">برنامج 2024 (PDF)</a> <a class="btn btn--sm btn--ghost" href="%PDF2022%">برنامج 2022 (PDF)</a></p>
',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'program',
			'title'      => 'البرنامج',
			'slug'       => 'program',
			'menu_order' => 2,
			'excerpt'    => 'أربعة أنماط ودائرة واحدة: الدبكة والموسيقى والخط العربي والأدب.',
			'content'    => '
<p class="lede">أربعة أنماط تشكّل عمود المهرجان الفقري، وتجمعها فكرة واحدة: ألّا تكتفي بمشاهدة الثقافة، بل أن تصبح جزءاً منها.</p>
<h2>الرقص الشعبي والدبكة</h2>
<p>حين يتشابك الناس بالأيدي ويتمايلون على وقع الموسيقى، تكون الدبكة قد حضرت. الرقص الشعبي الشامي لا يحتاج إلى دعوة: من يمسك الصف يصبح جزءاً من الدائرة — ولا حاجة إلى خبرة سابقة. الدورات المفتوحة مجانية، والتبرعات لدعم عملنا مرحّب بها.</p>
<h2>الموسيقى العربية</h2>
<p>العود والكمان والإيقاع والصوت: تعمل الموسيقى العربية عبر ألحانها ومقاماتها وأنماطها الإيقاعية. من الفرق الكلاسيكية إلى الفرق المعاصرة، لكلٍّ مكان.</p>
<h2>الخط العربي</h2>
<p>الحروف كشكل فني. في الورش والمعارض نعرض تاريخ الكتابة العربية وأشكالها والسكينة التي يسكبها الكتابة.</p>
<h2>الأدب والحكواتي</h2>
<p>قراءات وحوارات والحكاية التقليدية — الحكواتي — تُعيد الأدب العربي من المنفى إلى المسرح.</p>
<h2>للمدارس والجمعيات والفرق</h2>
<p>كل الأنماط قابلة للحجز كورشة — لصفوف المدارس والبيوت الثقافية والفرق والمهرجانات. راسلنا بالموعد المفضّل وحجم المجموعة.</p>
',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'events',
			'title'      => 'الفعاليات',
			'slug'       => 'events',
			'menu_order' => 3,
			'excerpt'    => 'كل الورش والحفلات وأيام المهرجان القادمة.',
			'content'    => '<p class="lede">كل المواعيد في مكان واحد. المشاركة في أنماط كثيرة مجانية — والتبرعات مرحّب بها.</p>',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'articles',
			'title'      => 'مقالات',
			'slug'       => 'articles',
			'menu_order' => 4,
			'excerpt'    => 'نصوص عن الدبكة والخط العربي والموسيقى والثقافة العربية في المنفى.',
			'content'    => '<p class="lede">مجلتنا: حكايات وخلفيات وأصوات من الثقافة العربية في هامبورغ وخارجها.</p>',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'press',
			'title'      => 'الصحافة',
			'slug'       => 'press',
			'menu_order' => 5,
			'excerpt'    => 'تقارير وإعلانات وبورتريهات عن مهرجان الثقافة العربية في هامبورغ.',
			'content'    => '
<p class="lede">تقارير وإعلانات وبورتريهات عن مهرجان الثقافة العربية — من بوابات الثقافة في هامبورغ، ومن شركائنا، ومن الصحافة.</p>
<h2>في الصحافة</h2>
<ul class="press-list">
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">STADTKULTUR HAMBURG</span><span class="press-item__date">17 أيار/مايو 2024</span></p>
<h3 class="press-item__title"><a href="https://www.stadtkultur-hh.de/2024/05/arab-kulturfestival-vom-24-bis-26-mai-2024-im-goldbekhaus" target="_blank" rel="noopener noreferrer">Arab Kulturfestival vom 24. bis 26. Mai 2024 im Goldbekhaus</a></h3>
<p class="press-item__text">يحتفل المهرجان بجمال الثقافة العربية وتنوّعها: ورشة رقص شرقي وفرقة شرقية يوم الجمعة، وشِعر وموسيقى عالمية ودبكة يوم السبت، وبرنامج للأطفال وحكواتي وفيلم يوم الأحد.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">STADTKULTUR HAMBURG</span><span class="press-item__date">7 أيلول/سبتمبر 2022</span></p>
<h3 class="press-item__title"><a href="https://www.stadtkultur-hh.de/2022/09/arab-kulturfestival-vom-9-bis-zum-11-september-2022-im-goldbekhaus" target="_blank" rel="noopener noreferrer">Arab Kulturfestival vom 9. bis zum 11. September 2022 im Goldbekhaus</a></h3>
<p class="press-item__text">الدورة الكبرى الأولى: حفلات مع Spirit of the East والفرقة الشرقية، ورقص مع OrientTanzEnsembleHamburg، وفيلم وبرنامج للأطفال، إضافة إلى معرض في QR134 ومعرض صغير للكتاب العربي.</p>
</li>
</ul>
<h2>الشركاء والبوابات الثقافية</h2>
<ul class="press-list">
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">Arab Filmclub</span></p>
<h3 class="press-item__title"><a href="https://arab-filmclub.de/arab-kulturfestival-%d9%85%d9%87%d8%b1%d8%ac%d8%a7%d9%86-%d8%a7%d9%84%d8%ab%d9%82%d8%a7%d9%81%d8%a9-%d8%a7%d9%84%d8%b9%d8%b1%d8%a8%d9%8a%d8%a9/" target="_blank" rel="noopener noreferrer">Arab Kulturfestival مهرجان الثقافة العربية</a></h3>
<p class="press-item__text">شريكنا «Arab Filmclub» يعلن المهرجان — بحفلات ورقص وفيلم ومعرض بالعربية والألمانية، مع برنامج للأطفال.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">kultur-hamburg.de</span><span class="press-item__date">دورة في Goldbekhaus</span></p>
<h3 class="press-item__title"><a href="https://www.kultur-hamburg.de/kurse_workshops/dabka-arabischer-tanz-755657" target="_blank" rel="noopener noreferrer">Dabka – Arabischer Tanz</a></h3>
<p class="press-item__text">دورة الدبكة المنتظمة في Goldbekhaus ضمن تقويم kultur-hamburg.de: كل خميس من 18:15 إلى 19:45 مع المدرّب أحمد.</p>
</li>
<li class="press-item">
<p class="press-item__meta"><span class="press-item__outlet">Goldbekhaus</span></p>
<h3 class="press-item__title"><a href="https://www.goldbekhaus.de/programm/veranstaltungen" target="_blank" rel="noopener noreferrer">الفعاليات في Goldbekhaus</a></h3>
<p class="press-item__text">مكاننا في فينترهوده: يرافق Goldbekhaus المهرجان كشريك منذ الدورة الأولى.</p>
</li>
</ul>
<h2>الملف الصحفي</h2>
<p>كراسات البرنامج الكاملة مع صور الفرق المشاركة متاحة بصيغة PDF. الصور والشعار متاحة عند الطلب.</p>
<p><a class="btn btn--sm" href="%PDF2024%">كراسة برنامج 2024 (PDF)</a> <a class="btn btn--sm btn--ghost" href="%PDF2022%">كراسة برنامج 2022 (PDF)</a></p>
<h2>للتواصل الصحفي</h2>
<p>Arap Culture · مهرجان الثقافة العربية<br />Julius-Vosseler-Straße 106b, 22527 Hamburg<br /><a href="mailto:info@arab-kulturfestival.de">info@arab-kulturfestival.de</a> · +49 178 1116220</p>
<p>للمقابلات والاعتمادات والمواد المصوّرة، راسلنا مع ذكر الوسيلة والموضوع والموعد النهائي.</p>
',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'about',
			'title'      => 'من نحن',
			'slug'       => 'about',
			'menu_order' => 6,
			'excerpt'    => 'من نحن، ولماذا ننظّم المهرجان، ومن يقف خلفه.',
			'content'    => '
<p class="lede">للفن والثقافة دور كبير في مرافقة الإنسان في كل شؤونه الاجتماعية والفكرية والثقافية — فهي تلامس الشخص نفسه وهويته وأفعاله.</p>
<h2>من نحن</h2>
<p>حين نسمع عن ثقافات أخرى، أول ما يخطر في بالنا هو العرق أو الإثنية التي ينتمي إليها الفرد. لكن الثقافة في الحقيقة أبعد من ذلك: كلّنا جزء من مجموعات ثقافية مختلفة، وهويتنا الثقافية تتطوّر وتتغيّر حين تتغيّر الظروف من حولنا.</p>
<p>اليوم يعيش كثير منّا في هامبورغ ويحمل الثقافة العربية معه. من هنا أنشأنا مهرجان الثقافة العربية: لتحقيق رؤية كثير من العرب في المهجر — الانتماء.</p>
<h2>رؤيتنا</h2>
<p>يريد مهرجان الثقافة العربية في هامبورغ أن يجمع الناس من ثقافات مختلفة عبر الفن والثقافة، وأن يخاطب العقل والروح، وأن يعرض عادات وتقاليد من تنوّع الثقافات العربية تتوافق مع الديمقراطية في ألمانيا، وهي ليست حصرية بل جامعة.</p>
<h2>اللغة المشتركة</h2>
<p>تُتحدَّث العربية والألمانية في المهرجان. أما اللغة المشتركة للجميع فهي الفن والموسيقى والرقص — لغات نفهمها جميعاً مهما كانت لغتنا الأم.</p>
<blockquote>«الفن والثقافة يفتحان كل باب.» — أحمد الظاهر</blockquote>
<h2>فريقنا</h2>
<p>يحمل المهرجان فريق من الفنانين والمترجمين والتقنيين والمتطوعين — وكل من يعود إلينا كل عام.</p>
',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'contact',
			'title'      => 'اتصل بنا',
			'slug'       => 'contact',
			'menu_order' => 7,
			'excerpt'    => 'راسلنا — نجيبك شخصياً.',
			'content'    => '<p class="lede">تخطط لورشة أو عرض أو تعاون؟ أرسل لنا المعلومات الأساسية — نجيبك شخصياً.</p>',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'privacy',
			'title'      => 'الخصوصية',
			'slug'       => 'privacy',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p>المسؤول عن معالجة البيانات وفق اللائحة العامة لحماية البيانات:</p>
<p>مهرجان الثقافة العربية<br />Julius-Vosseler-Straße 106b<br />22527 هامبورغ<br />ألمانيا<br />البريد: info@arab-kulturfestival.de</p>
<h2>نطاق المعالجة</h2>
<p>نعالج البيانات الشخصية فقط بالقدر اللازم لتقديم موقع يعمل ومحتوى وخدمات. وتتم المعالجة عادةً بموافقة المستخدمين.</p>
<h2>نموذج التواصل</h2>
<p>عند الكتابة عبر نموذج التواصل يُحفظ الاسم والبريد والرسالة لمعالجة الطلب، ويُحذف بعد انتهاء الحاجة إليه.</p>
<h2>المحتوى الخارجي</h2>
<p>تُضمَّن الفيديوهات من يوتيوب (Google Ireland Limited) ولا تُحمّل إلا بعد موافقتك الصريحة.</p>
<h2 id="datenschutzeinstellungen">إعدادات الخصوصية</h2>
<p>يمكنك تغيير موافقتك على يوتيوب أو سحبها في أي وقت عبر <a href="#datenschutzeinstellungen" data-yd-consent-open>Datenschutzeinstellungen</a> (بالألمانية).</p>
<h2>حقوقك</h2>
<p>لك الحق في الاطلاع والتصحيح والحذف وتقييد المعالجة ونقل البيانات والاعتراض. تواصل معنا على info@arab-kulturfestival.de.</p>
',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'cookies',
			'title'      => 'ملفات تعريف الارتباط',
			'slug'       => 'cookies',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p class="lede">توضح هذه الصفحة ملفات تعريف الارتباط والتخزين المحلي التي نستخدمها — وكيف تغيّر اختيارك.</p>
<h2>لا ملفات تتبّع أو إحصاء</h2>
<p>لا نستخدم ملفات تعريف ارتباط للتحليل أو الإعلان أو التتبّع، ولا ننشئ ملفات تعريفية ولا نشارك بيانات الاستخدام مع أطراف ثالثة.</p>
<h2>يوتيوب</h2>
<p>لا تُحمّل الفيديوهات إلا بعد موافقتك في نافذة «YouTube-Videos?». قبل ذلك لا يُنشأ أي اتصال بيوتيوب أو جوجل ولا تُحمّل صور مصغّرة. يُحفظ اختيارك محلياً في متصفحك فقط (localStorage، المفتاح <code>yd_consent_youtube</code>) ولا يغادر جهازك.</p>
<h2>الخطوط</h2>
<p>تُسلَّم الخطوط مع الموقع وتُحمَّل مباشرة من خادمنا. لا يتم أي اتصال بـ Google Fonts أو أي خدمة خطوط أخرى، ولا يُنقل عنوان IP إلى أي طرف ثالث.</p>
<h2>تغيير الاختيار أو سحبه</h2>
<p>افتح <a href="#datenschutzeinstellungen" data-yd-consent-open>Datenschutzeinstellungen</a> للسماح ليوتيوب أو تعطيله. عند سحب الموافقة تُزال المشغّلات المحمّلة.</p>
',
		),
		array(
			'lang'       => 'ar',
			'group'      => 'imprint',
			'title'      => 'بيانات الناشر',
			'slug'       => 'imprint',
			'menu_order' => 0,
			'hidden'     => true,
			'content'    => '
<p><strong>مهرجان الثقافة العربية</strong><br />Julius-Vosseler-Straße 106b<br />22527 هامبورغ<br />ألمانيا</p>
<p>البريد: info@arab-kulturfestival.de<br />الهاتف: +49 178 1116220</p>
<h2>المسؤول عن المحتوى</h2>
<p>أحمد الظاهر — مؤسس مهرجان الثقافة العربية ومديره الفني، ومؤسس ورئيس تحرير آراب كالتشر.</p>
<h2>إخلاء المسؤولية</h2>
<p>لا يتحمّل المؤلف أي ضمان بشأن حداثة المعلومات أو صحتها أو اكتمالها أو جودتها، وتُستثنى مطالبات المسؤولية إلا في حال الإهمال المُثبت.</p>
<h2>حقوق النشر</h2>
<p>حقوق جميع الرسومات والصور والنصوص والتسجيلات تعود إلى أصحابها، ولا يجوز تعديلها أو إعادة استخدامها دون موافقة صريحة.</p>
',
		),
	);
}
