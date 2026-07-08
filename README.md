# BeaconCMS Next.js Restructure

This package restructures the original custom PHP CMS into a React + Next.js + Tailwind project.

## Stack

- Next.js App Router
- React
- TypeScript
- Tailwind CSS
- lucide-react icons

## Project Structure

- `app/` Next.js routes and page layouts
- `components/` reusable React components
- `components/admin/` rebuilt admin UI components
- `lib/` site config and temporary content data
- `types/` shared TypeScript types
- `public/assets/` copied static assets from the original CMS
- `public/uploads/` copied uploaded media from the original CMS
- `legacy-php/` original PHP MVC code retained as migration reference

## Important Migration Notes

The original project is not a React framework app. It is a custom PHP MVC CMS with:

- PHP controllers, models, and views
- PDO MySQL database access
- PHP sessions for admin login
- Apache `.htaccess` routing
- local filesystem media uploads
- installer-generated `config.php`

This package now includes a Next.js API/backend rebuild for the core PHP/MySQL content model.

## Backend/API Included

- MySQL connection pool in `lib/db.ts`
- Secure env validation in `lib/env.ts`
- JWT HTTP-only cookie auth in `lib/auth.ts`
- SQL schema in `database/schema.sql`
- Migration script in `scripts/migrate.mjs`
- Admin user creation script in `scripts/create-admin.mjs`
- Public content API
- Admin CRUD API
- Admin UI for login, dashboard, list, create, edit, and delete
- Form submission API
- Health check API

## API Endpoints

Public read:

- `GET /api/public/posts`
- `GET /api/public/posts/[slug]`
- `GET /api/public/pages`
- `GET /api/public/pages/[slug]`
- `GET /api/public/specialties`
- `GET /api/public/specialties/[slug]`
- `GET /api/public/consultants`
- `GET /api/public/consultants/[slug]`
- `GET /api/public/promotions`
- `GET /api/public/promotions/[slug]`
- `GET /api/public/forms`

Auth:

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`

Admin CRUD, requires login as `admin` or `editor`:

- `GET /api/admin/[collection]`
- `POST /api/admin/[collection]`
- `GET /api/admin/[collection]/[id]`
- `PUT /api/admin/[collection]/[id]`
- `DELETE /api/admin/[collection]/[id]`

Supported admin collections:

- `posts`
- `pages`
- `specialties`
- `consultants`
- `promotions`
- `forms`

Forms:

- `POST /api/forms/submit`

Media:

- `GET /api/media`
- `POST /api/media` returns `501` until persistent object storage is configured.

Health:

- `GET /api/health`

## Production Next Steps

1. Replace placeholder content in `lib/site.ts` with data from a real CMS or API.
2. Add media upload using persistent object storage.
3. Add spam protection, rate limiting, consent handling, and email/CRM connectors for forms.
4. Add dynamic frontend routes that consume `/api/public/...`.
5. Add schema only where the visible page content supports it.
6. Verify healthcare copy for Malaysian healthcare advertising and KKLIU expectations before production.

## Admin UI

Admin routes:

- `/admin/login`
- `/admin`
- `/admin/posts`
- `/admin/pages`
- `/admin/specialties`
- `/admin/consultants`
- `/admin/promotions`
- `/admin/forms`

The UI supports login, listing records, creating records, editing records, and deleting records through the Next.js API.

Security note: the UI guard improves the admin experience, but real protection is enforced by the API routes using the HTTP-only auth cookie. Keep `AUTH_SECRET` long, private, and unique in production.

## Persistent Upload Recommendation

Vercel serverless functions do not provide reliable persistent local storage. Files written to local disk may disappear between deployments or function instances.

Recommended option for this project:

- **Cloudflare R2** because the production stack already uses Cloudflare, it is S3-compatible, works well behind Cloudflare CDN, and avoids tying media persistence to Vercel function storage.

Good alternatives:

- **AWS S3** if the organisation already uses AWS.
- **DigitalOcean Spaces** for a simpler S3-compatible object store.
- **Supabase Storage** if the project later moves database/auth pieces into Supabase.

Avoid for production:

- Local `/public/uploads` writes on Vercel.
- Base64 file storage in MySQL.
- Public unauthenticated upload endpoints.

Recommended media flow:

1. Admin requests a signed upload URL from `/api/media`.
2. Browser uploads directly to R2/S3.
3. API stores the final media URL, MIME type, size, alt text, and uploader ID in the `media` table.
4. Public pages serve optimized remote images through Next.js image handling or the CDN URL.

## Local Development

```bash
npm install
npm run dev
```

## Build

```bash
npm run build
```

## Database Setup

Run the schema migration:

```bash
npm run db:migrate
```

Create or update an admin user:

```bash
ADMIN_USERNAME=admin ADMIN_EMAIL=admin@example.com ADMIN_PASSWORD="use-a-strong-password" npm run admin:create
```

## Vercel

Deploy this folder as the Vercel project root.

Set:

- `NEXT_PUBLIC_SITE_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`
- `AUTH_SECRET`

Use a managed MySQL provider reachable from Vercel. Do not use local filesystem uploads for production media.
