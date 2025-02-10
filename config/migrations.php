<?php

return [
    'name' => 'Cartorio Database Migrations',
    'migrations_namespace' => 'Application\Migrations',
    'table_name' => 'migrations',
    'column_name' => 'version',
    'column_length' => 14,
    'executed_at_column_name' => 'executed_at',
    'migrations_directory' => 'module/Application/src/Migrations',
    'all_or_nothing' => true,
    'check_database_platform' => true,
]; 