<?php

return [

    'max_servers' => env('APP_MAX_SERVERS', 1000),
    'max_active_domains' => env('APP_ACTIVE_DOMAINS', 1000),
    'max_scan_records' => env('APP_MAX_SCAN_RECORDS', 100),
    'max_scan_range' => env('APP_MAX_SCAN_RANGE', 256),

    'wrk_weekly_report_conn' => env('WORK_WEEKLY_REPORT_CONN', 'ph4database'),
    'wrk_weekly_report_queue' => env('WORK_WEEKLY_REPORT_QUEUE', 'default'),
    'wrk_weekly_emails_conn' => env('WORK_WEEKLY_EMAILS_CONN', 'ph4database'),
    'wrk_weekly_emails_queue' => env('WORK_WEEKLY_EMAILS_QUEUE', 'emails'),
    'wrk_key_check_emails_conn' => env('WORK_KEY_CHECK_EMAILS_CONN', 'ph4database'),
    'wrk_key_check_emails_queue' => env('WORK_KEY_CHECK_EMAILS_QUEUE', 'default'),

    'enabled_ip_scanning' => env('APP_ENABLED_IP_SCANNING', false),
    'enabled_api_self_register' => env('APP_ENABLED_API_SELF_REGISTER', false),
    'enabled_user_auto_register' => env('APP_ENABLED_USER_AUTO_REGISTER', false),

    'ssh_key_pool_size' => env('SSH_KEY_POOL_SIZE', 100),
    'ssh_key_free_max_age_days' => env('SSH_KEY_FREE_MAX_AGE_DAYS', 14),
    'ssh_key_sizes' => json_decode(env('SSH_KEY_SIZES', '[2048,3072]')),

    'wrk_ssh_pool_conn' => env('WORK_SSH_POOL_CONN', 'database'),
    'wrk_ssh_pool_queue' => env('WORK_SSH_POOL_QUEUE', 'sshpool'),
    'wrk_ssh_pool_gen_conn' => env('WORK_SSH_POOL_GEN_CONN', 'database'),
    'wrk_ssh_pool_gen_queue' => env('WORK_SSH_POOL_GEN_QUEUE', 'sshpool'),
];

