# Novastra agent standard

## Mission

Build and maintain Novastra as a small, production-minded Indonesian fresh cooking-ingredients store. The catalog covers chicken and meat, fish and seafood, vegetables and fruit, spices, and pantry needs. Favor code that is secure, direct, and easy to deploy on a cPanel Node.js application. The detailed product brief is `NOVASTRA.md` when present; this file defines day-to-day implementation expectations.

## Stack

- Next.js App Router, React, and strict TypeScript
- Tailwind CSS with shadcn/ui primitives and Lucide icons
- MariaDB/MySQL through Prisma
- Auth.js with separate hashed credentials for customers and admins
- Zod for every mutation boundary
- SMTP through the email service abstraction, ExcelJS reports, and server-controlled local storage

Do not add a separate API service, Redis, object storage, event buses, or repository layers without a demonstrated requirement.

## Working rules

1. Inspect the current route, related module, Prisma models, and existing tests before editing.
2. Keep database access and business rules on the server. Client components handle only interaction and presentation.
3. Recompute price, stock, subtotal, total, ownership, role, and status from trusted server data.
4. Use transactions for stock and order mutations. Make confirm, cancel, and payment transitions idempotent.
5. Store historical product name, SKU, and price snapshots on order items.
6. Resolve file paths from configured server roots plus generated IDs. Never accept or return arbitrary filesystem paths.
7. Keep customer and admin authentication separate. Customer credentials never grant admin access implicitly.
8. Add useful loading, empty, error, disabled, and success states for changed flows.
9. Preserve the fresh Novastra visual system: clean off-whites, leafy greens, small produce-red accents, crisp food photography, compact controls, restrained cards, and concise Indonesian copy. Fresh food must look hygienic and natural, never rustic-dark or artificially neon.
10. Prefer semantic HTML, visible focus states, labelled controls, keyboard operation, and layouts that work from 360px to 1440px.

## Definition of ready

Before handing off a change, run the relevant checks from `package.json`. For business changes, add or update tests that cover authorization, totals, stock concurrency/idempotency, and failure cleanup. Never suppress a lint, type, test, or build error to make a check pass.

## Agent skill

Use `.agents/skills/novastra-fullstack/SKILL.md` for feature work that crosses UI, server actions/routes, Prisma, authentication, orders, payments, reporting, or filesystem storage.

<!-- BEGIN:nextjs-agent-rules -->

# This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` (resolved from this file's directory; in monorepos the `next` package may not be visible from the repo root) before writing any code. Heed deprecation notices.

This block is written and re-added by `next dev` — verify at `node_modules/next/dist/server/lib/generate-agent-files.js`. Removing it from a diff only re-creates the uncommitted change; committing it with your work keeps the tree clean.

<!-- END:nextjs-agent-rules -->
