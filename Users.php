<?php

declare(strict_types=1);

namespace System;

use PDO;

class Users
{
    /**
     * Class: Users
     * Constructor searches for id people in all fields of the database (supports expressions more, less, not equal);
     * selectUsers() - Getting an array of instances of the User class from the array with the id of the people obtained in the constructor;
     * deleteUsers() - Removing people from the database using instances of the User class according to the array obtained in the constructor.
     */

    public array $users = [];
    protected array $params = [
        'more' => null,
        'less' => null,
        'notEqual' => null
    ];
    protected PDO $conn;

    /**
     * @param $db
     * @param int|null $more
     * @param int|null $less
     * @param int|null $notEqual
     * Assign the passed parameters to the object properties, create an array masks and fill it with empty values,
     * for the case of empty parameters passed to the constructor.
     * If the parameter is present, then create the corresponding part of the query.
     * Remove elements containing '', null, false from the array masks,
     * insert separator ' AND ' between the remaining elements of the array.
     * Make a sql-query
     */
    public function __construct($db, int $more = null, int $less = null, int $notEqual = null)
    {
        $this->conn = $db;

        $this->params['more'] = $more;
        $this->params['less'] = $less;
        $this->params['notEqual'] = $notEqual;
        $masks = [
            'queryMore' => null,
            'queryLess' => null,
            'queryNotEqual' => null
        ];

        if ($more !== null) {
            $masks['queryMore'] = 'id_user >' . "{$this->params['more']}";
        }
        if ($less !== null) {
            $masks['queryLess'] = 'id_user <' . "{$this->params['less']}";
        }
        if ($notEqual !== null) {
            $masks['queryNotEqual'] = 'id_user <>' . "{$this->params['notEqual']}";
        }

        $a = implode(' AND ', array_diff($masks, array('', null, false)));

        $sql = "SELECT id_user FROM users WHERE $a";
        $stmt = $this->conn->prepare($sql);
        // $stmt->bindParam(":more", $a); how can i do this?
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->users = $row;
    }

    /**
     * @return array
     * Assign an array of object with user's id to the list variable,
     * create an empty array in case we get null on id request,
     * write all users in this array as objects of class User
     */
    public function selectUsers(): array
    {
        $list = $this->users;
        $arr = [];
        foreach ($list as $item) {
            $arr [] = new User($this->conn, $item);
        }
        return $arr;
    }

    /**
     * @return void
     */
    public function deleteUsers(): void
    {
        $list = $this->users;
        foreach ($list as $item) {
            (new User($this->conn, $item))->removeUser();
        }
    }

}
