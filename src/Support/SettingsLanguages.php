<?php namespace Seiger\sSettings\Support;

/** Resolves the optional sLang integration without requiring that package. */
final class SettingsLanguages
{
    public function available(): bool
    {
        return class_exists(\Seiger\sLang\sLang::class) && app()->bound('sLang');
    }

    /** @return list<string> */
    public function locales(): array
    {
        if (!$this->available()) {
            return [];
        }

        $languages = app('sLang');
        return array_values(array_unique(array_filter(array_map(
            fn ($locale): string => trim((string) $locale),
            [$languages->langDefault(), ...$languages->langConfig()]
        ), fn (string $locale): bool => (bool) preg_match('/^[a-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/D', $locale))));
    }

    public function defaultLocale(): string
    {
        return $this->locales()[0] ?? '';
    }
}
