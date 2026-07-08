import mysql from 'mysql2/promise';
import { getEnv, getOptionalEnv } from '@/lib/env';

let pool: mysql.Pool | undefined;

export function getPool() {
  if (!pool) {
    pool = mysql.createPool({
      host: getEnv('DB_HOST'),
      port: Number(getOptionalEnv('DB_PORT', '3306')),
      database: getEnv('DB_NAME'),
      user: getEnv('DB_USER'),
      password: getEnv('DB_PASSWORD'),
      waitForConnections: true,
      connectionLimit: 8,
      namedPlaceholders: true,
      timezone: 'Z'
    });
  }

  return pool;
}

export async function queryRows<T>(sql: string, params: Record<string, unknown> = {}) {
  const [rows] = await getPool().execute(sql, params);
  return rows as T[];
}

export async function queryOne<T>(sql: string, params: Record<string, unknown> = {}) {
  const rows = await queryRows<T>(sql, params);
  return rows[0] ?? null;
}

export async function execute(sql: string, params: Record<string, unknown> = {}) {
  const [result] = await getPool().execute(sql, params);
  return result as mysql.ResultSetHeader;
}
