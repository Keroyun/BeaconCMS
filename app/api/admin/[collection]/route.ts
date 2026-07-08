import type { NextRequest } from 'next/server';
import { fail, ok, serverError } from '@/lib/api-response';
import { requireAdmin } from '@/lib/auth';
import { collectionConfig, type Collection, createAdmin, listAdmin } from '@/lib/content-repository';
import { readJson } from '@/lib/validation';

export const dynamic = 'force-dynamic';

function isCollection(value: string): value is Collection {
  return value in collectionConfig;
}

export async function GET(request: NextRequest, { params }: { params: { collection: string } }) {
  try {
    const user = await requireAdmin(request);

    if (!user) {
      return fail('Unauthorised.', 401);
    }

    if (!isCollection(params.collection)) {
      return fail('Unknown collection.', 404);
    }

    const rows = await listAdmin(params.collection);
    return ok(rows);
  } catch (error) {
    return serverError(error);
  }
}

export async function POST(request: NextRequest, { params }: { params: { collection: string } }) {
  try {
    const user = await requireAdmin(request);

    if (!user) {
      return fail('Unauthorised.', 401);
    }

    if (!isCollection(params.collection)) {
      return fail('Unknown collection.', 404);
    }

    const body = await readJson(request);

    if (!body || typeof body !== 'object') {
      return fail('Invalid JSON payload.');
    }

    const row = await createAdmin(params.collection, body as Record<string, unknown>);
    return ok(row, { status: 201 });
  } catch (error) {
    return serverError(error);
  }
}
