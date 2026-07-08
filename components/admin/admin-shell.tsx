'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { LogOut } from 'lucide-react';
import type { ReactNode } from 'react';
import { adminCollections } from '@/lib/admin-config';

export function AdminShell({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const router = useRouter();

  async function logout() {
    await fetch('/api/auth/logout', { method: 'POST' });
    router.push('/admin/login');
    router.refresh();
  }

  return (
    <div className="min-h-screen bg-slate-100">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
          <Link href="/admin" className="text-lg font-semibold text-beacon-navy">
            BeaconCMS Admin
          </Link>
          <button
            type="button"
            onClick={logout}
            className="inline-flex items-center gap-2 rounded border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
          >
            <LogOut size={16} aria-hidden="true" />
            Logout
          </button>
        </div>
      </header>
      <div className="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[240px_1fr] lg:px-8">
        <aside className="rounded border border-slate-200 bg-white p-3">
          <nav className="grid gap-1" aria-label="Admin navigation">
            <Link
              href="/admin"
              className={`rounded px-3 py-2 text-sm font-medium ${pathname === '/admin' ? 'bg-beacon-mint text-beacon-teal' : 'text-slate-700 hover:bg-slate-100'}`}
            >
              Dashboard
            </Link>
            {adminCollections.map((collection) => (
              <Link
                key={collection.key}
                href={`/admin/${collection.key}`}
                className={`rounded px-3 py-2 text-sm font-medium ${pathname.startsWith(`/admin/${collection.key}`) ? 'bg-beacon-mint text-beacon-teal' : 'text-slate-700 hover:bg-slate-100'}`}
              >
                {collection.label}
              </Link>
            ))}
          </nav>
        </aside>
        <section>{children}</section>
      </div>
    </div>
  );
}
