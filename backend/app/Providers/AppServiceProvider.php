<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->environment('development')) {
            // Log all queries in development environment
            DB::listen(function ($query) {
                // Log the raw SQL query with bindings replaced
                $sql = $query->sql;
                $bindings = $query->bindings;

                // Replace bindings in the query (manually bind the values to the SQL)
                foreach ($bindings as $binding) {
                    $sql = preg_replace('/\?/', "'" . addslashes($binding) . "'", $sql, 1);
                }

                // Log the full query with bindings
                Log::info('SQL Query Executed: ' . $sql);
            });
        }
    }
}
