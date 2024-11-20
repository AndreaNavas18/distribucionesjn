<?php
require 'vendor/autoload.php';

use Medoo\Medoo;

$database = new Medoo([
    'type' => 'pgsql',
    'host' => '127.0.0.1',
    'database' => 'distribucionesjn',
    'username' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8'
]);

