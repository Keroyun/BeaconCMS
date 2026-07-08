import { ok, serverError } from '@/lib/api-response';
import { queryOne } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    await queryOne('SELECT 1 AS ok');
    return ok({ status: 'ok' });
  } catch (error) {
    return serverError(error);
  }
}
