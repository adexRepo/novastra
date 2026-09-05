# Novastra

Novastra is a small-business fresh cooking-ingredients e-commerce monolith for chicken, fish, vegetables, spices, and pantry goods. It is built with Next.js App Router, React, TypeScript, Tailwind CSS, shadcn/ui, Prisma, MariaDB/MySQL, and Auth.js. It includes a responsive storefront, product discovery, a device-local cart, server-validated checkout, customer orders, credentials-protected admin tools, image storage, payment/order state rules, SMTP email notifications, and Excel reporting.

## Requirements

- Node.js 22.13 or newer
- npm 11 or newer
- MariaDB 10.6+ or MySQL 8+
- An SMTP account

## Local setup

1. Copy `.env.example` to `.env` and fill the database, Auth.js, customer, admin, and SMTP values.
2. Install dependencies with `npm install`.
3. Generate Prisma Client with `npm run prisma:generate`.
4. Create a migration with `npx prisma migrate dev --name init`.
5. Seed development data with `npm run prisma:seed`.
6. Start the app with `npm run dev` and open `http://localhost:3000`.

Generate `AUTH_SECRET` with `npx auth secret`. Generate bcrypt hashes for `CUSTOMER_PASSWORD_HASH` and `ADMIN_PASSWORD_HASH` from their final production passwords; never deploy a plain customer password. The plain `ADMIN_PASSWORD` variable is read only by the development seed.

## Authentication

Google OAuth is temporarily disabled. Customers use the shared username/password configured with `CUSTOMER_USERNAME`, `CUSTOMER_PASSWORD_HASH`, `CUSTOMER_NAME`, and `CUSTOMER_EMAIL`. During local development only, leaving `CUSTOMER_PASSWORD_HASH` empty enables the default account `pelanggan` / `novastra123`. Production has no default password and requires a bcrypt hash.

Admins use a different username/password provider at `/admin/login`. Customer and admin roles are deliberately separate. Configure a strong bcrypt hash in `ADMIN_PASSWORD_HASH`; customer credentials cannot open the admin area.

This shared customer login is intended only as a temporary launch/testing mode: everyone using it sees the same order history. Replace it with per-customer accounts or re-enable OAuth before allowing unrelated public customers to order.

## Database and migrations

Prisma uses `DATABASE_URL` with its native MySQL/MariaDB engine. Development uses `prisma migrate dev`; production uses only:

`npm run prisma:migrate`

Do not use `prisma db push` against production. Back up the database before applying migrations.

## Owner dashboard and analytics

The protected `/admin` dashboard summarizes 30-day revenue, order volume, average order value, payment and cancellation rates, anonymous visitors, conversion, top products, and low stock. Storefront analytics save only a random browser identifier, public pathname, business date, and timestamp; they do not store an IP address, email, or customer identity. Apply the included Prisma migration before expecting visit data to appear.

## File storage

Product images accept JPEG, PNG, and WebP up to 2 MB. The server verifies the file signature and dimensions, generates a UUID filename, converts it to WebP, and stores only a relative public path in the database. Browser-provided filesystem paths are never used.

Local defaults:

- Public uploads: `public/uploads/`
- Private files: `storage/private/`
- Temporary files: `storage/tmp/`

Override them with `PUBLIC_UPLOAD_DIR`, `PRIVATE_UPLOAD_DIR`, and `TEMP_UPLOAD_DIR`. On cPanel these should resolve to persistent directories that survive application updates and restarts. The Node.js process needs read/write permission on these exact directories. Use the minimum permission needed for the application user; do not default to `chmod 777`.

## Email and WhatsApp

Set `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD`, and `SMTP_FROM`. Order creation commits before notification is attempted, so an SMTP failure cannot duplicate or roll back a valid order. `WHATSAPP_ADMIN_NUMBER` stores the international WhatsApp number for customer handoff links.

## Quality checks

Run before release:

```text
npm run lint
npm run typecheck
npm test
npm run build
```

Tests focus on integer-rupiah totals, shipping rules, state transitions, invalid quantities, and idempotent transitions. Integration testing should use an isolated MariaDB database and cover order transactions, stock deduction/restoration, ownership, admin authorization, and file/database cleanup.

## Rumahweb cPanel deployment

1. Confirm the hosting plan supports a Node.js Application with Node 22, MariaDB/MySQL, environment variables, and a persistent writable filesystem.
2. Create a database and least-privilege database user in cPanel. Import no production secrets into source control.
3. Upload or clone the project into a dedicated application root, for example `/home/CPANEL_USER/apps/novastra`. Do not use the public web root for private files.
4. In **Setup Node.js App**, choose Node 22, Production mode, the application root, the public domain, and `server.js` from the standalone build as the startup file.
5. Configure every variable from `.env.example` in the cPanel application environment. Use the final HTTPS domain for `AUTH_URL` and `NEXT_PUBLIC_APP_URL`.
6. Install dependencies, generate Prisma Client, apply migrations, and build:

   ```text
   npm ci
   npm run prisma:generate
   npm run prisma:migrate
   npm run build
   ```

7. Next.js emits `.next/standalone`. Copy `.next/static` to `.next/standalone/.next/static` and `public` to `.next/standalone/public`, or configure the cPanel startup wrapper to preserve those paths. Point the Node.js Application startup file at `.next/standalone/server.js`.
8. Create persistent public upload, private, and temp directories outside disposable release folders. Set their absolute paths in the environment and grant the application user read/write access only to those directories.
9. Restart the Node.js Application in cPanel. Check the app log, `/`, `/products`, `/login`, `/admin/login`, one image upload, and one SMTP message.
10. Test both customer and admin credentials, then restart the application after any environment change.

If the host cannot run a Next.js standalone server, Prisma's MariaDB driver, or Sharp, stop and confirm support with Rumahweb rather than weakening upload validation or switching to static export.

## Backup and restore

A complete Novastra backup is **database plus files**:

- MariaDB/MySQL dump
- `public/uploads/` or the configured public upload directory
- `storage/private/` or the configured private directory

Temporary files do not need backup. Keep database and filesystem backups from the same maintenance window. To restore: stop writes, restore the database, restore public and private files to the configured paths, verify ownership/permissions, run pending migrations, restart the app, and test representative product images and orders. Restoring only the database leaves broken image references.

## Troubleshooting

- **Database errors:** verify `DATABASE_URL`, database user permissions, and that migrations ran.
- **Customer login fails:** verify all four `CUSTOMER_*` variables and ensure `CUSTOMER_PASSWORD_HASH` is a bcrypt hash in production.
- **Images fail to save:** verify the configured path exists, is persistent, and is writable by the Node.js application user.
- **Mail fails:** verify the SMTP port, TLS mode (465 is implicit TLS), credentials, and sender policy.
- **Build fails in cPanel:** build locally or in CI using the same Node major version, then upload the standalone output and public assets.

## Repository guidance

`AGENTS.md` defines the standard engineering rules. `.agents/skills/novastra-fullstack/SKILL.md` is the reusable Codex skill for changes that cross UI, server logic, Prisma, auth, orders, payments, reporting, or filesystem storage.
