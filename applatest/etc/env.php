<?php
return [
    'backend' => [
        'frontName' => 'ae75fbd538_admin'
    ],
    'crypt' => [
        'key' => 'RHxvvH6G7mfoQbdKy53CZMwzjEWHJ0Nl'
    ],
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => '/var/run/redis-multi-af312799.redis/redis.sock',
            'port' => '0',
            'database' => '2',
            'compression_library' => 'gzip'
        ]
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => '127.0.0.1',
                'dbname' => 'af312799_b185f3',
                'username' => 'af312799_b185f3',
                'password' => 'UploadWoosEgretWirier',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'developer',
    'cache_types' => [
        'config' => 1,
        'layout' => 0,
        'block_html' => 0,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'full_page' => 0,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'translate' => 1,
        'config_webservice' => 1,
        'compiled_config' => 1
    ],
    'install' => [
        'date' => 'Wed, 02 Aug 2023 21:44:37 -0400'
    ],
    'dev' => [
        'debug' => [
            'debug_logging' => 1
        ]
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => 'e86_',
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => '/var/run/redis-multi-af312799.redis/redis.sock',
                    'database' => '1',
                    'port' => '0'
                ]
            ],
            'page_cache' => [
                'id_prefix' => 'e86_',
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => '/var/run/redis-multi-af312799.redis/redis.sock',
                    'database' => '0',
                    'port' => '0'
                ]
            ]
        ],
        'allow_parallel_generation' => false
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => null
        ]
    ],
    'directories' => [
        'document_root_is_pub' => true
    ]
];
