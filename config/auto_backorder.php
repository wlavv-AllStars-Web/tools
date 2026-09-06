<?php

return [
    /*
     * This module is deliberately audit-only for the first rollout. When the
     * process is approved, the state/history update belongs behind this flag.
     */
    'audit_only' => env('AUTO_BACKORDER_AUDIT_ONLY', true),

    'shipped_state' => (int) env('AUTO_BACKORDER_SHIPPED_STATE', 4),
    'backorder_state' => (int) env('AUTO_BACKORDER_BACKORDER_STATE', 15),
    'schedule_time' => env('AUTO_BACKORDER_SCHEDULE_TIME', '01:30'),
    // Bruno Fernandes (brunofernandes@all-stars-motorsport.com).
    'manual_run_user_ids' => [43],


    'ignored_reference_prefixes' => [
        'ccfee',
        'asmgoods',
        'shipping-',
        'parts-',
        'goodies-',
        'vat',
    ],

    'ignored_references' => [
        'ship-pick',
    ],
];
