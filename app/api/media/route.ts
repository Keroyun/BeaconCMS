import type { NextRequest } from 'next/server';
import { fail, ok, serverError } from '@/lib/api-response';
import { requireAdmin } from '@/lib/auth';
import { queryRows } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET(request: NextRequest) {
  try {
    const user = await requireAdmin(request);

    if (!user) {
      return fail('Unauthorised.', 401);
    }

    const rows = await queryRows('SELECT * FROM media ORDER BY created_at DESC LIMIT 100');
    return ok(rows);
  } catch (error) {
    return serverError(error);
  }
}

export async function POST(request: NextRequest) {
  const user = await requireAdmin(request);

  if (!user) {
    return fail('Unauthorised.', 401);
  }

  return fail('Media upload requires persistent object storage such as Cloudflare R2 or S3 before production use.', 501);
}
