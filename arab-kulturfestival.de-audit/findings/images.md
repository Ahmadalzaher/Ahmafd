# Image SEO Findings — arab-kulturfestival.de

**Images across 91 pages:** 618
**Missing alt text:** 184 (29.8%) on 40 pages
**Missing width/height:** 274 (44.3%)
**Lazy-loaded:** 488 (79%)
**Broken images:** 0
**Homepage image weight:** 3.47 MB across 61 files

---

## Finding 1 — 184 images have no alt text — **High**

30% of all images ship with `alt=""` or no `alt` attribute. Worst pages:

| Missing / total | Page |
|---|---|
| 10 / 12 | `/category/arab-kulturfestival/` |
| 10 / 12 | `/category/magazin/` |
| 8 / 62 | `/` , `/en/`, `/ar/` |
| 4 / 6 | 12 × `/angebot/*` service pages |
| 4 / 6 | every article page (20 posts) |

The recurring offenders are theme assets, not editor-uploaded media:

```
/wp-content/themes/yalla/assets/img/hero-dabke.jpg
/wp-content/themes/yalla/assets/img/stage-group.jpg
/wp-content/themes/yalla/assets/img/calligraphy.jpg
/wp-content/themes/yalla/assets/img/music.jpg
/wp-content/themes/yalla/assets/img/community-circle.jpg
/wp-content/themes/yalla/assets/img/moves/dabke-step-1..6.svg
```

Because these live in the theme templates, fixing them once fixes 40 pages.

**Recommended alt text:**

| File | Alt |
|---|---|
| `hero-dabke.jpg` | `Dabke-Tanzgruppe beim Arabischen Kulturfestival Hamburg` |
| `stage-group.jpg` | `Ensemble auf der Bühne des Arabischen Kulturfestivals` |
| `calligraphy.jpg` | `Arabische Kalligrafie — Feder und Tinte auf Papier` |
| `music.jpg` | `Oud und Percussion bei einer arabischen Musiksession` |
| `community-circle.jpg` | `Gemeinschaftskreis beim Arabischen Kulturfestival` |
| `dabke-step-1.svg` … `-6.svg` | `Dabke-Schritt 1` … `Dabke-Schritt 6` (diagram) |

Decorative images may keep `alt=""` **plus** `role="presentation"`, but the hero and stage photos are informative.

## Finding 2 — The wrong image is used on the music page — **High**

`/angebot/arabische-musik/` (and its EN/AR equivalents) shows `calligraphy.jpg` **twice** alongside `music.jpg`:

```
calligraphy.jpg   ×2
music.jpg         ×1
community-circle.jpg ×1
```

A calligraphy photo on the Arabic music service page is a relevance and UX defect. It also means the image alt (once added) will describe the wrong subject.

**Fix:** replace with a music-specific photo (the gallery has concert shots such as `akf-day1-*`).

## Finding 3 — Gallery alt text is auto-numbered — **Medium**

45 gallery images use:

```
alt="Bilder vom Arab Kulturfestival — 34"
alt="Bilder vom Arab Kulturfestival — 35"
```

This is better than empty alt (it is at least descriptive), but numbering adds no semantic value. Google Image Search cannot distinguish the images.

**Fix:** describe the subject — `Dabke-Auftritt auf der Bühne, Tag 1`, `Publikum beim Workshop`, etc. If the gallery is purely decorative, `alt=""` is acceptable, but then remove the numbering.

## Finding 4 — 274 images lack width/height — **Medium**

44% of images have no intrinsic dimensions, which causes layout shift (CLS). This includes the hero and all theme JPEGs.

**Fix:** add `width`/`height` to every `<img>` (CSS can still override the rendered size). For responsive images, set the aspect ratio with `aspect-ratio` in CSS as a backstop.

## Finding 5 — Oversized images on the homepage — **High**

Top payloads:

| Size | File | Dimensions | Loading |
|---|---|---|---|
| 180 KB | `hero-dabke.jpg` | unknown | none |
| 102 KB | `akf-day1-45-1024x682.webp` | 1024×682 | lazy |
| 96 KB | `akf-day1-25-1024x682.webp` | 1024×682 | lazy |
| 95 KB | `akf-day1-24-1024x682.webp` | 1024×682 | lazy |
| 94 KB | `akf-day1-26-682x1024.webp` | 682×1024 | lazy |

The WebP variants exist and are correctly generated, but the `srcset` stops at 1400 px and mobile devices still pick the 1024 px file because `sizes="auto, (max-width: 1024px) 100vw, 1024px"` resolves large.

**Fix:** add 480 w and 768 w candidates, and reduce the gallery to 8–12 images (see `performance.md`).

## Finding 6 — No `ImageObject` schema for key images — **Low**

`Article.image` and `Event.image` point to theme placeholders, not the actual article/event photo. Add an `ImageObject` with `url`, `width`, `height`, and `caption` for the primary image on articles and events — this feeds Google Discover and image rich results.

## Finding 7 — Filenames are already descriptive — **✅ Good**

`hero-dabke.jpg`, `akf-day1-34.webp`, `calligraphy.jpg` are readable. No `IMG_2034.JPG` problems. Keep this convention.

## Fix order

1. Theme templates: add alt + dimensions to the 5 shared JPEGs and 6 SVGs (fixes ~120 instances).
2. Replace `calligraphy.jpg` on the music pages.
3. Hero: convert to WebP, add dimensions, `fetchpriority="high"`, real alt.
4. Add mobile `srcset` candidates and trim the gallery to 12 images.
5. Gallery alt text: describe subjects instead of numbering.
