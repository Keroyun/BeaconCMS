import Link from 'next/link';
import type { Metadata } from 'next';
import { posts } from '@/lib/site';

export const metadata: Metadata = {
  title: 'Health Blog',
  description: 'Read healthcare education articles.'
};

export default function BlogPage() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <h1 className="text-3xl font-semibold text-beacon-navy">Health Blog</h1>
      <p className="mt-3 max-w-2xl text-sm leading-6 text-beacon-muted">
        Content should be educational and should not replace personalised medical advice from a qualified healthcare professional.
      </p>
      <div className="mt-8 grid gap-4 md:grid-cols-2">
        {posts.map((post) => (
          <Link key={post.slug} href={`/blog/${post.slug}`} className="rounded border border-slate-200 bg-white p-5 hover:shadow-soft">
            <p className="text-xs text-beacon-muted">{post.date}</p>
            <h2 className="mt-2 text-lg font-semibold text-beacon-navy">{post.title}</h2>
            <p className="mt-2 text-sm leading-6 text-beacon-muted">{post.excerpt}</p>
          </Link>
        ))}
      </div>
    </section>
  );
}
