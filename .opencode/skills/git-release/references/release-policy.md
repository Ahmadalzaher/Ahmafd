# Release Policy

## Versioning

Use semantic versioning (`MAJOR.MINOR.PATCH`):

- **PATCH** — bug fixes and small changes that do not alter behaviour.
- **MINOR** — new features or significant improvements, backwards compatible.
- **MAJOR** — breaking changes to public APIs or behaviour.

## Rules

1. Never change version files before the user approves the proposed bump.
2. Summarize merged changes since the previous tag, grouped by:
   - Added
   - Changed
   - Fixed
   - Removed
3. The changelog must be generated only after approval.
4. Tag format: `v<MAJOR>.<MINOR>.<PATCH>`.
5. Release notes must include the version, date, and the grouped change list.
6. Do not include internal-only changes in public release notes.
