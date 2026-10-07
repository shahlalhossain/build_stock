<?php

namespace App\Services\Notification;

/**
 * Removes secrets (keys, passwords, tokens, authorization headers) from provider
 * answers and error messages BEFORE we save them in the database.
 * Channels should already avoid secrets; this is a second safety net.
 */
class ResponseSanitizer
{
    /**
     * Words that mark a field name as secret.
     */
    protected const SECRET_WORDS = ['password', 'passwd', 'secret', 'token', 'authorization', 'api_key', 'apikey', 'credential', 'private_key', 'bearer'];

    /**
     * The text shown instead of a secret value.
     */
    protected const HIDDEN = '[hidden]';

    /**
     * The longest text we keep for a single value.
     */
    protected const MAX_LENGTH = 2000;

    /**
     * Cleans a list of values (including nested lists): secret fields are hidden and long texts are cut.
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function sanitizeArray(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && $this->looksSecret($key)) {
                $clean[$key] = self::HIDDEN;
            } elseif (is_array($value)) {
                $clean[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $clean[$key] = $this->sanitizeText($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    /**
     * Cleans one text: hides "Bearer xxx", "password=xxx", "api_key: xxx" style secrets and cuts it if too long.
     */
    public function sanitizeText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = preg_replace('/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/i', 'Bearer '.self::HIDDEN, $text);
        $text = preg_replace('/\b(password|passwd|secret|token|api[_-]?key|authorization)\b(["\']?\s*[:=]\s*["\']?)[^\s"\'&,;]+/i', '$1$2'.self::HIDDEN, $text);

        return mb_substr($text, 0, self::MAX_LENGTH);
    }

    /**
     * Says whether a field name sounds like it holds a secret.
     */
    protected function looksSecret(string $key): bool
    {
        $key = strtolower($key);

        foreach (self::SECRET_WORDS as $word) {
            if (str_contains($key, $word)) {
                return true;
            }
        }

        return false;
    }
}
