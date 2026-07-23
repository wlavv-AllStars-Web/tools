<?php

$csv = static fn (string $value): array => array_values(array_filter(
    array_map('trim', explode(',', $value)),
    static fn (string $item): bool => $item !== ''
));

return [
    'unrestricted_user_ids' => array_map(
        'intval',
        $csv((string) env('ACCESS_UNRESTRICTED_USER_IDS', '43'))
    ),
    'allowed_ips' => $csv((string) env('ACCESS_ALLOWED_IPS', '127.0.0.1,::1')),
];
