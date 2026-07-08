import bcrypt from 'bcryptjs';
import mysql from 'mysql2/promise';

const required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'ADMIN_USERNAME', 'ADMIN_EMAIL', 'ADMIN_PASSWORD'];

for (const key of required) {
  if (!process.env[key]) {
    throw new Error(`Missing required environment variable: ${key}`);
  }
}

if (process.env.ADMIN_PASSWORD.length < 12) {
  throw new Error('ADMIN_PASSWORD must be at least 12 characters.');
}

const connection = await mysql.createConnection({
  host: process.env.DB_HOST,
  port: Number(process.env.DB_PORT || '3306'),
  database: process.env.DB_NAME,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  namedPlaceholders: true
});

try {
  const hash = await bcrypt.hash(process.env.ADMIN_PASSWORD, 12);

  await connection.execute(
    `INSERT INTO users (username, email, password, role)
     VALUES (:username, :email, :password, 'admin')
     ON DUPLICATE KEY UPDATE email = VALUES(email), password = VALUES(password), role = 'admin'`,
    {
      username: process.env.ADMIN_USERNAME,
      email: process.env.ADMIN_EMAIL,
      password: hash
    }
  );

  console.log('Admin user created or updated.');
} finally {
  await connection.end();
}
