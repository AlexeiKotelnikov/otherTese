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

try {
    $a = $user->formatPerson($user);
} catch (Exception $e) {
    var_dump(DateTime::getLastErrors());
}

$users = new Users($db,30,70);
$users->selectUsers()();
$users->deleteUsers();

