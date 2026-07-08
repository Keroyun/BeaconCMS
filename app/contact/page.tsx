import type { Metadata } from 'next';
import { Mail, MapPin, Phone } from 'lucide-react';
import { siteConfig } from '@/lib/site';

export const metadata: Metadata = {
  title: 'Contact',
  description: 'Contact Beacon Hospital.'
};

export default function ContactPage() {
  return (
    <section className="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
      <div>
        <h1 className="text-3xl font-semibold text-beacon-navy">Contact</h1>
        <p className="mt-3 text-sm leading-6 text-beacon-muted">
          Use this page for appointment enquiries, call clicks, WhatsApp clicks, and form submissions. Do not send sensitive health information to analytics platforms.
        </p>
        <div className="mt-6 grid gap-4 text-sm text-beacon-muted">
          <p className="flex gap-3"><MapPin className="text-beacon-teal" size={20} aria-hidden="true" />{siteConfig.address}</p>
          <p className="flex gap-3"><Phone className="text-beacon-teal" size={20} aria-hidden="true" />{siteConfig.phone}</p>
          <p className="flex gap-3"><Mail className="text-beacon-teal" size={20} aria-hidden="true" />{siteConfig.email}</p>
        </div>
      </div>
      <form className="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div className="grid gap-4">
          <label className="grid gap-2 text-sm font-medium text-beacon-navy">
            Name
            <input className="rounded border border-slate-300 px-3 py-2" name="name" autoComplete="name" />
          </label>
          <label className="grid gap-2 text-sm font-medium text-beacon-navy">
            Email
            <input className="rounded border border-slate-300 px-3 py-2" name="email" type="email" autoComplete="email" />
          </label>
          <label className="grid gap-2 text-sm font-medium text-beacon-navy">
            Message
            <textarea className="min-h-32 rounded border border-slate-300 px-3 py-2" name="message" />
          </label>
          <button type="button" className="rounded bg-beacon-teal px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">
            Submit Enquiry
          </button>
        </div>
      </form>
    </section>
  );
}
