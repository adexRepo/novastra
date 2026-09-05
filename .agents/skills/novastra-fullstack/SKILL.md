---
name: novastra-fullstack
description: Implement or review Novastra e-commerce features across Next.js UI, server boundaries, Prisma, authentication, orders, payments, reports, and cPanel filesystem storage. Use for changes that affect a customer or admin business flow; do not use for copy-only or isolated visual tweaks.
---

# Novastra full-stack

Deliver the smallest complete business slice: interface, trusted server mutation/query, authorization, persistence, failure states, and focused tests. Follow the repository `AGENTS.md` and the Novastra product brief.

Treat Novastra as a fresh cooking-ingredients business. Product names, units, cold-chain handling, stock visibility, photography, category design, and customer copy should fit chicken/meat, seafood, produce, spices, and pantry goods.

## Choose the boundary

- Prefer Server Components for reads and Server Actions for authenticated form mutations.
- Use Route Handlers for Auth.js, file streaming/upload, integration callbacks, contact submission, and generated downloads.
- Keep Prisma, filesystem APIs, secrets, and authoritative calculations out of client modules.
- Put reusable business rules in a narrow module service; do not add repositories or framework layers without a concrete need.

## Protect business invariants

- Read the acting user from the server session. Never accept customer/admin IDs or roles from the browser.
- Reload products inside the order transaction. Reject inactive products, stale prices, or quantities above available stock.
- Represent money as integer rupiah at UI boundaries and Prisma Decimal in persistence; centralize formatting.
- Treat order, stock, and payment transitions as a state machine. Make repeated submissions safe.
- Preserve order-item snapshots when products change.
- Check ownership for every customer order read and mutation; require an admin for every admin operation.

## Handle files safely

Validate image signature, MIME, extension compatibility, size, and dimensions. Generate a UUID filename under the configured category root. Never use a browser-provided path. For replacement: save the new file, commit database metadata, then best-effort remove the old file. Remove the new file when the database operation fails.

## Finish the slice

Include responsive and accessible UI states, safe Indonesian errors, and tests aimed at the real risk. Run lint, typecheck, tests, and the production build when available. For schema changes, use Prisma migrations rather than production schema push.
