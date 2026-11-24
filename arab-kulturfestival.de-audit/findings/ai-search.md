# AI Search Readiness (GEO / Agentic) — arab-kulturfestival.de

**Agentic score:** 6 checks pass / 4 informational / 6 not applicable
**Assessment date:** 2026-11-08
**Checked against:** Lighthouse Agentic Browsing rules, 2026-09-23

---

## Scorecard

| Priority | Check | Status |
|---|---|---|
| P0 | Server-rendered content | ✅ pass |
| P0 | robots.txt reachable | ✅ pass |
| P0 | Deliberate robots.txt groups for AI agents | ℹ️ info — no named groups |
| P1 | User-triggered agents allowed | ✅ pass |
| P1 | Real 404 for unknown URLs | ✅ pass |
| P1 | `Content-Signal` preferences | ℹ️ info — missing |
| P1 | `llms.txt` | ℹ️ info — 404 |
| P1 | Markdown delivery | ℹ️ info — missing |
| P2 | WebMCP tools | ℹ️ info — none |
| P2 | OAuth discovery documents | ✅ pass (inherited) |
| P3 | `ai-catalog.json`, A2A card, UCP | — not applicable |

---

## What works

- **Server-rendered HTML.** 94+ words of primary content are present without JavaScript, so GPTBot, ClaudeBot, PerplexityBot, and Bingbot all see the real page.
- **robots.txt allows AI crawlers.** No AI user-agent is blocked; the `*` group permits crawling except WooCommerce logs and admin paths.
- **Clean 404 handling.** Unknown URLs return a real 404, so agents do not ingest soft-404 junk.
- **Semantic HTML.** `<main>`, headings, `<article>`, breadcrumbs, and JSON-LD give agents a parseable structure.

## Finding 1 — No `llms.txt` — **Medium**

`https://arab-kulturfestival.de/llms.txt` returns **404**.

`llms.txt` is a plain-text map for LLMs: site purpose, key pages, and canonical descriptions. For a cultural festival with event dates, programme pages, and service pages, this is a cheap, high-leverage file.

**Suggested content:**

```markdown
# Arab Kulturfestival Hamburg

> Arabisches Kulturfestival in Hamburg: Dabke, arabische Musik,
> Kalligrafie und Literatur. Termine, Programm und Workshops.

## Veranstaltungen
- [Programm](https://arab-kulturfestival.de/programm/): Alle Termine 2026
- [Events](https://arab-kulturfestival.de/events/): Kommende Veranstaltungen

## Angebote
- [Dabke](https://arab-kulturfestival.de/angebot/folkloretanz-dabke/): offener Volkstanz-Kurs
- [Arabische Musik](https://arab-kulturfestival.de/angebot/arabische-musik/)
- [Kalligrafie](https://arab-kulturfestival.de/angebot/kalligrafie/)
- [Literatur & Hakawati](https://arab-kulturfestival.de/angebot/literatur-hakawati/)

## Über uns
- [Über uns](https://arab-kulturfestival.de/ueber-uns/)
- [Presse](https://arab-kulturfestival.de/presse/)
- [Kontakt](https://arab-kulturfestival.de/kontakt/)

## Optional
- [Impressum](https://arab-kulturfestival.de/impressum/)
- [Datenschutz](https://arab-kulturfestival.de/datenschutz/)
```

## Finding 2 — No `Content-Signal` declaration — **Low**

`robots.txt` has no `Content-Signal` line (the emerging `search=yes, ai-train=no, ai-input=yes` convention). Without it, AI crawlers fall back to their own defaults.

**Fix** — add to the top of `robots.txt`:

```
Content-Signal: search=yes, ai-train=yes, ai-input=yes
```

Choose the policy deliberately. For a publicly funded cultural organisation, `ai-train=yes` maximises reach; `ai-train=no` protects content. Either is defensible — the point is to declare it.

## Finding 3 — No Markdown delivery — **Low**

The site serves HTML only. Agents that request `Accept: text/markdown` receive HTML. Adding a `/page.md` variant or content negotiation is optional for a small site; `llms.txt` covers most of the benefit.

## Finding 4 — No `WebMCP` tools — **Not applicable**

WebMCP exposes forms and transactions to agents (e.g. "register for the Dabke course"). The site has no online booking, so there is nothing to expose yet. Revisit if registration is added.

## Finding 5 — Citability — **Medium**

AI answer engines cite passages, not pages. The site's strengths and gaps:

**Strengths**
- Specific facts: venue names, addresses, dates, times, instructor names.
- Clear H2/H3 structure with question-like headings.
- German/Arabic original copy, not translated boilerplate.

**Gaps**
- No concise, self-contained answer paragraphs near the top of key pages. The homepage opens with a poetic H1 (`Wo arabische Kultur in Bewegung kommt`) but no one-sentence definition ("Das Arabische Kulturfestival Hamburg ist ein …").
- No FAQ blocks with direct Q→A pairs.
- No `dateModified` visible to users, so freshness is hard for an LLM to judge.
- Event pages do not state price/registration in the first 100 words.

**Fix:** add a 40–60 word "definition paragraph" at the top of the homepage, `/festival/`, `/programm/`, and each `/angebot/` page. Example:

> Das Arabische Kulturfestival Hamburg ist eine Veranstaltungsreihe mit Dabke-Kursen, arabischen Musikabenden, Kalligrafie-Workshops und Lesungen. Die Teilnahme ist kostenlos, eine Anmeldung ist nicht erforderlich.

This is exactly the passage an AI Overview or ChatGPT answer would quote.

## Finding 6 — Brand mention signals — **Info**

`sameAs` covers Instagram, Facebook, and YouTube. No Wikipedia/Wikidata entity, no Google Business Profile link in schema, no press page citations in structured data. For a local cultural brand, a Wikidata item and consistent NAP across listings would strengthen entity recognition in AI answers.
