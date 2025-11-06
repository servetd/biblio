<?php

namespace App\Parser;

class RISParser
{
    public static function parse(string $content): array
    {
        $lines = explode("\n", $content);
        $publications = [];
        $currentEntry = [];
        $authors = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Check for tag - value pattern
            if (!preg_match('/^([A-Z][A-Z0-9])  - (.*)$/', $line, $matches)) {
                continue;
            }

            $tag = $matches[1];
            $value = trim($matches[2]);

            switch ($tag) {
                case 'TY': // Type of reference
                    if (!empty($currentEntry)) {
                        $currentEntry['authors'] = implode(', ', $authors);
                        $publications[] = self::normalizeEntry($currentEntry);
                        $currentEntry = [];
                        $authors = [];
                    }
                    $currentEntry['type'] = $value;
                    break;

                case 'TI': // Title
                case 'T1':
                    $currentEntry['title'] = $value;
                    break;

                case 'AU': // Author
                case 'A1':
                    $authors[] = $value;
                    break;

                case 'PY': // Publication year
                case 'Y1':
                    $currentEntry['year'] = substr($value, 0, 4);
                    break;

                case 'JO': // Journal
                case 'JF':
                case 'T2':
                    $currentEntry['journal'] = $value;
                    break;

                case 'VL': // Volume
                    $currentEntry['volume'] = $value;
                    break;

                case 'IS': // Issue
                    $currentEntry['number'] = $value;
                    break;

                case 'SP': // Start page
                    $currentEntry['pages'] = $value;
                    break;

                case 'EP': // End page
                    if (isset($currentEntry['pages'])) {
                        $currentEntry['pages'] .= '-' . $value;
                    } else {
                        $currentEntry['pages'] = $value;
                    }
                    break;

                case 'PB': // Publisher
                    $currentEntry['publisher'] = $value;
                    break;

                case 'DO': // DOI
                    $currentEntry['doi'] = $value;
                    break;

                case 'UR': // URL
                case 'L1':
                case 'L2':
                    if (!isset($currentEntry['url'])) {
                        $currentEntry['url'] = $value;
                    }
                    break;

                case 'AB': // Abstract
                case 'N2':
                    $currentEntry['abstract'] = $value;
                    break;

                case 'KW': // Keywords
                    if (isset($currentEntry['keywords'])) {
                        $currentEntry['keywords'] .= ', ' . $value;
                    } else {
                        $currentEntry['keywords'] = $value;
                    }
                    break;

                case 'ID': // Citation key
                    $currentEntry['citation_key'] = $value;
                    break;

                case 'ER': // End of reference
                    if (!empty($currentEntry)) {
                        $currentEntry['authors'] = implode(', ', $authors);
                        $publications[] = self::normalizeEntry($currentEntry);
                        $currentEntry = [];
                        $authors = [];
                    }
                    break;
            }
        }

        // Add last entry if exists
        if (!empty($currentEntry)) {
            $currentEntry['authors'] = implode(', ', $authors);
            $publications[] = self::normalizeEntry($currentEntry);
        }

        return $publications;
    }

    private static function normalizeEntry(array $entry): array
    {
        return [
            'citation_key' => $entry['citation_key'] ?? '',
            'type' => self::convertType($entry['type'] ?? ''),
            'title' => $entry['title'] ?? '',
            'authors' => $entry['authors'] ?? '',
            'year' => $entry['year'] ?? '',
            'journal' => $entry['journal'] ?? '',
            'volume' => $entry['volume'] ?? '',
            'number' => $entry['number'] ?? '',
            'pages' => $entry['pages'] ?? '',
            'publisher' => $entry['publisher'] ?? '',
            'doi' => $entry['doi'] ?? '',
            'url' => $entry['url'] ?? '',
            'abstract' => $entry['abstract'] ?? '',
            'keywords' => $entry['keywords'] ?? '',
        ];
    }

    private static function convertType(string $risType): string
    {
        $typeMap = [
            'JOUR' => 'article',
            'BOOK' => 'book',
            'CHAP' => 'inbook',
            'CONF' => 'inproceedings',
            'THES' => 'phdthesis',
            'RPRT' => 'techreport',
            'UNPB' => 'unpublished',
        ];

        return $typeMap[$risType] ?? strtolower($risType);
    }
}
