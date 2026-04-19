<?php

declare(strict_types=1);

namespace WizdamDebugToolbar\Adapters;

use WizdamDebugToolbar\Interfaces\DatabaseAdapterInterface;

/**
 * AdodbDatabaseAdapter
 *
 * Implementasi DatabaseAdapterInterface untuk aplikasi berbasis ADODB,
 * khususnya OJS 2.4.8.5.
 *
 * ---
 * CARA INTEGRASI DI OJS:
 *
 * Karena ADODB tidak memiliki event hook bawaan untuk query logging,
 * adapter ini menggunakan pendekatan static accumulator.
 * Anda perlu memanggil AdodbDatabaseAdapter::logQuery() setiap kali
 * query dieksekusi.
 *
 * Cara paling bersih adalah dengan meng-extend kelas DAORegistry
 * atau membuat wrapper di titik eksekusi query OJS:
 *
 *   // Di file bootstrap OJS atau custom DAO wrapper:
 *   $start = microtime(true);
 *   $result = $dbconn->Execute($sql, $params);
 *   $duration = (microtime(true) - $start) * 1000;
 *   AdodbDatabaseAdapter::logQuery($sql, $duration, $params ?? []);
 *
 * Atau, untuk integrasi otomatis, extend ADOConnection:
 *
 *   class WizdamAdodbConnection extends ADOConnection {
 *       public function Execute($sql, $inputarr = false) {
 *           $start  = microtime(true);
 *           $result = parent::Execute($sql, $inputarr);
 *           $ms     = (microtime(true) - $start) * 1000;
 *           AdodbDatabaseAdapter::logQuery(
 *               is_string($sql) ? $sql : $sql->sql,
 *               $ms,
 *               is_array($inputarr) ? $inputarr : []
 *           );
 *           return $result;
 *       }
 *   }
 * ---
 */
class AdodbDatabaseAdapter implements DatabaseAdapterInterface
{
    /** @var array<int, array{sql: string, duration: float, params: array, trace: string}> */
    private static array $queries = [];

    private static float $totalTime = 0.0;

    /**
     * Catat satu query ke dalam log.
     * Dipanggil dari kode aplikasi saat query dieksekusi.
     *
     * @param string $sql      Query SQL yang dieksekusi
     * @param float  $duration Durasi dalam milidetik
     * @param array  $params   Bind parameter (opsional)
     */
    public static function logQuery(string $sql, float $duration, array $params = []): void
    {
        $sql = trim($sql);

        self::$queries[] = [
            'sql'      => $sql,
            'duration' => round($duration, 4),
            'params'   => $params,
            'trace'    => self::buildShortTrace(),
        ];

        self::$totalTime += $duration;
    }

    /**
     * Reset log (berguna untuk testing atau profiling per-segmen).
     */
    public static function reset(): void
    {
        self::$queries   = [];
        self::$totalTime = 0.0;
    }

    // ---------------------------------------------------------------
    // Implementasi DatabaseAdapterInterface
    // ---------------------------------------------------------------

    public function getQueries(): array
    {
        return self::$queries;
    }

    public function getTotalTime(): float
    {
        return round(self::$totalTime, 4);
    }

    public function getQueryCount(): int
    {
        return count(self::$queries);
    }

    public function getDuplicates(): array
    {
        $counts = [];

        foreach (self::$queries as $entry) {
            // Normalisasi whitespace agar query yang sama terdeteksi meski
            // diformat berbeda
            $normalized = preg_replace('/\s+/', ' ', strtolower($entry['sql']));
            $counts[$normalized] = ($counts[$normalized] ?? 0) + 1;
        }

        $duplicates = [];
        foreach ($counts as $sql => $count) {
            if ($count > 1) {
                $duplicates[] = [
                    'sql'   => $sql,
                    'count' => $count,
                ];
            }
        }

        return $duplicates;
    }

    // ---------------------------------------------------------------
    // Helper privat
    // ---------------------------------------------------------------

    /**
     * Buat ringkasan stack trace yang relevan (tanpa noise framework).
     * Hanya menampilkan 3 frame pertama di luar adapter ini.
     */
    private static function buildShortTrace(): string
    {
        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $lines  = [];

        foreach ($frames as $frame) {
            // Lewati frame dari adapter ini sendiri
            if (isset($frame['class']) && $frame['class'] === self::class) {
                continue;
            }

            $file     = isset($frame['file']) ? basename($frame['file']) : '[internal]';
            $line     = $frame['line'] ?? 0;
            $function = $frame['function'] ?? '';
            $class    = isset($frame['class']) ? $frame['class'] . '::' : '';

            $lines[] = "{$file}:{$line} {$class}{$function}()";

            if (count($lines) >= 3) {
                break;
            }
        }

        return implode(' → ', $lines);
    }
}