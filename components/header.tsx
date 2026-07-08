'use client';

import Link from 'next/link';
import { Menu, X } from 'lucide-react';
import { useState } from 'react';
import { siteConfig } from '@/lib/site';

const navItems = [
  { href: '/', label: 'Home' },
  { href: '/doctors', label: 'Doctors' },
  { href: '/specialties', label: 'Specialties' },
  { href: '/promotions', label: 'Promotions' },
  { href: '/blog', label: 'Blog' },
  { href: '/contact', label: 'Contact' }
];

export function Header() {
  const [open, setOpen] = useState(false);

  return (
    <header className="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
      <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <Link href="/" className="flex items-center gap-3" aria-label={`${siteConfig.name} home`}>
          <span className="flex h-10 w-10 items-center justify-center rounded bg-beacon-teal text-lg font-bold text-white">
            B
          </span>
          <span className="text-base font-semibold text-beacon-navy sm:text-lg">{siteConfig.name}</span>
        </Link>

        <nav className="hidden items-center gap-7 lg:flex" aria-label="Primary navigation">
          {navItems.map((item) => (
            <Link key={item.href} href={item.href} className="text-sm font-medium text-slate-700 hover:text-beacon-teal">
              {item.label}
            </Link>
          ))}
        </nav>

        <Link
          href="/contact"
          className="hidden rounded bg-beacon-teal px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800 lg:inline-flex"
        >
          Appointment Enquiry
        </Link>

        <button
          type="button"
          className="inline-flex h-10 w-10 items-center justify-center rounded border border-slate-300 text-slate-800 lg:hidden"
          aria-label="Toggle navigation"
          aria-expanded={open}
          onClick={() => setOpen((value) => !value)}
        >
          {open ? <X size={20} aria-hidden="true" /> : <Menu size={20} aria-hidden="true" />}
        </button>
      </div>

      {open ? (
        <nav className="border-t border-slate-200 bg-white px-4 py-4 lg:hidden" aria-label="Mobile navigation">
          <div className="mx-auto grid max-w-7xl gap-2">
            {navItems.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="rounded px-3 py-3 text-sm font-medium text-slate-700 hover:bg-slate-100"
                onClick={() => setOpen(false)}
              >
                {item.label}
              </Link>
            ))}
          </div>
        </nav>
      ) : null}
    </header>
  );
}
