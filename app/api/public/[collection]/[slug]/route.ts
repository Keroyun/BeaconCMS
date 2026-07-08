import { fail, ok, serverError } from '@/lib/api-response';
import { collectionConfig, type Collection, getPublicBySlug } from '@/lib/content-repository';

export const dynamic = 'force-dynamic';

function isCollection(value: string): value is Collection {
  return value in collectionConfig;
}

export async function GET(_: Request, { params }: { params: { collection: string; slug: string } }) {
  try {
    if (!isCollection(params.collection)) {
      return fail('Unknown collection.', 404);
    }

    const row = await getPublicBySlug(params.collection, params.slug);

    if (!row) {
      return fail('Not found.', 404);
    }

    return ok(row);
  } catch (error) {
    return serverError(error);
  }
}
