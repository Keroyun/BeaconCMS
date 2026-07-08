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

This package converts the deploy structure and public frontend shell, but it does not fully rebuild the CMS admin backend.

## Production Next Steps

1. Replace placeholder content in `lib/site.ts` with data from a real CMS or API.
2. Decide backend strategy:
   - keep PHP backend separately and expose API endpoints, or
   - rebuild admin CMS in Next.js with a database ORM, or
   - connect to WordPress/headless CMS.
3. Implement real form submission with spam protection, consent handling, and no sensitive health data in analytics.
4. Add dynamic routes for doctor, specialty, promotion, and blog detail pages.
5. Add schema only where the visible page content supports it.
6. Verify healthcare copy for Malaysian healthcare advertising and KKLIU expectations before production.

## Local Development

```bash
npm install
npm run dev
```

## Build

```bash
npm run build
```

## Vercel

Deploy this folder as the Vercel project root.

Set:

- `NEXT_PUBLIC_SITE_URL`

Use additional environment variables only after the backend/API strategy is confirmed.
