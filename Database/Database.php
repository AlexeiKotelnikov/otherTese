<?php

namespace System;

use PDO;
use PDOException;

class Database {

    // укажите свои собственные учетные данные для базы данных
    private $host = DB_HOST;
    private $db_name = DB_NAME ;
    private $username =  DB_USER;
    private $password = DB_PASS;
    public $conn;

    // получение соединения с базой данных
    public function getConnection(): ?PDO
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
        } catch(PDOException $exception) {
            echo "Ошибка соединения: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>