<?php

namespace App\Services;

/**
 * Site-wide font choices, editable from Admin → Site Settings → Typography.
 *
 * The stylesheet reads three CSS variables — --bs-font-sans-serif (body),
 * --font-heading and --font-display — plus --bs-body-font-size. The SCSS
 * build keeps sensible defaults; this service turns the saved settings into
 * a Google Fonts URL and a :root override that every layout prints after
 * the compiled CSS (see partials/typography.blade.php).
 *
 * Only fonts from the curated list below can be chosen, so nothing typed by
 * an admin ever reaches the <style> block or the font URL.
 */
class TypographyService
{
    public const DEFAULT_BODY = 'Instrument Sans';

    public const DEFAULT_HEADING = 'Manrope';

    public const DEFAULT_DISPLAY = 'Playfair Display';

    public const DEFAULT_SIZE = 16;

    public const MIN_SIZE = 14;

    public const MAX_SIZE = 18;

    private const SANS = "ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif";

    private const SERIF = 'ui-serif, Georgia, serif';

    /**
     * Font name => [category, weights to load].
     *
     * @var array<string, array{0: 'sans'|'serif', 1: string}>
     */
    private const FONTS = [
        // Sans-serif
        'Instrument Sans' => ['sans', '400;500;600;700'],
        'Inter' => ['sans', '400;500;600;700;800'],
        'Manrope' => ['sans', '400;500;600;700;800'],
        'Poppins' => ['sans', '400;500;600;700;800'],
        'Roboto' => ['sans', '400;500;700'],
        'Open Sans' => ['sans', '400;500;600;700;800'],
        'Lato' => ['sans', '400;700'],
        'Montserrat' => ['sans', '400;500;600;700;800'],
        'Nunito' => ['sans', '400;600;700;800'],
        'DM Sans' => ['sans', '400;500;600;700'],
        'Work Sans' => ['sans', '400;500;600;700'],
        'Rubik' => ['sans', '400;500;600;700'],
        'Mulish' => ['sans', '400;600;700;800'],
        'Plus Jakarta Sans' => ['sans', '400;500;600;700;800'],
        'Outfit' => ['sans', '400;500;600;700'],
        'Noto Sans' => ['sans', '400;500;600;700'],
        'Mukta' => ['sans', '400;500;600;700'],
        'Hind' => ['sans', '400;500;600;700'],
        // Serif
        'Playfair Display' => ['serif', '600;700'],
        'Merriweather' => ['serif', '400;700'],
        'Lora' => ['serif', '400;600;700'],
        'Cormorant Garamond' => ['serif', '500;600;700'],
        'Libre Baskerville' => ['serif', '400;700'],
        'DM Serif Display' => ['serif', '400'],
        'Noto Serif' => ['serif', '400;600;700'],
    ];

    public function __construct(private readonly SettingsService $settings)
    {
    }

    /**
     * @return list<string>
     */
    public static function fontNames(): array
    {
        return array_keys(self::FONTS);
    }

    /**
     * Font names grouped for a <select> with optgroups.
     *
     * @return array<string, list<string>>
     */
    public static function groupedOptions(): array
    {
        $groups = ['Sans-serif' => [], 'Serif' => []];

        foreach (self::FONTS as $name => [$category]) {
            $groups[$category === 'serif' ? 'Serif' : 'Sans-serif'][] = $name;
        }

        return $groups;
    }

    /**
     * The resolved choices, falling back to defaults for anything unset or
     * no longer on the list.
     *
     * @return array{body: string, heading: string, display: string, size: int}
     */
    public function current(): array
    {
        $all = $this->settings->all();

        $pick = fn (string $key, string $default) => isset(self::FONTS[$all[$key] ?? ''])
            ? $all[$key]
            : $default;

        $size = (int) ($all['font_size_base'] ?? self::DEFAULT_SIZE);

        return [
            'body' => $pick('font_body', self::DEFAULT_BODY),
            'heading' => $pick('font_heading', self::DEFAULT_HEADING),
            'display' => $pick('font_display', self::DEFAULT_DISPLAY),
            'size' => ($size >= self::MIN_SIZE && $size <= self::MAX_SIZE) ? $size : self::DEFAULT_SIZE,
        ];
    }

    /**
     * One Google Fonts stylesheet URL loading every chosen family.
     */
    public function googleFontsUrl(): string
    {
        $current = $this->current();
        $families = array_unique([$current['body'], $current['heading'], $current['display']]);

        $params = array_map(
            fn (string $name) => 'family='.str_replace(' ', '+', $name).':wght@'.self::FONTS[$name][1],
            $families,
        );

        return 'https://fonts.googleapis.com/css2?'.implode('&', $params).'&display=swap';
    }

    /**
     * CSS custom properties overriding the compiled defaults.
     */
    public function cssVariables(): string
    {
        $current = $this->current();

        return sprintf(
            ':root{--bs-font-sans-serif:%s;--bs-body-font-family:%s;--font-heading:%s;--font-display:%s;--bs-body-font-size:%s}',
            $this->stack($current['body']),
            $this->stack($current['body']),
            $this->stack($current['heading']),
            $this->stack($current['display']),
            rtrim(rtrim(number_format($current['size'] / 16, 4, '.', ''), '0'), '.').'rem',
        );
    }

    private function stack(string $name): string
    {
        return "'{$name}', ".(self::FONTS[$name][0] === 'serif' ? self::SERIF : self::SANS);
    }
}
