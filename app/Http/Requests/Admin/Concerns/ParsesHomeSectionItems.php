<?php

namespace App\Http\Requests\Admin\Concerns;

trait ParsesHomeSectionItems
{
    /**
     * Parse a "one entry per line" textarea into structured items for the given section type.
     * features, business_opportunity, process_steps: "Title | Description" -> [{title, description}]
     * stats: "Label | Value" -> [{label, value}]
     * team: "Name | Role" -> [{name, role}]
     * hero: "Trust badge label" per line -> [{title, ''}]
     *
     * @return list<array<string, string>>|null
     */
    private function parseItems(?string $text, string $type): ?array
    {
        $titleDescriptionTypes = ['features', 'business_opportunity', 'process_steps', 'hero'];

        if (! in_array($type, [...$titleDescriptionTypes, 'stats', 'team'], true) || ! $text) {
            return null;
        }

        $keysByType = ['stats' => ['label', 'value'], 'team' => ['name', 'role']];
        [$firstKey, $secondKey] = $keysByType[$type] ?? ['title', 'description'];

        return collect(explode("\n", $text))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->map(function (string $line) use ($firstKey, $secondKey) {
                [$first, $second] = array_pad(explode('|', $line, 2), 2, '');

                return [$firstKey => trim($first), $secondKey => trim($second)];
            })
            ->values()
            ->all();
    }
}
