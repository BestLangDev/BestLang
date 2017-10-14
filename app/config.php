<?php

return [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=bestlang_test',
        'user' => 'bestlang',
        'pass' => 'Bestlang'
    ],

    'cache' => [
        'provider' => '\bestlang\ext\cache\WinCache2'
    ]
];