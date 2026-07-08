import bcrypt from 'bcryptjs';
import { SignJWT, jwtVerify } from 'jose';
import { cookies } from 'next/headers';
import type { NextRequest } from 'next/server';
import { getEnv } from '@/lib/env';
import { queryOne } from '@/lib/db';

export type AuthUser = {
  id: number;
  username: string;
  email: string;
  role: 'admin' | 'editor' | 'author';
};

type UserRow = AuthUser & {
  password: string;
};

const cookieName = 'beaconcms_session';

function secretKey() {
  return new TextEncoder().encode(getEnv('AUTH_SECRET'));
}

export async function verifyPassword(password: string, hash: string) {
  return bcrypt.compare(password, hash);
}

export async function hashPassword(password: string) {
  return bcrypt.hash(password, 12);
}

export async function findUserForLogin(identifier: string) {
  return queryOne<UserRow>(
    `SELECT id, username, email, password, role
     FROM users
     WHERE username = :identifier OR email = :identifier
     LIMIT 1`,
    { identifier }
  );
}

export async function createSession(user: AuthUser) {
  const token = await new SignJWT({
    username: user.username,
    email: user.email,
    role: user.role
  })
    .setProtectedHeader({ alg: 'HS256' })
    .setSubject(String(user.id))
    .setIssuedAt()
    .setExpirationTime('8h')
    .sign(secretKey());

  cookies().set(cookieName, token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: 60 * 60 * 8
  });
}

export function clearSession() {
  cookies().delete(cookieName);
}

export async function getCurrentUserFromRequest(request: NextRequest): Promise<AuthUser | null> {
  const token = request.cookies.get(cookieName)?.value;

  if (!token) {
    return null;
  }

  try {
    const verified = await jwtVerify(token, secretKey());
    const payload = verified.payload;

    return {
      id: Number(payload.sub),
      username: String(payload.username || ''),
      email: String(payload.email || ''),
      role: String(payload.role || 'author') as AuthUser['role']
    };
  } catch {
    return null;
  }
}

export async function requireAdmin(request: NextRequest) {
  const user = await getCurrentUserFromRequest(request);

  if (!user || !['admin', 'editor'].includes(user.role)) {
    return null;
  }

  return user;
}
