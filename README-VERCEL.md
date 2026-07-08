# BeaconCMS Vercel Package

This package is repacked for Vercel serverless PHP routing.

## Required Vercel Environment Variables

Set these in Vercel Project Settings > Environment Variables:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `SITE_URL`
- `SITE_NAME`
- `DEBUG` set to `false` for production

## Important Production Notes

- `install.php` is intentionally excluded from this production package.
- Vercel does not provide persistent local file storage. The existing media upload feature writes to `uploads/`, so uploaded files will not be reliable on Vercel serverless. Use persistent object storage before relying on admin media uploads in production.
- The database must be externally hosted and reachable from Vercel.
- Sessions in serverless can be inconsistent. Admin login should be tested carefully before using this as a live CMS.
- If this is for a real production healthcare site, test forms, admin login, media, sitemap, canonical URLs, and all public landing pages before pointing the production domain.

## Deploy

Upload this folder to Vercel or deploy it as the project root.
