<?php
include_once "Conn.php";
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

class DemoConn extends Conn {
    public function __construct() {
        parent::__construct($_ENV['db_host'],$_ENV['db_user'],$_ENV['db_password'],$_ENV['db_name']);
    }
}