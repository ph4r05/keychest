<?php

return [

    'max_servers' => env('APP_MAX_SERVERS', 1000),
    'max_active_domains' => env('APP_ACTIVE_DOMAINS', 1000),
    'max_scan_records' => env('APP_MAX_SCAN_RECORDS', 100),
    'max_scan_range' => env('APP_MAX_SCAN_RANGE', 256),

    'wrk_weekly_report_conn' => env('WORK_WEEKLY_REPORT_CONN', 'database'),
    'wrk_weekly_report_queue' => env('WORK_WEEKLY_REPORT_QUEUE', 'default'),
    'wrk_weekly_emails_conn' => env('WORK_WEEKLY_EMAILS_CONN', 'database'),
    'wrk_weekly_emails_queue' => env('WORK_WEEKLY_EMAILS_QUEUE', 'emails'),

    'enabled_ip_scanning' => env('APP_ENABLED_IP_SCANNING', false),
];

