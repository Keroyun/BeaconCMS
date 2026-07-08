import fs from 'node:fs/promises';
import path from 'node:path';
import mysql from 'mysql2/promise';

const required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];

for (const key of required) {
  if (!process.env[key]) {
    throw new Error(`Missing required environment variable: ${key}`);
  }
}

const schemaPath = path.join(process.cwd(), 'database', 'schema.sql');
const sql = await fs.readFile(schemaPath, 'utf8');

const connection = await mysql.createConnection({
  host: process.env.DB_HOST,
  port: Number(process.env.DB_PORT || '3306'),
  database: process.env.DB_NAME,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  multipleStatements: true
});

try {
  await connection.query(sql);
  console.log('Database schema migrated.');
} finally {
  await connection.end();
}
