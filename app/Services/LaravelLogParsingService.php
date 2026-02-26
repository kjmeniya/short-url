<?php

namespace App\Services;

use App\Models\LaravelLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LaravelLogParsingService
{
    protected array $logLevels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug'
    ];

    /**
     * Parse and import Laravel log files.
     */
    public function parseLogFiles(string $logPath = null): array
    {
        $logPath = $logPath ?: storage_path('logs');
        $results = [
            'processed' => 0,
            'imported' => 0,
            'errors' => 0,
            'files' => []
        ];

        if (!File::exists($logPath)) {
            throw new \Exception("Log directory not found: {$logPath}");
        }

        $logFiles = File::glob($logPath . '/*.log');

        foreach ($logFiles as $filePath) {
            try {
                $fileResult = $this->parseLogFile($filePath);
                $results['processed'] += $fileResult['processed'];
                $results['imported'] += $fileResult['imported'];
                $results['files'][] = [
                    'file' => basename($filePath),
                    'processed' => $fileResult['processed'],
                    'imported' => $fileResult['imported']
                ];
            } catch (\Exception $e) {
                $results['errors']++;
                $results['files'][] = [
                    'file' => basename($filePath),
                    'error' => $e->getMessage()
                ];
                Log::error("Error parsing log file {$filePath}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Parse a single log file.
     */
    public function parseLogFile(string $filePath): array
    {
        $results = ['processed' => 0, 'imported' => 0];

        if (!File::exists($filePath)) {
            throw new \Exception("Log file not found: {$filePath}");
        }

        $content = File::get($filePath);

        // Ensure content is UTF-8 encoded
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            Log::warning("Log file {$filePath} was not in UTF-8 encoding, converted automatically");
        }

        $logEntries = $this->extractLogEntries($content, $filePath);

        foreach ($logEntries as $entry) {
            $results['processed']++;

            // Check if this log entry already exists
            if (!$this->logEntryExists($entry)) {
                $this->createLogEntry($entry);
                $results['imported']++;
            }
        }

        return $results;
    }

    /**
     * Extract log entries from log file content.
     */
    protected function extractLogEntries(string $content, string $filePath): array
    {
        $entries = [];

        // Ensure content is properly encoded
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }

        $lines = explode("\n", $content);
        $currentEntry = null;
        $lineNumber = 0;

        foreach ($lines as $line) {
            $lineNumber++;

            if (empty(trim($line))) {
                continue;
            }

            // Check if this line starts a new log entry
            if ($this->isLogEntryStart($line)) {
                // Save previous entry if exists
                if ($currentEntry) {
                    $entries[] = $this->finalizeLogEntry($currentEntry, $filePath);
                }

                // Start new entry
                $currentEntry = $this->parseLogEntryStart($line, $lineNumber);
            } elseif ($currentEntry) {
                // Append to current entry
                $currentEntry['message'] .= "\n" . $line;

                // Check for stack trace or exception details
                if (Str::contains($line, ['Stack trace:', 'Exception:', 'Error:'])) {
                    $currentEntry['stack_trace'] = ($currentEntry['stack_trace'] ?? '') . "\n" . $line;
                }
            }
        }

        // Don't forget the last entry
        if ($currentEntry) {
            $entries[] = $this->finalizeLogEntry($currentEntry, $filePath);
        }

        return $entries;
    }

    /**
     * Check if a line starts a new log entry.
     */
    protected function isLogEntryStart(string $line): bool
    {
        // Laravel log format: [YYYY-MM-DD HH:MM:SS] environment.LEVEL: message
        return preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line);
    }

    /**
     * Parse the start of a log entry.
     */
    protected function parseLogEntryStart(string $line, int $lineNumber): array
    {
        // Extract timestamp, environment, level, and initial message
        preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)$/', $line, $matches);

        if (count($matches) < 5) {
            // Fallback parsing
            preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(\w+): (.*)$/', $line, $fallbackMatches);
            $matches = $fallbackMatches;
        }

        $timestamp = $matches[1] ?? now()->format('Y-m-d H:i:s');
        $environment = $matches[2] ?? config('app.env', 'production');
        $level = strtolower($matches[3] ?? 'info');
        $message = $matches[4] ?? $line;

        // Ensure level is valid
        if (!in_array($level, $this->logLevels)) {
            $level = 'info';
        }

        $loggedAt = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp);

        return [
            'logged_at' => $loggedAt,
            'environment' => $environment,
            'level' => $level,
            'message' => $message,
            'line_number' => $lineNumber,
            'log_month' => $loggedAt->format('Y-m'),
            'log_date' => $loggedAt->format('Y-m-d'),
            'channel' => 'laravel',
            'context' => null,
            'extra' => null,
            'stack_trace' => null,
        ];
    }

    /**
     * Finalize a log entry by extracting additional information.
     */
    protected function finalizeLogEntry(array $entry, string $filePath): array
    {
        $entry['file_path'] = $filePath;

        // Extract context and exception information from message
        $this->extractContextFromMessage($entry);
        $this->extractExceptionFromMessage($entry);
        $this->extractRequestInfoFromMessage($entry);

        return $entry;
    }

    /**
     * Extract context information from log message.
     */
    protected function extractContextFromMessage(array &$entry): void
    {
        $message = $entry['message'];

        // Look for JSON context in the message
        if (preg_match('/\{.*\}/', $message, $matches)) {
            $jsonString = $matches[0];
            $context = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $entry['context'] = json_encode($context);
                // Remove context from message
                $entry['message'] = trim(str_replace($jsonString, '', $message));
            }
        }
    }

    /**
     * Extract exception information from log message.
     */
    protected function extractExceptionFromMessage(array &$entry): void
    {
        $message = $entry['message'];

        // Look for exception class
        if (preg_match('/(\w+Exception|\w+Error)/', $message, $matches)) {
            $entry['exception_class'] = $matches[1];
        }

        // Look for file and line information
        if (preg_match('/in (.+):(\d+)/', $message, $matches)) {
            $entry['metadata'] = json_encode([
                'exception_file' => $matches[1],
                'exception_line' => (int)$matches[2]
            ]);
        }
    }

    /**
     * Extract request information from log message.
     */
    protected function extractRequestInfoFromMessage(array &$entry): void
    {
        $message = $entry['message'];

        // Extract request ID if present
        if (preg_match('/request_id[:\s]+([a-zA-Z0-9-]+)/', $message, $matches)) {
            $entry['request_id'] = $matches[1];
        }

        // Extract user ID if present
        if (preg_match('/user_id[:\s]+(\d+)/', $message, $matches)) {
            $entry['user_id'] = $matches[1];
        }

        // Extract IP address if present
        if (preg_match('/ip[:\s]+(\d+\.\d+\.\d+\.\d+)/', $message, $matches)) {
            $entry['ip_address'] = $matches[1];
        }

        // Extract URL if present
        if (preg_match('/url[:\s]+(https?:\/\/[^\s]+)/', $message, $matches)) {
            $entry['url'] = $matches[1];
        }

        // Extract HTTP method if present
        if (preg_match('/method[:\s]+(GET|POST|PUT|DELETE|PATCH|HEAD|OPTIONS)/', $message, $matches)) {
            $entry['method'] = $matches[1];
        }
    }

    /**
     * Check if a log entry already exists in the database.
     */
    protected function logEntryExists(array $entry): bool
    {
        return LaravelLog::where('logged_at', $entry['logged_at'])
            ->where('level', $entry['level'])
            ->where('message', $entry['message'])
            ->where('line_number', $entry['line_number'])
            ->exists();
    }

    /**
     * Create a new log entry in the database.
     */
    protected function createLogEntry(array $entry): LaravelLog
    {
        return LaravelLog::create($entry);
    }

    /**
     * Get available log months.
     */
    public function getAvailableMonths(): array
    {
        return LaravelLog::select('log_month')
            ->distinct()
            ->orderBy('log_month', 'desc')
            ->pluck('log_month')
            ->toArray();
    }

    /**
     * Get log statistics.
     */
    public function getLogStats(array $filters = []): array
    {
        $baseQuery = LaravelLog::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $baseQuery->where('logged_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $baseQuery->where('logged_at', '<=', $filters['date_to']);
        }

        if (isset($filters['level'])) {
            $baseQuery->where('level', $filters['level']);
        }

        if (isset($filters['environment'])) {
            $baseQuery->where('environment', $filters['environment']);
        }

        $total = (clone $baseQuery)->count();
        $errors = (clone $baseQuery)->errors()->count();
        $warnings = (clone $baseQuery)->warnings()->count();
        $recent = (clone $baseQuery)->recent(24)->count();

        return [
            'total' => $total,
            'errors' => $errors,
            'warnings' => $warnings,
            'recent_24h' => $recent,
            'error_rate' => $total > 0 ? round($errors / $total * 100, 2) : 0,
        ];
    }
}
