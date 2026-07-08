const requiredServerEnv = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'AUTH_SECRET'] as const;

export function getEnv(name: (typeof requiredServerEnv)[number]) {
  const value = process.env[name];

  if (!value) {
    throw new Error(`Missing required environment variable: ${name}`);
  }

  return value;
}

export function getOptionalEnv(name: string, fallback = '') {
  return process.env[name] || fallback;
}
