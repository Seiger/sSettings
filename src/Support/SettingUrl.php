<?php namespace Seiger\sSettings\Support;

/**
 * Resolve settings used as destinations in HTML URL attributes.
 *
 * @since 2.1.0
 */
final class SettingUrl
{
    /**
     * Accept HTTP and HTTPS URLs or resolve a local path against a trusted site URL.
     *
     * The caller still escapes the result for its HTML attribute.
     *
     * @since 2.1.0
     */
    public static function resolve(mixed $value, ?string $siteUrl = null): ?string
    {
        if (!is_string($value) || preg_match('/[\x00-\x1f\x7f]/', $value) || str_contains($value, '\\')) {
            return null;
        }

        $url = trim($value, ' ');
        $parts = parse_url($url);
        if ($url === '' || $parts === false || str_starts_with($url, '//')) {
            return null;
        }

        if (isset($parts['scheme'])) {
            $host = $parts['host'] ?? '';

            return in_array(strtolower($parts['scheme']), ['http', 'https'], true)
                && $host !== '' && !str_contains($host, ' ') ? $url : null;
        }

        if ($siteUrl === null || ltrim($parts['path'] ?? '', '/') === '') {
            return null;
        }

        return rtrim($siteUrl, '/') . '/' . ltrim($url, '/');
    }
}
