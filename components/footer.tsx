import Link from 'next/link';
import { Mail, MapPin, Phone } from 'lucide-react';
import { siteConfig } from '@/lib/site';

export function Footer() {
  return (
    <footer className="border-t border-slate-200 bg-beacon-navy text-white">
      <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
          <h2 className="text-lg font-semibold">{siteConfig.name}</h2>
          <p className="mt-3 max-w-sm text-sm leading-6 text-slate-200">
            Healthcare information and appointment pathways should be clear, accurate, and easy to use across every device.
          </p>
        </div>
        <div>
          <h2 className="text-sm font-semibold uppercase tracking-wide text-slate-200">Quick Links</h2>
          <div className="mt-3 grid gap-2 text-sm text-slate-200">
            <Link href="/doctors">Doctors</Link>
            <Link href="/specialties">Specialties</Link>
            <Link href="/promotions">Promotions</Link>
            <Link href="/blog">Health Blog</Link>
          </div>
        </div>
        <div>
          <h2 className="text-sm font-semibold uppercase tracking-wide text-slate-200">Contact</h2>
          <div className="mt-3 grid gap-3 text-sm text-slate-200">
            <p className="flex gap-2"><MapPin size={18} aria-hidden="true" />{siteConfig.address}</p>
            <p className="flex gap-2"><Phone size={18} aria-hidden="true" />{siteConfig.phone}</p>
            <p className="flex gap-2"><Mail size={18} aria-hidden="true" />{siteConfig.email}</p>
          </div>
        </div>
      </div>
      <div className="border-t border-white/10 px-4 py-4 text-center text-xs text-slate-300">
        Copyright {new Date().getFullYear()} {siteConfig.name}. All rights reserved.
      </div>
    </footer>
  );
}
