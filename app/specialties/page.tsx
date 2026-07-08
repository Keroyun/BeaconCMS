import Link from 'next/link';
import type { Metadata } from 'next';
import { specialties } from '@/lib/site';

export const metadata: Metadata = {
  title: 'Specialties',
  description: 'Browse hospital specialties and care areas.'
};

export default function SpecialtiesPage() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <h1 className="text-3xl font-semibold text-beacon-navy">Specialties</h1>
      <p className="mt-3 max-w-2xl text-sm leading-6 text-beacon-muted">
        Specialty copy should stay clinically accurate, balanced, and aligned with approved service information.
      </p>
      <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {specialties.map((specialty) => (
          <Link key={specialty.slug} href={`/specialties/${specialty.slug}`} className="rounded border border-slate-200 bg-white p-5 hover:shadow-soft">
            <h2 className="text-lg font-semibold text-beacon-navy">{specialty.name}</h2>
            <p className="mt-2 text-sm leading-6 text-beacon-muted">{specialty.description}</p>
          </Link>
        ))}
      </div>
    </section>
  );
}
