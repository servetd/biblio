<?php

namespace App\Parser;

use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;

class BibTeXParser
{
    public static function parse(string $content): array
    {
        $parser = new Parser();
        $listener = new Listener();

        $parser->addListener($listener);
        $parser->parseString($content);

        $entries = $listener->export();
        $publications = [];

        foreach ($entries as $entry) {
            if (!isset($entry['type'])) {
                continue;
            }

            $publications[] = self::normalizeEntry($entry);
        }

        return $publications;
    }

    private static function normalizeEntry(array $entry): array
    {
        return [
            'citation_key' => $entry['citation-key'] ?? '',
            'type' => $entry['type'] ?? '',
            'title' => self::cleanValue($entry['title'] ?? ''),
            'authors' => self::formatAuthors($entry['author'] ?? ''),
            'year' => $entry['year'] ?? '',
            'journal' => $entry['journal'] ?? $entry['booktitle'] ?? '',
            'volume' => $entry['volume'] ?? '',
            'number' => $entry['number'] ?? '',
            'pages' => $entry['pages'] ?? '',
            'publisher' => $entry['publisher'] ?? '',
            'doi' => $entry['doi'] ?? '',
            'url' => $entry['url'] ?? '',
            'abstract' => self::cleanValue($entry['abstract'] ?? ''),
            'keywords' => $entry['keywords'] ?? '',
        ];
    }

    private static function formatAuthors(string $authors): string
    {
        if (empty($authors)) {
            return '';
        }

        // Remove extra whitespace
        $authors = preg_replace('/\s+/', ' ', $authors);

        // Replace ' and ' with comma
        $authors = str_replace(' and ', ', ', $authors);

        return $authors;
    }

    private static function cleanValue(string $value): string
    {
        // Remove curly braces used in BibTeX
        $value = str_replace(['{', '}'], '', $value);

        // Remove extra whitespace
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }
}
