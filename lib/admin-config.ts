export type AdminField = {
  name: string;
  label: string;
  type: 'text' | 'textarea' | 'select' | 'number' | 'date' | 'email' | 'url' | 'json';
  options?: string[];
  help?: string;
};

export type AdminCollection = 'posts' | 'pages' | 'specialties' | 'consultants' | 'promotions' | 'forms';

export const adminCollections: Array<{
  key: AdminCollection;
  label: string;
  description: string;
  titleField: string;
  statusField?: string;
}> = [
  { key: 'posts', label: 'Posts', description: 'Health articles and blog content.', titleField: 'title', statusField: 'status' },
  { key: 'pages', label: 'Pages', description: 'Reusable website pages.', titleField: 'title', statusField: 'status' },
  { key: 'specialties', label: 'Specialties', description: 'Clinical service and specialty pages.', titleField: 'name', statusField: 'status' },
  { key: 'consultants', label: 'Consultants', description: 'Doctor profile records.', titleField: 'name', statusField: 'status' },
  { key: 'promotions', label: 'Promotions', description: 'Packages and campaign landing content.', titleField: 'title', statusField: 'status' },
  { key: 'forms', label: 'Forms', description: 'Lead capture form definitions.', titleField: 'title', statusField: 'status' }
];

export const adminFields: Record<AdminCollection, AdminField[]> = {
  posts: [
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'slug', label: 'Slug', type: 'text' },
    { name: 'status', label: 'Status', type: 'select', options: ['draft', 'published', 'archived'] },
    { name: 'excerpt', label: 'Excerpt', type: 'textarea' },
    { name: 'content', label: 'Content', type: 'textarea' },
    { name: 'featured_image', label: 'Featured Image URL', type: 'url' },
    { name: 'seo_title', label: 'SEO Title', type: 'text' },
    { name: 'seo_description', label: 'SEO Description', type: 'textarea' },
    { name: 'seo_keywords', label: 'SEO Keywords', type: 'text' },
    { name: 'og_image', label: 'OG Image URL', type: 'url' }
  ],
  pages: [
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'slug', label: 'Slug', type: 'text' },
    { name: 'status', label: 'Status', type: 'select', options: ['draft', 'published'] },
    { name: 'template', label: 'Template', type: 'text' },
    { name: 'sort_order', label: 'Sort Order', type: 'number' },
    { name: 'content', label: 'Content', type: 'textarea' },
    { name: 'seo_title', label: 'SEO Title', type: 'text' },
    { name: 'seo_description', label: 'SEO Description', type: 'textarea' },
    { name: 'og_image', label: 'OG Image URL', type: 'url' }
  ],
  specialties: [
    { name: 'name', label: 'Name', type: 'text' },
    { name: 'slug', label: 'Slug', type: 'text' },
    { name: 'status', label: 'Status', type: 'select', options: ['draft', 'published'] },
    { name: 'sort_order', label: 'Sort Order', type: 'number' },
    { name: 'icon', label: 'Icon', type: 'text' },
    { name: 'description', label: 'Description', type: 'textarea' }
  ],
  consultants: [
    { name: 'name', label: 'Name', type: 'text' },
    { name: 'slug', label: 'Slug', type: 'text' },
    { name: 'status', label: 'Status', type: 'select', options: ['draft', 'published'] },
    { name: 'sort_order', label: 'Sort Order', type: 'number' },
    { name: 'specialty_id', label: 'Specialty ID', type: 'number' },
    { name: 'photo', label: 'Photo URL', type: 'url' },
    { name: 'qualifications', label: 'Qualifications', type: 'textarea' },
    { name: 'experience', label: 'Experience', type: 'textarea' },
    { name: 'bio', label: 'Bio', type: 'textarea' },
    { name: 'clinic_hours', label: 'Clinic Hours', type: 'textarea' },
    { name: 'contact_number', label: 'Contact Number', type: 'text' },
    { name: 'email', label: 'Email', type: 'email' },
    { name: 'booking_link', label: 'Booking Link', type: 'url' },
    { name: 'seo_title', label: 'SEO Title', type: 'text' },
    { name: 'seo_description', label: 'SEO Description', type: 'textarea' }
  ],
  promotions: [
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'slug', label: 'Slug', type: 'text' },
    { name: 'status', label: 'Status', type: 'select', options: ['draft', 'published', 'expired'] },
    { name: 'start_date', label: 'Start Date', type: 'date' },
    { name: 'end_date', label: 'End Date', type: 'date' },
    { name: 'featured_image', label: 'Featured Image URL', type: 'url' },
    { name: 'description', label: 'Description', type: 'textarea' },
    { name: 'seo_title', label: 'SEO Title', type: 'text' },
    { name: 'seo_description', label: 'SEO Description', type: 'textarea' }
  ],
  forms: [
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'shortcode', label: 'Shortcode', type: 'text' },
    { name: 'status', label: 'Status', type: 'select', options: ['active', 'inactive'] },
    { name: 'fields_json', label: 'Fields JSON', type: 'json', help: 'Use valid JSON. Do not collect sensitive health details unless necessary and approved.' },
    { name: 'settings_json', label: 'Settings JSON', type: 'json' }
  ]
};

export function getAdminCollection(key: string) {
  return adminCollections.find((collection) => collection.key === key);
}
