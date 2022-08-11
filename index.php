<?php

declare(strict_types=1);

include_once "User.php";
include_once "Users.php";
include_once "Database\Database.php";

use System\User;
use System\Users;
use System\Database;

const DB_HOST = 'localhost';
const DB_NAME = 'test';
const DB_USER = 'root';
const DB_PASS = '';

$database = new Database();
$db = $database->getConnection();

$user = new User($db, ['id_user'=> 108]);
var_dump($user);

try {
    $a = $user->formatPerson($user);
    var_dump($a);
} catch (Exception $e) {
    var_dump(DateTime::getLastErrors());
}

