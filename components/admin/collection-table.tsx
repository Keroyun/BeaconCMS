'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { Edit, Plus, Trash2 } from 'lucide-react';
import type { AdminCollection } from '@/lib/admin-config';

type CollectionTableProps = {
  collection: AdminCollection;
  label: string;
  titleField: string;
  statusField?: string;
};

type Row = Record<string, unknown> & {
  id: number;
};

export function CollectionTable({ collection, label, titleField, statusField }: CollectionTableProps) {
  const [rows, setRows] = useState<Row[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  async function loadRows() {
    setLoading(true);
    setError('');

    const response = await fetch(`/api/admin/${collection}`, { cache: 'no-store' });

    if (!response.ok) {
      setError('Could not load records.');
      setLoading(false);
      return;
    }

    const json = await response.json();
    setRows(json.data || []);
    setLoading(false);
  }

  async function remove(id: number) {
    const confirmed = window.confirm('Delete this record? This cannot be undone.');

    if (!confirmed) {
      return;
    }

    const response = await fetch(`/api/admin/${collection}/${id}`, { method: 'DELETE' });

    if (!response.ok) {
      setError('Delete failed.');
      return;
    }

    await loadRows();
  }

  useEffect(() => {
    loadRows();
  }, [collection]);

  return (
    <div className="rounded border border-slate-200 bg-white">
      <div className="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-xl font-semibold text-beacon-navy">{label}</h1>
          <p className="mt-1 text-sm text-beacon-muted">{rows.length} records</p>
        </div>
        <Link href={`/admin/${collection}/new`} className="inline-flex items-center justify-center gap-2 rounded bg-beacon-teal px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
          <Plus size={16} aria-hidden="true" />
          New
        </Link>
      </div>
      {error ? <p className="m-4 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p> : null}
      {loading ? (
        <p className="p-4 text-sm text-beacon-muted">Loading...</p>
      ) : (
        <div className="overflow-x-auto">
          <table className="w-full min-w-[680px] border-collapse text-left text-sm">
            <thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
              <tr>
                <th className="px-4 py-3">Title</th>
                <th className="px-4 py-3">Slug</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Updated</th>
                <th className="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((row) => (
                <tr key={row.id} className="border-t border-slate-200">
                  <td className="px-4 py-3 font-medium text-beacon-navy">{String(row[titleField] || `#${row.id}`)}</td>
                  <td className="px-4 py-3 text-slate-600">{String(row.slug || row.shortcode || '')}</td>
                  <td className="px-4 py-3 text-slate-600">{statusField ? String(row[statusField] || '') : ''}</td>
                  <td className="px-4 py-3 text-slate-600">{String(row.updated_at || row.created_at || '')}</td>
                  <td className="px-4 py-3">
                    <div className="flex justify-end gap-2">
                      <Link href={`/admin/${collection}/${row.id}`} className="inline-flex h-9 w-9 items-center justify-center rounded border border-slate-300 text-slate-700 hover:bg-slate-50" aria-label="Edit record">
                        <Edit size={16} aria-hidden="true" />
                      </Link>
                      <button type="button" onClick={() => remove(row.id)} className="inline-flex h-9 w-9 items-center justify-center rounded border border-red-200 text-red-700 hover:bg-red-50" aria-label="Delete record">
                        <Trash2 size={16} aria-hidden="true" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {rows.length === 0 ? <p className="p-4 text-sm text-beacon-muted">No records yet.</p> : null}
        </div>
      )}
    </div>
  );
}
