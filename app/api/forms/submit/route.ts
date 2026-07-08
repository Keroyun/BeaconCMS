import { headers } from 'next/headers';
import { fail, ok, serverError } from '@/lib/api-response';
import { execute, queryOne } from '@/lib/db';
import { asString, readJson } from '@/lib/validation';

export const dynamic = 'force-dynamic';

type FormRow = {
  id: number;
  shortcode: string;
  status: 'active' | 'inactive';
};

export async function POST(request: Request) {
  try {
    const body = await readJson(request);

    if (!body || typeof body !== 'object') {
      return fail('Invalid JSON payload.');
    }

    const payload = body as Record<string, unknown>;
    const shortcode = asString(payload.shortcode);
    const data = payload.data;

    if (!shortcode || !data || typeof data !== 'object' || Array.isArray(data)) {
      return fail('Form shortcode and data are required.');
    }

    const form = await queryOne<FormRow>(
      `SELECT id, shortcode, status FROM forms WHERE shortcode = :shortcode AND status = 'active' LIMIT 1`,
      { shortcode }
    );

    if (!form) {
      return fail('Form not found.', 404);
    }

    const headerList = headers();
    const ip =
      headerList.get('x-forwarded-for')?.split(',')[0]?.trim() ||
      headerList.get('x-real-ip') ||
      null;

    await execute(
      `INSERT INTO form_entries (form_id, entry_data_json, ip_address, user_agent)
       VALUES (:formId, :entryData, :ip, :userAgent)`,
      {
        formId: form.id,
        entryData: JSON.stringify(data),
        ip,
        userAgent: headerList.get('user-agent') || null
      }
    );

    return ok({ submitted: true }, { status: 201 });
  } catch (error) {
    return serverError(error);
  }
}
