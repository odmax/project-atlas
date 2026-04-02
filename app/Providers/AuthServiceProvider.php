<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        Auth::provider('primary_email', function ($app) {
            return new class($app->make('hash'), User::class) extends EloquentUserProvider {
                public function retrieveByCredentials(array $credentials): ?Authenticatable
                {
                    $username = $credentials['email'] ?? ($credentials['primary_email'] ?? null);

                    if ($username === null) {
                        return null;
                    }

                    $model = $this->createModel();
                    return $model->where('primary_email', $username)->first();
                }
            };
        });
    }
}
