import type { NextRequest } from 'next/server';
import { fail, ok, serverError } from '@/lib/api-response';
import { collectionConfig, type Collection, listPublic } from '@/lib/content-repository';

export const dynamic = 'force-dynamic';

function isCollection(value: string): value is Collection {
  return value in collectionConfig;
}

export async function GET(request: NextRequest, { params }: { params: { collection: string } }) {
  try {
    if (!isCollection(params.collection)) {
      return fail('Unknown collection.', 404);
    }

    const limit = Math.min(Number(request.nextUrl.searchParams.get('limit') || '50'), 100);
    const rows = await listPublic(params.collection, limit);
    return ok(rows);
  } catch (error) {
    return serverError(error);
  }
}
