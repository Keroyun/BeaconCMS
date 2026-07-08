import Link from 'next/link';
import { AdminGuard } from '@/components/admin/admin-guard';
import { AdminShell } from '@/components/admin/admin-shell';
import { adminCollections } from '@/lib/admin-config';

export default function AdminDashboardPage() {
  return (
    <AdminGuard>
      <AdminShell>
        <div className="grid gap-6">
          <div className="rounded border border-slate-200 bg-white p-5">
            <h1 className="text-2xl font-semibold text-beacon-navy">Dashboard</h1>
            <p className="mt-2 max-w-3xl text-sm leading-6 text-beacon-muted">
              Manage content records through the rebuilt Next.js API. Keep publication workflows strict for healthcare pages, doctor profiles, packages, and lead forms.
            </p>
          </div>
          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {adminCollections.map((collection) => (
              <Link key={collection.key} href={`/admin/${collection.key}`} className="rounded border border-slate-200 bg-white p-5 hover:shadow-soft">
                <h2 className="text-lg font-semibold text-beacon-navy">{collection.label}</h2>
                <p className="mt-2 text-sm leading-6 text-beacon-muted">{collection.description}</p>
              </Link>
            ))}
          </div>
          <div className="rounded border border-amber-200 bg-amber-50 p-5">
            <h2 className="text-base font-semibold text-amber-900">Production Note</h2>
            <p className="mt-2 text-sm leading-6 text-amber-900">
              Media upload is intentionally blocked until persistent object storage is configured. For this stack, Cloudflare R2 is the recommended option.
            </p>
          </div>
        </div>
      </AdminShell>
    </AdminGuard>
  );
}
