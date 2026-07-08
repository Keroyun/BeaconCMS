import { notFound } from 'next/navigation';
import { AdminGuard } from '@/components/admin/admin-guard';
import { AdminShell } from '@/components/admin/admin-shell';
import { CollectionTable } from '@/components/admin/collection-table';
import { getAdminCollection } from '@/lib/admin-config';

export default function AdminCollectionPage({ params }: { params: { collection: string } }) {
  const collection = getAdminCollection(params.collection);

  if (!collection) {
    notFound();
  }

  return (
    <AdminGuard>
      <AdminShell>
        <CollectionTable
          collection={collection.key}
          label={collection.label}
          titleField={collection.titleField}
          statusField={collection.statusField}
        />
      </AdminShell>
    </AdminGuard>
  );
}
