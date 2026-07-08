import type { NextRequest } from 'next/server';
import { fail, ok } from '@/lib/api-response';
import { getCurrentUserFromRequest } from '@/lib/auth';

export const dynamic = 'force-dynamic';

export async function GET(request: NextRequest) {
  const user = await getCurrentUserFromRequest(request);

  if (!user) {
    return fail('Unauthenticated.', 401);
  }

  return ok(user);
}
