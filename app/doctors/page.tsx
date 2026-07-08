import Link from 'next/link';
import type { Metadata } from 'next';
import { doctors } from '@/lib/site';

export const metadata: Metadata = {
  title: 'Doctors',
  description: 'Browse consultant profile pathways.'
};

export default function DoctorsPage() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <h1 className="text-3xl font-semibold text-beacon-navy">Doctors</h1>
      <p className="mt-3 max-w-2xl text-sm leading-6 text-beacon-muted">
        Replace placeholder profiles with verified names, qualifications, specialties, languages, and profile images.
      </p>
      <div className="mt-8 grid gap-4 md:grid-cols-3">
        {doctors.map((doctor) => (
          <Link key={doctor.slug} href={`/doctors/${doctor.slug}`} className="rounded border border-slate-200 bg-white p-5 hover:shadow-soft">
            <h2 className="text-lg font-semibold text-beacon-navy">{doctor.name}</h2>
            <p className="mt-1 text-sm font-medium text-beacon-teal">{doctor.specialty}</p>
            <p className="mt-3 text-sm leading-6 text-beacon-muted">{doctor.qualifications}</p>
          </Link>
        ))}
      </div>
    </section>
  );
}
