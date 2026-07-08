import { ok } from '@/lib/api-response';
import { clearSession } from '@/lib/auth';

export const dynamic = 'force-dynamic';

export async function POST() {
  clearSession();
  return ok({ loggedOut: true });
}
