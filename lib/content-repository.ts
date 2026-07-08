import { execute, queryOne, queryRows } from '@/lib/db';
import { slugify } from '@/lib/slug';

export type Collection = 'posts' | 'pages' | 'specialties' | 'consultants' | 'promotions' | 'forms';

type CollectionConfig = {
  table: Collection;
  publicOrder: string;
  adminOrder: string;
  allowed: string[];
  publicSelect: string;
  publicWhere: string;
};

export const collectionConfig: Record<Collection, CollectionConfig> = {
  posts: {
    table: 'posts',
    publicOrder: 'created_at DESC',
    adminOrder: 'updated_at DESC',
    allowed: ['title', 'slug', 'content', 'excerpt', 'featured_image', 'status', 'seo_title', 'seo_description', 'seo_keywords', 'og_image'],
    publicSelect: '*',
    publicWhere: "status = 'published'"
  },
  pages: {
    table: 'pages',
    publicOrder: 'sort_order ASC',
    adminOrder: 'sort_order ASC',
    allowed: ['title', 'slug', 'content', 'template', 'status', 'sort_order', 'parent_id', 'seo_title', 'seo_description', 'og_image'],
    publicSelect: '*',
    publicWhere: "status = 'published'"
  },
  specialties: {
    table: 'specialties',
    publicOrder: 'sort_order ASC',
    adminOrder: 'sort_order ASC',
    allowed: ['name', 'slug', 'description', 'icon', 'status', 'sort_order'],
    publicSelect: '*',
    publicWhere: "status = 'published'"
  },
  consultants: {
    table: 'consultants',
    publicOrder: 'c.sort_order ASC',
    adminOrder: 'sort_order ASC',
    allowed: ['name', 'slug', 'photo', 'specialty_id', 'qualifications', 'experience', 'bio', 'clinic_hours', 'contact_number', 'email', 'booking_link', 'status', 'sort_order', 'seo_title', 'seo_description'],
    publicSelect: 'c.*, s.name AS specialty_name, s.slug AS specialty_slug',
    publicWhere: "c.status = 'published'"
  },
  promotions: {
    table: 'promotions',
    publicOrder: 'start_date DESC, created_at DESC',
    adminOrder: 'updated_at DESC',
    allowed: ['title', 'slug', 'description', 'featured_image', 'start_date', 'end_date', 'status', 'seo_title', 'seo_description'],
    publicSelect: '*',
    publicWhere: "status = 'published'"
  },
  forms: {
    table: 'forms',
    publicOrder: 'created_at DESC',
    adminOrder: 'updated_at DESC',
    allowed: ['title', 'shortcode', 'fields_json', 'settings_json', 'status'],
    publicSelect: 'id, title, shortcode, fields_json, settings_json',
    publicWhere: "status = 'active'"
  }
};

function publicFromClause(collection: Collection) {
  if (collection === 'consultants') {
    return 'consultants c LEFT JOIN specialties s ON c.specialty_id = s.id';
  }

  return collectionConfig[collection].table;
}

export async function listPublic(collection: Collection, limit = 50) {
  const config = collectionConfig[collection];
  return queryRows(
    `SELECT ${config.publicSelect}
     FROM ${publicFromClause(collection)}
     WHERE ${config.publicWhere}
     ORDER BY ${config.publicOrder}
     LIMIT :limit`,
    { limit }
  );
}

export async function getPublicBySlug(collection: Collection, slug: string) {
  const config = collectionConfig[collection];
  const slugColumn = collection === 'consultants' ? 'c.slug' : collection === 'forms' ? 'shortcode' : 'slug';

  return queryOne(
    `SELECT ${config.publicSelect}
     FROM ${publicFromClause(collection)}
     WHERE ${config.publicWhere} AND ${slugColumn} = :slug
     LIMIT 1`,
    { slug }
  );
}

export async function listAdmin(collection: Collection) {
  const config = collectionConfig[collection];
  return queryRows(`SELECT * FROM ${config.table} ORDER BY ${config.adminOrder}`);
}

export async function getAdminById(collection: Collection, id: number) {
  const config = collectionConfig[collection];
  return queryOne(`SELECT * FROM ${config.table} WHERE id = :id LIMIT 1`, { id });
}

export function sanitizeCollectionPayload(collection: Collection, payload: Record<string, unknown>) {
  const config = collectionConfig[collection];
  const output: Record<string, unknown> = {};

  for (const key of config.allowed) {
    if (payload[key] !== undefined) {
      output[key] = payload[key];
    }
  }

  if (!output.slug && typeof output.title === 'string') {
    output.slug = slugify(output.title);
  }

  if (!output.slug && typeof output.name === 'string') {
    output.slug = slugify(output.name);
  }

  if (!output.shortcode && typeof output.title === 'string' && collection === 'forms') {
    output.shortcode = slugify(output.title);
  }

  if (collection === 'forms') {
    for (const key of ['fields_json', 'settings_json']) {
      if (output[key] && typeof output[key] !== 'string') {
        output[key] = JSON.stringify(output[key]);
      }
    }
  }

  return output;
}

export async function createAdmin(collection: Collection, payload: Record<string, unknown>) {
  const config = collectionConfig[collection];
  const data = sanitizeCollectionPayload(collection, payload);
  const keys = Object.keys(data);

  if (keys.length === 0) {
    throw new Error('No valid fields provided.');
  }

  const columns = keys.map((key) => `\`${key}\``).join(', ');
  const placeholders = keys.map((key) => `:${key}`).join(', ');
  const result = await execute(`INSERT INTO ${config.table} (${columns}) VALUES (${placeholders})`, data);
  return getAdminById(collection, result.insertId);
}

export async function updateAdmin(collection: Collection, id: number, payload: Record<string, unknown>) {
  const config = collectionConfig[collection];
  const data = sanitizeCollectionPayload(collection, payload);
  const keys = Object.keys(data);

  if (keys.length === 0) {
    throw new Error('No valid fields provided.');
  }

  const setClause = keys.map((key) => `\`${key}\` = :${key}`).join(', ');
  await execute(`UPDATE ${config.table} SET ${setClause} WHERE id = :id`, { ...data, id });
  return getAdminById(collection, id);
}

export async function deleteAdmin(collection: Collection, id: number) {
  const config = collectionConfig[collection];
  await execute(`DELETE FROM ${config.table} WHERE id = :id`, { id });
  return { deleted: true };
}
