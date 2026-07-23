<?php

namespace App\Services\Security;

use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpFoundation\IpUtils;

class TrustedAccessPolicy
{
    public function allows(?Authenticatable $user, ?string $ip): bool
    {
        if (! $user) {
            return false;
        }

        $unrestrictedUserIds = array_map(
            'intval',
            (array) config('access_control.unrestricted_user_ids', [])
        );

        if (in_array((int) $user->getAuthIdentifier(), $unrestrictedUserIds, true)) {
            return true;
        }

        $allowedIps = array_values(array_filter(
            array_map('trim', (array) config('access_control.allowed_ips', []))
        ));

        return $ip !== null
            && $ip !== ''
            && $allowedIps !== []
            && IpUtils::checkIp($ip, $allowedIps);
    }
}
