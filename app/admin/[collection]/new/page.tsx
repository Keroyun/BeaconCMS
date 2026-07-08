import { notFound } from 'next/navigation';
import { AdminGuard } from '@/components/admin/admin-guard';
import { AdminShell } from '@/components/admin/admin-shell';
import { CollectionEditor } from '@/components/admin/collection-editor';
import { adminFields, getAdminCollection } from '@/lib/admin-config';

export default function AdminNewRecordPage({ params }: { params: { collection: string } }) {
  const collection = getAdminCollection(params.collection);

  if (!collection) {
    notFound();
  }

  return (
    <AdminGuard>
      <AdminShell>
        <CollectionEditor collection={collection.key} fields={adminFields[collection.key]} label={collection.label} />
      </AdminShell>
    </AdminGuard>
  );
}
