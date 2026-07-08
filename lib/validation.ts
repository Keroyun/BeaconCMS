export function asString(value: unknown, fallback = '') {
  return typeof value === 'string' ? value.trim() : fallback;
}

export function asOptionalString(value: unknown) {
  const text = asString(value);
  return text === '' ? null : text;
}

export function asStatus(value: unknown, allowed: string[], fallback: string) {
  const text = asString(value);
  return allowed.includes(text) ? text : fallback;
}

export function asNumber(value: unknown, fallback = 0) {
  const numeric = Number(value);
  return Number.isFinite(numeric) ? numeric : fallback;
}

export async function readJson(request: Request) {
  try {
    return await request.json();
  } catch {
    return null;
  }
}
