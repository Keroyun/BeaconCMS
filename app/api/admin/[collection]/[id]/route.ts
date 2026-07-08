import type { NextRequest } from 'next/server';
import { fail, ok, serverError } from '@/lib/api-response';
import { requireAdmin } from '@/lib/auth';
import { collectionConfig, type Collection, deleteAdmin, getAdminById, updateAdmin } from '@/lib/content-repository';
import { readJson } from '@/lib/validation';

export const dynamic = 'force-dynamic';

function isCollection(value: string): value is Collection {
  return value in collectionConfig;
}

function parseId(value: string) {
  const id = Number(value);
  return Number.isInteger(id) && id > 0 ? id : null;
}

export async function GET(request: NextRequest, { params }: { params: { collection: string; id: string } }) {
  try {
    const user = await requireAdmin(request);

    if (!user) {
      return fail('Unauthorised.', 401);
    }

    if (!isCollection(params.collection)) {
      return fail('Unknown collection.', 404);
    }

    const id = parseId(params.id);

    if (!id) {
      return fail('Invalid ID.');
    }

    const row = await getAdminById(params.collection, id);

    if (!row) {
      return fail('Not found.', 404);
    }

    return ok(row);
  } catch (error) {
    return serverError(error);
  }
}

export async function PUT(request: NextRequest, { params }: { params: { collection: string; id: string } }) {
  try {
    const user = await requireAdmin(request);

    if (!user) {
      return fail('Unauthorised.', 401);
    }

    if (!isCollection(params.collection)) {
      return fail('Unknown collection.', 404);
    }

    const id = parseId(params.id);

    if (!id) {
      return fail('Invalid ID.');
    }

    const body = await readJson(request);

    if (!body || typeof body !== 'object') {
      return fail('Invalid JSON payload.');
    }

    const row = await updateAdmin(params.collection, id, body as Record<string, unknown>);
    return ok(row);
  } catch (error) {
    return serverError(error);
  }
}

export async function DELETE(request: NextRequest, { params }: { params: { collection: string; id: string } }) {
  try {
    const user = await requireAdmin(request);

    if (!user) {
      return fail('Unauthorised.', 401);
    }

    if (!isCollection(params.collection)) {
      return fail('Unknown collection.', 404);
    }

    const id = parseId(params.id);

    if (!id) {
      return fail('Invalid ID.');
    }

    return ok(await deleteAdmin(params.collection, id));
  } catch (error) {
    return serverError(error);
  }
}
