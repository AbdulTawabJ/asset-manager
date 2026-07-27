<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerSqliteCompatFunctions();
    }

    /**
     * The advanced-query feature filters the hierarchical `location` string with
     * MySQL's SUBSTRING_INDEX(). SQLite (used by the zero-setup demo) has no such
     * function, so we register a user-defined function with identical semantics
     * on the SQLite connection only. MySQL/MariaDB behaviour is left untouched.
     */
    private function registerSqliteCompatFunctions(): void
    {
        try {
            $connection = DB::connection();

            if ($connection->getDriverName() !== 'sqlite') {
                return;
            }

            $pdo = $connection->getPdo();

            if (! method_exists($pdo, 'sqliteCreateFunction')) {
                return;
            }

            $pdo->sqliteCreateFunction('SUBSTRING_INDEX', function ($string, $delimiter, $count) {
                if ($string === null) {
                    return null;
                }

                $parts = explode($delimiter, (string) $string);

                if ($count >= 0) {
                    return implode($delimiter, array_slice($parts, 0, $count));
                }

                return implode($delimiter, array_slice($parts, $count));
            }, 3);
        } catch (\Throwable $e) {
            // No usable DB connection yet (e.g. during early console commands) — ignore.
        }
    }
}
