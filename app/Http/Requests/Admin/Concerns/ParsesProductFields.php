<?php

namespace App\Http\Requests\Admin\Concerns;

trait ParsesProductFields
{
    /**
     * Comma-separated tags textbox -> list of trimmed tag strings.
     *
     * @return list<string>
     */
    private function parseTags(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return collect(explode(',', $text))->map(fn (string $tag) => trim($tag))->filter()->values()->all();
    }

    /**
     * "Key | Value" per line textarea -> associative specs array.
     *
     * @return array<string, string>
     */
    private function parseSpecs(?string $text): array
    {
        if (! $text) {
            return [];
        }

        $specs = [];

        foreach (explode("\n", $text) as $line) {
            [$key, $value] = array_pad(explode('|', trim($line), 2), 2, '');
            $key = trim($key);

            if ($key !== '') {
                $specs[$key] = trim($value);
            }
        }

        return $specs;
    }
}
