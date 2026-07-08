<?php
/**
 * Sanitizer — Input Cleaning & Validation
 *
 * Static utility class for sanitising user input and validating data
 * against a set of declarative rules.
 */
class Sanitizer
{
    // ── Cleaning ────────────────────────────────────────────────────────────

    /**
     * Strip tags, trim whitespace, and HTML-encode a single value.
     */
    public static function clean(mixed $input): string
    {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim(strip_tags((string) $input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean every value in an associative array (one level deep).
     *
     * @param  array<string,mixed> $input
     * @return array<string,string>
     */
    public static function cleanArray(array $input): array
    {
        $cleaned = [];
        foreach ($input as $key => $value) {
            $cleaned[$key] = self::clean($value);
        }
        return $cleaned;
    }

    // ── Validation ──────────────────────────────────────────────────────────

    /**
     * Validate $data against a set of $rules.
     *
     * Rule format (pipe-separated):
     *   'title' => 'required|max:255'
     *   'email' => 'required|email'
     *   'status' => 'required|in:draft,published'
     *
     * Supported rules:
     *   required, email, url, max:{n}, min:{n}, numeric, date, in:val1,val2,...
     *
     * @param  array<string,mixed>  $data
     * @param  array<string,string> $rules  field => pipe-separated rules
     * @return array<string,string> field => first error message (empty = valid)
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value      = $data[$field] ?? '';
            $ruleParts  = explode('|', $ruleString);
            $fieldLabel = ucfirst(str_replace('_', ' ', $field));

            foreach ($ruleParts as $rule) {
                $param = null;

                // Extract parameter (e.g. max:255 → rule="max", param="255")
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                $error = self::applyRule($value, $rule, $param, $fieldLabel);

                if ($error !== null) {
                    $errors[$field] = $error;
                    break; // Stop at first error per field
                }
            }
        }

        return $errors;
    }

    /**
     * Apply a single validation rule to a value.
     *
     * @return string|null Error message or null if valid
     */
    private static function applyRule(mixed $value, string $rule, ?string $param, string $label): ?string
    {
        $strValue = is_string($value) ? trim($value) : (string) $value;

        switch ($rule) {
            case 'required':
                if ($strValue === '') {
                    return "{$label} is required.";
                }
                break;

            case 'email':
                if ($strValue !== '' && filter_var($strValue, FILTER_VALIDATE_EMAIL) === false) {
                    return "{$label} must be a valid email address.";
                }
                break;

            case 'url':
                if ($strValue !== '' && filter_var($strValue, FILTER_VALIDATE_URL) === false) {
                    return "{$label} must be a valid URL.";
                }
                break;

            case 'max':
                if ($strValue !== '' && mb_strlen($strValue) > (int) $param) {
                    return "{$label} must not exceed {$param} characters.";
                }
                break;

            case 'min':
                if ($strValue !== '' && mb_strlen($strValue) < (int) $param) {
                    return "{$label} must be at least {$param} characters.";
                }
                break;

            case 'numeric':
                if ($strValue !== '' && !is_numeric($strValue)) {
                    return "{$label} must be a number.";
                }
                break;

            case 'date':
                if ($strValue !== '' && strtotime($strValue) === false) {
                    return "{$label} must be a valid date.";
                }
                break;

            case 'in':
                $allowed = explode(',', $param ?? '');
                if ($strValue !== '' && !in_array($strValue, $allowed, true)) {
                    return "{$label} must be one of: " . implode(', ', $allowed) . ".";
                }
                break;
        }

        return null;
    }

    // ── Slug Generation ─────────────────────────────────────────────────────

    /**
     * Convert an arbitrary string to a URL-safe slug.
     *
     * "Hello World! — Test" → "hello-world-test"
     */
    public static function slug(string $string): string
    {
        // Transliterate to ASCII
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        // Lowercase
        $slug = mb_strtolower($slug);

        // Replace non-alphanumeric chars with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Trim leading/trailing hyphens
        return trim($slug, '-');
    }
}
