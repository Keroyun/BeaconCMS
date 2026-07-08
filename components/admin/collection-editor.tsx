'use client';

import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import type { AdminCollection, AdminField } from '@/lib/admin-config';

type CollectionEditorProps = {
  collection: AdminCollection;
  fields: AdminField[];
  id?: string;
  label: string;
};

type FormState = Record<string, string>;

export function CollectionEditor({ collection, fields, id, label }: CollectionEditorProps) {
  const router = useRouter();
  const isNew = !id;
  const [form, setForm] = useState<FormState>({});
  const [loading, setLoading] = useState(!isNew);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (isNew) {
      return;
    }

    let active = true;

    async function loadRecord() {
      const response = await fetch(`/api/admin/${collection}/${id}`, { cache: 'no-store' });

      if (!active) {
        return;
      }

      if (!response.ok) {
        setError('Could not load record.');
        setLoading(false);
        return;
      }

      const json = await response.json();
      const data = json.data || {};
      const nextForm: FormState = {};

      for (const field of fields) {
        const value = data[field.name];
        nextForm[field.name] = value === null || value === undefined ? '' : typeof value === 'object' ? JSON.stringify(value, null, 2) : String(value);
      }

      setForm(nextForm);
      setLoading(false);
    }

    loadRecord();

    return () => {
      active = false;
    };
  }, [collection, fields, id, isNew]);

  function update(name: string, value: string) {
    setForm((current) => ({ ...current, [name]: value }));
  }

  function buildPayload() {
    const payload: Record<string, unknown> = {};

    for (const field of fields) {
      const value = form[field.name];

      if (value === undefined || value === '') {
        continue;
      }

      if (field.type === 'number') {
        payload[field.name] = Number(value);
      } else if (field.type === 'json') {
        payload[field.name] = JSON.parse(value);
      } else {
        payload[field.name] = value;
      }
    }

    return payload;
  }

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError('');
    setSaving(true);

    let payload: Record<string, unknown>;

    try {
      payload = buildPayload();
    } catch {
      setError('JSON fields must contain valid JSON.');
      setSaving(false);
      return;
    }

    const response = await fetch(isNew ? `/api/admin/${collection}` : `/api/admin/${collection}/${id}`, {
      method: isNew ? 'POST' : 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    setSaving(false);

    if (!response.ok) {
      setError('Save failed. Check required fields and duplicate slugs.');
      return;
    }

    router.push(`/admin/${collection}`);
    router.refresh();
  }

  if (loading) {
    return <p className="rounded border border-slate-200 bg-white p-4 text-sm text-beacon-muted">Loading record...</p>;
  }

  return (
    <form onSubmit={submit} className="rounded border border-slate-200 bg-white">
      <div className="border-b border-slate-200 p-4">
        <h1 className="text-xl font-semibold text-beacon-navy">{isNew ? `New ${label}` : `Edit ${label}`}</h1>
        <p className="mt-1 text-sm text-beacon-muted">Review healthcare claims, doctor credentials, and promotion terms before publishing.</p>
      </div>
      {error ? <p className="m-4 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p> : null}
      <div className="grid gap-5 p-4">
        {fields.map((field) => (
          <label key={field.name} className="grid gap-2 text-sm font-medium text-slate-700">
            {field.label}
            {field.type === 'textarea' || field.type === 'json' ? (
              <textarea
                value={form[field.name] || ''}
                onChange={(event) => update(field.name, event.target.value)}
                className="min-h-32 rounded border border-slate-300 px-3 py-2 font-sans"
              />
            ) : field.type === 'select' ? (
              <select value={form[field.name] || ''} onChange={(event) => update(field.name, event.target.value)} className="rounded border border-slate-300 px-3 py-2">
                <option value="">Select</option>
                {field.options?.map((option) => (
                  <option key={option} value={option}>
                    {option}
                  </option>
                ))}
              </select>
            ) : (
              <input
                value={form[field.name] || ''}
                onChange={(event) => update(field.name, event.target.value)}
                className="rounded border border-slate-300 px-3 py-2"
                type={field.type}
              />
            )}
            {field.help ? <span className="text-xs font-normal text-beacon-muted">{field.help}</span> : null}
          </label>
        ))}
      </div>
      <div className="flex justify-end gap-3 border-t border-slate-200 p-4">
        <button type="button" onClick={() => router.back()} className="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
          Cancel
        </button>
        <button type="submit" disabled={saving} className="rounded bg-beacon-teal px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60">
          {saving ? 'Saving...' : 'Save'}
        </button>
      </div>
    </form>
  );
}
