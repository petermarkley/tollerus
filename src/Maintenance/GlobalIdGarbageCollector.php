<?php

namespace PeterMarkley\Tollerus\Maintenance;

use Illuminate\Support\Facades\DB;

/**
 * Whenever a Glyph, Entry, Lexeme, or Form gets deleted by the database as
 * an indirect `ON DELETE CASCADE` target, for example if a user deletes a
 * language or a word class or a neography section etc., in many
 * implementations this does not invoke the relevant `AFTER DELETE`
 * database triggers and a row in `global_ids` gets orphaned.
 *
 * While the application tries to be robust about behaving correctly in
 * this situation, it's still best to remove these and keep `global_ids` in
 * sync with the other tables. But the cascade routes for deletion are too
 * myriad to comfortably address with taylored semantics, so we take a
 * blanket "garbage collection" approach.
 */
final class GlobalIdGarbageCollector
{
    public function collect(): int
    {
        $conn = DB::connection(config('tollerus.connection'));
        $prefix = $conn->getTablePrefix();

        $gid = $prefix.'global_ids';
        $entries = $prefix.'entries';
        $lexemes = $prefix.'lexemes';
        $forms = $prefix.'forms';
        $glyphs = $prefix.'neography_glyphs';

        $affected = $conn->affectingStatement(<<<SQL
DELETE gid
FROM {$gid} gid
WHERE NOT EXISTS (SELECT 1 FROM {$entries} e WHERE e.global_id_raw = gid.global_id_raw)
  AND NOT EXISTS (SELECT 1 FROM {$lexemes} l WHERE l.global_id_raw = gid.global_id_raw)
  AND NOT EXISTS (SELECT 1 FROM {$forms} f WHERE f.global_id_raw = gid.global_id_raw)
  AND NOT EXISTS (SELECT 1 FROM {$glyphs} g WHERE g.global_id_raw = gid.global_id_raw)
SQL
        );
        // \Illuminate\Support\Facades\Log::info("Deleted {$affected} orphaned global IDs.");
        return $affected;
    }

    /**
     * For debugging purposes, if needed ...
     */
    public function show(): ?array
    {
        $conn = DB::connection(config('tollerus.connection'));
        $prefix = $conn->getTablePrefix();

        $gid = $prefix.'global_ids';
        $entries = $prefix.'entries';
        $lexemes = $prefix.'lexemes';
        $forms = $prefix.'forms';
        $glyphs = $prefix.'neography_glyphs';

        $result = $conn->select(<<<SQL
SELECT gid.global_id_raw, gid.kind
  FROM {$gid} gid
  LEFT JOIN {$entries} e ON e.global_id_raw = gid.global_id_raw
  LEFT JOIN {$lexemes} l ON l.global_id_raw = gid.global_id_raw
  LEFT JOIN {$forms} f ON f.global_id_raw = gid.global_id_raw
  LEFT JOIN {$glyphs} g ON g.global_id_raw = gid.global_id_raw
  WHERE e.global_id_raw IS NULL AND l.global_id_raw IS NULL
  AND f.global_id_raw IS NULL AND g.global_id_raw IS NULL
SQL
        );
        return $result;
    }
}
