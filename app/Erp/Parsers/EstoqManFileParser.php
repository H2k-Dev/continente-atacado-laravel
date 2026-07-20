<?php

namespace App\Erp\Parsers;

/**
 * Parses EstoqMan PDV integration files (NT/PDV 8).
 *
 * Format: pseudo-XML lines using asc 60/62 delimiters and pipe separators.
 * Example: <cp|descricao>Leite integral</cp|descricao>
 */
class EstoqManFileParser
{
    /**
     * @return list<array<string, string>>
     */
    public function parseRecords(string $contents, string $recordType): array
    {
        $records = [];
        $current = null;
        $depth = 0;

        foreach (preg_split('/\r\n|\r|\n/', $contents) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^<rg\|'.preg_quote($recordType, '/').'>$/i', $line)) {
                $current = [];
                $depth = 1;

                continue;
            }

            if (preg_match('/^<\/rg\|'.preg_quote($recordType, '/').'>$/i', $line) && $current !== null) {
                $records[] = $current;
                $current = null;
                $depth = 0;

                continue;
            }

            if ($current !== null && preg_match('/^<cp\|([^>]+)>(.*)<\/cp\|\1>$/i', $line, $matches)) {
                $current[strtolower($matches[1])] = $matches[2];
            }
        }

        return $records;
    }

    public function hasFullReloadCommand(string $contents): bool
    {
        return (bool) preg_match('/<cm\|comando>\s*limpa\s*<\/cm\|comando>/i', $contents);
    }
}