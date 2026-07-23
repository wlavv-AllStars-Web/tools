<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Security\TrustedAccessPolicy;
use Tests\TestCase;

class TrustedAccessPolicyTest extends TestCase
{
    public function test_unrestricted_users_are_allowed_from_any_ip(): void
    {
        config()->set('access_control.unrestricted_user_ids', [43, 99]);
        config()->set('access_control.allowed_ips', []);

        $user = new User();
        $user->id = 43;

        $this->assertTrue((new TrustedAccessPolicy())->allows($user, '203.0.113.15'));
    }

    public function test_other_users_require_an_allowed_ip_or_network(): void
    {
        config()->set('access_control.unrestricted_user_ids', [43]);
        config()->set('access_control.allowed_ips', ['192.0.2.10', '198.51.100.0/24', '::1']);

        $user = new User();
        $user->id = 75;
        $policy = new TrustedAccessPolicy();

        $this->assertTrue($policy->allows($user, '192.0.2.10'));
        $this->assertTrue($policy->allows($user, '198.51.100.27'));
        $this->assertTrue($policy->allows($user, '::1'));
        $this->assertFalse($policy->allows($user, '203.0.113.15'));
    }
}
