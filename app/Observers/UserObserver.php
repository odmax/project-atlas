<?php

namespace App\Observers;

use App\Models\User;
use App\Services\SystemWiringService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    protected SystemWiringService $wiringService;

    public function __construct(SystemWiringService $wiringService)
    {
        $this->wiringService = $wiringService;
    }

    public function created(User $user): void
    {
        $this->wiringService->triggerUserCreated($user);
    }

    public function updated(User $user): void
    {
        if ($user->isDirty('employment_status')) {
            $previousStatus = $user->getOriginal('employment_status');
            $currentStatus = $user->employment_status;

            if ($previousStatus !== 'suspended' && $currentStatus === 'suspended') {
                $this->wiringService->triggerUserSuspended($user);
            }

            if ($previousStatus === 'suspended' && $currentStatus !== 'suspended') {
                $this->wiringService->triggerUserUnsuspended($user);
            }

            if ($previousStatus === 'inactive' && $currentStatus === 'active') {
                $this->wiringService->triggerUserUnsuspended($user);
            }

            if ($previousStatus !== 'inactive' && $currentStatus === 'inactive') {
                $this->wiringService->triggerUserSuspended($user);
            }
        }
    }
}