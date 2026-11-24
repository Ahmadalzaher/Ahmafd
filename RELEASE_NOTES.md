# v1.0.0 — SEO Release

**Date:** 2026-11-24

## Summary

Initial SEO release for Arab Kulturfestival event pages. Unifies event titles and enriches Event schema with a full postal address.

## Added

- Unified Rank Math title template for `yd_event` pages: `%title% %sep% Arab Kulturfestival`.
- Postal code `20251` added to the `Event → location → address` schema node for the Jahresabschlussfeier 2026 pages (DE/AR/EN).

## Changed

- `_yd_address` updated to `Heider Straße 1, 20251 Hamburg-Nord` on the three event translations.
- `yd_event-sitemap.xml` verified with all 28 event URLs.

## Fixed

- Stale legacy brand suffix (`Hamburger* mit Herz e.V.`) in event titles.
- Missing `postalCode` in Event schema for the year-end celebration pages.

## Verification

- All 28 event pages: HTTP 200, title, description, canonical, og:image, hreflang (de/en/ar/x-default), JSON-LD, noindex check, word count > 250.
- Live page re-fetched after WP-Optimize cache purge: schema confirms `postalCode: 20251`.

## Tag

`v1.0.0` → commit `25e3cb8`
