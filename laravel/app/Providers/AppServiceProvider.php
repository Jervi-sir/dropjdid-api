<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();

        if (
            app()->isProduction() ||
            str_starts_with(config('app.url'), 'https://') ||
            $this->serverHasIpPrefix('91.') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ) {
            URL::forceScheme('https');
        }

        // Define authorization gate for Log Viewer
        Gate::define('viewLogViewer', function ($user = null) {
            if (config('log-viewer.bypass_auth')) {
                return true;
            }

            $allowedEmails = env('LOG_VIEWER_ALLOWED_EMAILS') ? explode(',', env('LOG_VIEWER_ALLOWED_EMAILS')) : [];
            if ($user && in_array($user->email, $allowedEmails)) {
                return true;
            }

            return false;
        });
    }

    /**
     * Determine if the server has a network interface IP starting with the given prefix.
     */
    protected function serverHasIpPrefix(string $prefix): bool
    {
        if (!function_exists('net_get_interfaces')) {
            return false;
        }

        $interfaces = net_get_interfaces();
        if ($interfaces === false) {
            return false;
        }

        foreach ($interfaces as $details) {
            if (!empty($details['unicast'])) {
                foreach ($details['unicast'] as $unicast) {
                    if (isset($unicast['address']) && str_starts_with($unicast['address'], $prefix)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
