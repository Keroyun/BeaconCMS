'use client';

import { useState } from 'react';
import type { FormEvent } from 'react';
import { useRouter } from 'next/navigation';

export function LoginForm() {
  const router = useRouter();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');
    setLoading(true);

    const response = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ identifier, password })
    });

    setLoading(false);

    if (!response.ok) {
      setError('Login failed. Check the username/email and password.');
      return;
    }

    router.push('/admin');
    router.refresh();
  }

  return (
    <form onSubmit={submit} className="grid gap-4 rounded border border-slate-200 bg-white p-6 shadow-soft">
      <div>
        <h1 className="text-2xl font-semibold text-beacon-navy">Admin Login</h1>
        <p className="mt-2 text-sm text-beacon-muted">Use the admin account created with `npm run admin:create`.</p>
      </div>
      {error ? <p className="rounded bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p> : null}
      <label className="grid gap-2 text-sm font-medium text-slate-700">
        Username or email
        <input
          value={identifier}
          onChange={(event) => setIdentifier(event.target.value)}
          className="rounded border border-slate-300 px-3 py-2"
          autoComplete="username"
          required
        />
      </label>
      <label className="grid gap-2 text-sm font-medium text-slate-700">
        Password
        <input
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          className="rounded border border-slate-300 px-3 py-2"
          type="password"
          autoComplete="current-password"
          required
        />
      </label>
      <button type="submit" disabled={loading} className="rounded bg-beacon-teal px-4 py-3 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60">
        {loading ? 'Signing in...' : 'Sign In'}
      </button>
    </form>
  );
}
