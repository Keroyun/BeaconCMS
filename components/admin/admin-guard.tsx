'use client';

import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { useRouter } from 'next/navigation';

export function AdminGuard({ children }: { children: ReactNode }) {
  const router = useRouter();
  const [ready, setReady] = useState(false);

  useEffect(() => {
    let active = true;

    async function checkSession() {
      const response = await fetch('/api/auth/me', { cache: 'no-store' });

      if (!active) {
        return;
      }

      if (!response.ok) {
        router.replace('/admin/login');
        return;
      }

      setReady(true);
    }

    checkSession();

    return () => {
      active = false;
    };
  }, [router]);

  if (!ready) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
        <p className="rounded bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">Checking admin session...</p>
      </div>
    );
  }

  return children;
}
