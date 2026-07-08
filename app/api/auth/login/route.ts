import { fail, ok, serverError } from '@/lib/api-response';
import { createSession, findUserForLogin, verifyPassword } from '@/lib/auth';
import { asString, readJson } from '@/lib/validation';

export const dynamic = 'force-dynamic';

export async function POST(request: Request) {
  try {
    const body = await readJson(request);

    if (!body || typeof body !== 'object') {
      return fail('Invalid JSON payload.');
    }

    const identifier = asString((body as Record<string, unknown>).identifier);
    const password = asString((body as Record<string, unknown>).password);

    if (!identifier || !password) {
      return fail('Username/email and password are required.');
    }

    const user = await findUserForLogin(identifier);

    if (!user || !(await verifyPassword(password, user.password))) {
      return fail('Invalid credentials.', 401);
    }

    await createSession({
      id: user.id,
      username: user.username,
      email: user.email,
      role: user.role
    });

    return ok({
      id: user.id,
      username: user.username,
      email: user.email,
      role: user.role
    });
  } catch (error) {
    return serverError(error);
  }
}
