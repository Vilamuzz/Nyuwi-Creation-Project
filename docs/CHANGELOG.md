# Changelog

## 2026-09-15 — Phase 3: Auth (Session 2)
**Agent:** Cursor / Claude Code
**Goal:** Implement signup flow

### Changed
- `app/signup/page.tsx` — created signup form
- `lib/auth.ts` — added `hashPassword()` helper
- `prisma/schema.prisma` — no changes

### Added
- `lib/auth.test.ts` — 3 tests for password hashing

### Verified
- ✅ `pnpm typecheck` passes
- ✅ `pnpm test` — 12/12 passing
- ⚠️ Manual test: signup works, but no email confirmation yet

### Next
- Login page
- Session middleware

### Notes
- Chose bcrypt over argon2 for [reason]
