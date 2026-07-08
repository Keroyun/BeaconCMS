import Link from 'next/link';
import { Activity, ClipboardCheck, HeartPulse, Hospital } from 'lucide-react';
import { SectionHeading } from '@/components/section-heading';
import { doctors, posts, promotions, specialties } from '@/lib/site';

const iconMap = {
  Activity,
  ClipboardCheck,
  Hospital,
  HeartPulse
};

export default function HomePage() {
  return (
    <>
      <section className="bg-white">
        <div className="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-20">
          <div>
            <p className="text-sm font-semibold uppercase tracking-wide text-beacon-teal">Trusted Healthcare Information</p>
            <h1 className="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-beacon-navy sm:text-5xl">
              Welcome to Beacon Hospital
            </h1>
            <p className="mt-5 max-w-2xl text-base leading-7 text-beacon-muted">
              A modern patient-facing website structure for doctors, specialties, health packages, and educational content.
              Replace this draft copy with verified hospital-approved content before production.
            </p>
            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
              <Link href="/doctors" className="rounded bg-beacon-teal px-5 py-3 text-center text-sm font-semibold text-white hover:bg-teal-800">
                Find a Doctor
              </Link>
              <Link href="/promotions" className="rounded border border-beacon-teal px-5 py-3 text-center text-sm font-semibold text-beacon-teal hover:bg-beacon-mint">
                View Promotions
              </Link>
            </div>
          </div>
          <div className="rounded border border-slate-200 bg-beacon-sky p-6 shadow-soft">
            <dl className="grid gap-4 sm:grid-cols-2">
              {[
                ['Doctors', 'Specialist profile pathways'],
                ['Specialties', 'Service discovery'],
                ['Promotions', 'Package landing pages'],
                ['Blog', 'Health education']
              ].map(([label, text]) => (
                <div key={label} className="rounded bg-white p-4">
                  <dt className="text-lg font-semibold text-beacon-navy">{label}</dt>
                  <dd className="mt-1 text-sm text-beacon-muted">{text}</dd>
                </div>
              ))}
            </dl>
          </div>
        </div>
      </section>

      <section className="px-4 py-14 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="Care Areas"
          title="Our Specialties"
          description="Use verified specialty names, descriptions, and clinical scope before publishing."
        />
        <div className="mx-auto mt-8 grid max-w-7xl gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {specialties.map((specialty) => {
            const Icon = iconMap[specialty.icon as keyof typeof iconMap] || Hospital;
            return (
              <Link key={specialty.slug} href={`/specialties/${specialty.slug}`} className="rounded border border-slate-200 bg-white p-5 shadow-sm hover:shadow-soft">
                <Icon className="h-8 w-8 text-beacon-teal" aria-hidden="true" />
                <h3 className="mt-4 text-lg font-semibold text-beacon-navy">{specialty.name}</h3>
                <p className="mt-2 text-sm leading-6 text-beacon-muted">{specialty.description}</p>
              </Link>
            );
          })}
        </div>
      </section>

      <section className="bg-white px-4 py-14 sm:px-6 lg:px-8">
        <SectionHeading title="Featured Consultants" description="Doctor data here is placeholder content until connected to a verified CMS source." />
        <div className="mx-auto mt-8 grid max-w-7xl gap-4 md:grid-cols-3">
          {doctors.map((doctor) => (
            <Link key={doctor.slug} href={`/doctors/${doctor.slug}`} className="rounded border border-slate-200 p-5 hover:border-beacon-teal">
              <div className="flex h-16 w-16 items-center justify-center rounded bg-slate-100 text-xl font-semibold text-beacon-navy">
                {doctor.name.charAt(0)}
              </div>
              <h3 className="mt-4 text-lg font-semibold text-beacon-navy">{doctor.name}</h3>
              <p className="mt-1 text-sm font-medium text-beacon-teal">{doctor.specialty}</p>
              <p className="mt-3 text-sm leading-6 text-beacon-muted">{doctor.qualifications}</p>
            </Link>
          ))}
        </div>
      </section>

      <section className="px-4 py-14 sm:px-6 lg:px-8">
        <SectionHeading title="Latest Promotions" description="Avoid claims such as guaranteed results, risk free, or best. Keep package details verifiable." />
        <div className="mx-auto mt-8 grid max-w-7xl gap-4 md:grid-cols-2">
          {promotions.map((promotion) => (
            <Link key={promotion.slug} href={`/promotions/${promotion.slug}`} className="rounded border border-slate-200 bg-white p-5 hover:shadow-soft">
              <p className="text-xs font-semibold uppercase tracking-wide text-beacon-teal">Until {promotion.endDate}</p>
              <h3 className="mt-2 text-lg font-semibold text-beacon-navy">{promotion.title}</h3>
              <p className="mt-2 text-sm leading-6 text-beacon-muted">{promotion.description}</p>
            </Link>
          ))}
        </div>
      </section>

      <section className="bg-white px-4 py-14 sm:px-6 lg:px-8">
        <SectionHeading title="Health & Wellness Blog" description="Educational articles should be reviewed for clinical accuracy and compliance." />
        <div className="mx-auto mt-8 grid max-w-7xl gap-4 md:grid-cols-2">
          {posts.map((post) => (
            <Link key={post.slug} href={`/blog/${post.slug}`} className="rounded border border-slate-200 p-5 hover:border-beacon-teal">
              <p className="text-xs text-beacon-muted">{post.date}</p>
              <h3 className="mt-2 text-lg font-semibold text-beacon-navy">{post.title}</h3>
              <p className="mt-2 text-sm leading-6 text-beacon-muted">{post.excerpt}</p>
            </Link>
          ))}
        </div>
      </section>
    </>
  );
}
