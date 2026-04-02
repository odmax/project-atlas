<?php

namespace App\Filament\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyFilamentPermissions
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();
        
        if (!$panel) {
            return $next($request);
        }

        $routeName = $request->route()->getName() ?? '';
        
        $resource = $this->getResourceFromRoute($routeName);
        
        if ($resource) {
            $permission = $this->getPermission($routeName, $resource);
            
            if ($permission && !$request->user()->can($permission)) {
                abort(403, 'You do not have permission to access this resource.');
            }
        }

        return $next($request);
    }

    protected function getResourceFromRoute(string $routeName): ?string
    {
        if (str_contains($routeName, 'users')) {
            return 'users';
        }
        
        if (str_contains($routeName, 'connectors')) {
            return 'connectors';
        }
        
        if (str_contains($routeName, 'linked-accounts')) {
            return 'linked_accounts';
        }

        return null;
    }

    protected function getPermission(string $routeName, string $resource): ?string
    {
        if (str_contains($routeName, '.index')) {
            return "{$resource}.view";
        }
        
        if (str_contains($routeName, '.create')) {
            return "{$resource}.create";
        }
        
        if (str_contains($routeName, '.edit')) {
            return "{$resource}.edit";
        }
        
        if (str_contains($routeName, '.delete')) {
            return "{$resource}.delete";
        }

        return null;
    }
}
