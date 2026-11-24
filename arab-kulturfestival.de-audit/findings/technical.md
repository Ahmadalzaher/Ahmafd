# Technical SEO Findings — arab-kulturfestival.de

**Audit date:** 2026-11-08
**Pages crawled:** 91 (100% of sitemap URLs)
**Crawl status:** 91/91 HTTP 200, 0 errors, 0 timeouts

---

## What works

| Check | Result |
|---|---|
| HTTPS | Valid, `http://` → `https://` 301 |
| Host canonicalization | `https://www.` → non-www 301 |
| robots.txt | 200, allows crawling, declares sitemap |
| XML sitemap index | 200, 5 child sitemaps, 91 URLs, all valid XML |
| Sitemap freshness | `lastmod` 2026-10-28 → 2026-11-06 |
| Canonical tags | 91/91 present, all self-referencing |
| Meta robots | 91/91 `index, follow` — no accidental noindex |
| Unknown URLs | Return real 404 (not soft-404) |
| Trailing-slash handling | Consistent, no duplicate variants |
| Server-rendered HTML | Content present without JavaScript (94+ words in raw HTML) |
| Language attributes | `lang="de|en|ar"` correct on all 91, `dir="rtl"` on Arabic |
| Sitemap ↔ site consistency | All sitemap URLs reachable and indexable |

## Findings

### 1. Legacy and duplicate URLs are still indexable — **High**
Nine legacy/duplicate page pairs are live, self-canonical, and in the sitemap. Each pair competes for the same query and splits internal signals.

| Duplicate / legacy URL | Canonical target it should point to | Evidence |
|---|---|---|
| `/2147-2/` | `/impressum/` | Both `Impressum`; 486 vs 298 words |
| `/privacy-policy/` | `/datenschutz/` | 3,378-word old Datenschutz vs current page |
| `/festivalprogramm/` | `/programm/` | Same programme content |
| `/kulturartikel/` | `/artikel/` | Duplicate H1, 206 words |
| `/arab-kulturfestival-2/` | `/` or `/festival/` | 2022 archive page |
| `/arabische-musik/` | `/angebot/arabische-musik/` | Identical title `Arabische Musik - Arab Kulturfestival` |
| `/arabische-musik-2/` | `/angebot/arabische-musik/` | Duplicate body |
| `/kalligrafie/` | `/angebot/kalligrafie/` | Identical title `Kalligrafie - Arab Kulturfestival` |
| `/kalligrafie-2/` | `/angebot/kalligrafie/` | Duplicate body |
| `/arabische-kultur-im-exil/` + `-2/` | One canonical article | 74% shingle similarity |

**Recommendation:** 301 the legacy slug to the current slug, then remove it from the sitemap. Do not `noindex` and leave it live — redirect preserves link equity.

### 2. Security headers missing — **Medium**
Only `Permissions-Policy` is set. Missing:

```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: (report-only first)
```

`X-Powered-By: PHP/8.4.27` and `Server: Apache/2.4.69 (Unix)` expose exact stack versions. Remove `X-Powered-By` (`expose_php = Off`) and trim the `Server` token.

No HTTPS/HSTS issue affects ranking directly, but missing HSTS leaves the first request open to downgrade and is a trust/security audit item.

### 3. Internal redirects from `/uncategorized/...` — **Low**
5 internal links still point at the old category path and 301 to the final URL:

```
/uncategorized/arab-kulturfestival/            → /
/uncategorized/arabische-kultur-im-exil/       → /arabische-kultur-im-exil/
/uncategorized/die-arabische-kultur-westliche-vorurteile/ → /die-arabische-kultur-westliche-vorurteile/
/uncategorized/die-dabke-braucht-keine-einladung/ → /die-dabke-braucht-keine-einladung/
/uncategorized/kalligrafie/                    → /kalligrafie/
```

**Fix:** update the menu/related-posts template to the final URLs. Each hop costs crawl budget and dilutes anchor value.

### 4. Orphan and unreachable pages — **Medium**
- **19 pages have zero inbound internal links** (orphans), including all 12 `/angebot/*` service pages, `/festivalprogramm/`, `/kulturartikel/`, `/newsletter/`, `/privacy-policy/`, `/2147-2/`, and both category archives.
- **27 pages are unreachable from the homepage** by following links (depth graph).
- Max click depth is 4; the deepest linked nodes are ICS download endpoints.

The `/angebot/` cluster is the commercial core of the site (music, calligraphy, dabke, literature lessons) and currently cannot be discovered by crawling from the homepage.

**Fix:** add a services block to the homepage and to `/festival/`, and link `/angebot/` from the main navigation or footer.

### 5. Sitemap contains non-content URLs — **Low**
The sitemap mixes 91 URLs across posts (20), pages (38), events (19), services (12), and 2 category archives. Category archives carry no unique value and add duplicate listing pages.

**Fix:** exclude `category-sitemap.xml` from the index, or noindex category archives.

### 6. Hreflang reciprocity broken on 18 links — **High**
All 91 pages emit hreflang, but 18 targets do not link back. Full detail in `findings/hreflang.md`.

### 7. WordPress / plugin surface — **Info**
`/wp-json/` is publicly readable (1.4 MB response) and exposes users, post types, and plugin routes. `?author=1` enumeration is typically open on this stack. Not a ranking issue; harden if the site is a target.

---

## Crawl budget summary

| Metric | Value |
|---|---|
| URLs in sitemap | 91 |
| Crawled | 91 |
| HTTP 200 | 91 (100%) |
| Non-200 | 0 |
| Internal links found | 104 unique |
| Broken internal links | 0 |
| Broken images | 0 |
| Internal redirects | 5 |
| Orphan pages | 19 |
| Median TTFB | 524–676 ms |
