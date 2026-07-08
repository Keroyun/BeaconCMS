import Link from 'next/link';
import type { Metadata } from 'next';
import { promotions } from '@/lib/site';

export const metadata: Metadata = {
  title: 'Promotions',
  description: 'Browse health packages and promotional offers.'
};

export default function PromotionsPage() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <h1 className="text-3xl font-semibold text-beacon-navy">Promotions</h1>
      <p className="mt-3 max-w-2xl text-sm leading-6 text-beacon-muted">
        Confirm package inclusions, exclusions, pricing, period, eligibility, and regulatory wording before publication.
      </p>
      <div className="mt-8 grid gap-4 md:grid-cols-2">
        {promotions.map((promotion) => (
          <Link key={promotion.slug} href={`/promotions/${promotion.slug}`} className="rounded border border-slate-200 bg-white p-5 hover:shadow-soft">
            <p className="text-xs font-semibold uppercase tracking-wide text-beacon-teal">Until {promotion.endDate}</p>
            <h2 className="mt-2 text-lg font-semibold text-beacon-navy">{promotion.title}</h2>
            <p className="mt-2 text-sm leading-6 text-beacon-muted">{promotion.description}</p>
          </Link>
        ))}
      </div>
    </section>
  );
}
