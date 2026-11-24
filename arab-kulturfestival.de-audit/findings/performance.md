# Performance Findings — arab-kulturfestival.de

**Core Web Vitals status:** ⚠️ **Not measured** — PageSpeed Insights API returned `PSI rate limit exceeded (240 QPM / 25,000 QPD)` on every attempt. No CrUX field data available.
**All performance findings below are lab proxies** measured directly from the origin (TTFB, payload, resource counts), not Lighthouse scores.

---

## Measured proxies

| Metric | Value | Threshold | Verdict |
|---|---|---|---|
| TTFB (median, 3 runs) | 524–676 ms | < 800 ms good | ⚠️ Borderline |
| Homepage HTML (gzip) | 107 KB | < 100 KB | ⚠️ Heavy |
| Homepage images | 61 files, **3.47 MB** | < 1.5 MB | ❌ Heavy |
| Homepage JS | 11 files, 55 KB | — | ✅ OK |
| Homepage CSS | 9 files, 33 KB | — | ✅ OK |
| Inline `<script>` blocks | 12 | — | ⚠️ |
| Inline `<style>` blocks | 9 | — | ⚠️ |
| Font preloads | 2 (`fraunces`, `work-sans`) | — | ✅ Good |
| Server | Apache 2.4.69 + PHP 8.4.27 | — | Info |

## Finding 1 — Hero image is the LCP element and is unoptimised — **High**

```html
<img src="/wp-content/themes/yalla/assets/img/hero-dabke.jpg"
     alt="" width=∅ height=∅ loading=∅>
```

- **180 KB JPEG** — the single largest image on the homepage, and almost certainly the LCP element.
- **No `fetchpriority="high"`** — the browser discovers it late, competing with 9 CSS and 11 JS files.
- **No `width`/`height`** — layout shift (CLS) risk on mobile.
- **Empty `alt=""`** — also an accessibility defect.
- **JPEG, not WebP/AVIF** — the gallery already uses WebP, so the pipeline exists.

**Fix:**

```html
<img src="/wp-content/themes/yalla/assets/img/hero-dabke.webp"
     width="1600" height="900"
     fetchpriority="high" decoding="async"
     alt="Dabke-Tanzgruppe beim Arabischen Kulturfestival Hamburg">
```

Convert to WebP at 1600 px wide (~60–80 KB) and preload it:

```html
<link rel="preload" as="image" href="/.../hero-dabke.webp" fetchpriority="high">
```

Expected LCP improvement: 0.8–1.5 s on mobile.

## Finding 2 — 3.47 MB of images on the homepage — **High**

61 images, 45 of them in the gallery carousel at 80–102 KB each (1024×682 WebP). Even with `loading="lazy"`, a carousel typically requests all slides within the first viewport scroll, so most of the 3.4 MB lands on the initial load on mobile.

**Fix:**
- Serve 480 px and 768 px `srcset` variants for mobile; the current `sizes` declares `100vw` but the largest candidate is 1400 px.
- Limit the carousel to 8–12 curated photos, not 45.
- Add `content-visibility: auto` to off-screen slides.
- Target < 1.2 MB total for the initial mobile viewport.

## Finding 3 — TTFB is borderline — **Medium**

676 ms median on the homepage, 524–637 ms on inner pages, measured from a European vantage point. Mobile 4G adds 100–300 ms of RTT, pushing TTFB toward 1 s.

**Likely causes:** no full-page cache (response has `Cache-Control: max-age=0`), PHP 8.4 rendering every request, and a 107 KB HTML document.

**Fix:**
- Enable page caching (WP Rocket, LiteSpeed Cache, or Cloudflare APO).
- Set `Cache-Control: public, max-age=600, s-maxage=86400` for anonymous HTML.
- Add a CDN in front of the origin.
- Target TTFB < 400 ms.

## Finding 4 — `Cache-Control: max-age=0` on HTML — **Medium**

Every page response sends `max-age=0`, so no browser or intermediary cache can reuse it. This amplifies TTFB on repeat visits and prevents CDN edge caching.

## Finding 5 — Render-blocking CSS and inline blocks — **Low**

9 stylesheets in `<head>` plus 9 inline `<style>` blocks. WordPress plugin CSS is the usual cause. Combine and defer non-critical styles, and inline only the above-the-fold critical CSS.

## Finding 6 — 45-image gallery in the DOM — **Medium**

All 45 slides are in the initial HTML. Even lazy-loaded, they inflate the DOM to ~1,500 nodes and force the browser to decode images during scroll.

**Fix:** render 5 slides server-side and load the rest on interaction, or use a lighter carousel that virtualises slides.

## Finding 7 — Arabic font fallback — **Medium** (performance + rendering)

Only Latin subsets are preloaded:

```
fraunces-normal-latin.woff2
work-sans-normal-latin.woff2
```

Arabic pages (`/ar/`, all `angebot/*` AR pages) have no Arabic webfont, so the browser falls back to a system font. This causes FOUT and inconsistent metrics on RTL pages. Add an Arabic subset (`Noto Naskh Arabic` or `IBM Plex Sans Arabic`) and preload it only on `lang="ar"` pages.

## What cannot be assessed without PSI

- LCP, INP, CLS field values (75th percentile)
- Lighthouse Performance / Accessibility / Best Practices / SEO scores
- Third-party script impact (none detected beyond WordPress core)
- Real-device mobile behaviour

**Recommendation:** retry `pagespeed_check.py` with an API key (`--api-key`) or after the daily quota resets, then replace this section with field data.
