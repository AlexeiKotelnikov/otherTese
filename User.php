<?php

declare(strict_types=1);

namespace System;

use DateTime;
use Exception;
use PDO;
use stdClass;

/**
 * 1. * Author: Алексей Котельников
 * 2. *
 * 3. * Date of realization: 27.07.2022 17:00
 * 4. *
 * 5. * Date of change:  xx.xx.2022 19:40
 * 6. *
 * 7. * Utility for working with the database*/
class User
{
    /**
     * Class: User
     * The class constructor either creates a person in the database with the given information,
     * or takes information from the database by id (data validation is provided);
     * createUser() - Saving the fields of an instance of a class in the database;
     * removeUser() - Removing a person from the database according to the object's id;
     * selectUser() - Selecting a person from the database according to the passed id;
     * conversionDate() - This is a conversion of date of birth to age (full years);
     * conversionGender() - This is the conversion of gender from binary to textual (male, female);
     * formatPerson() - Formatting a person with age and/or gender conversion
     * depending on the parameters (returns a new instance of StdClass with all the fields of the original class)
     * validator() - здесь быть не должно, но пока надо реализовать иные вещи :)
     */

    public int $id;
    public string $name;
    public string $lastName;
    public string $birthday;
    public int|string $gender;
    public string $city;
    protected PDO $conn;
    private array $templates = ['name', 'last_name', 'birthday', 'gender', 'city'];

    /**
     * @param $db
     * @param array $params
     * When creating the class, specify a certain number of parameters.
     * If the 'id_user' key is present in the array, and it is a number, we call the selectUser() method,
     * which searches the database for a user with that id.
     * If the first condition is not met,
     * then check the number of keys and their correspondence with the keys in the $templates field
     * If all goes well, then the validator() method is called, which checks the validity of the parameters passed
     * If there are no errors, then call the createUser() method
     * If there are any errors in one of these steps, an error is thrown
     */
    public function __construct($db, array $params = [])
    {
        $this->conn = $db;

        if (array_key_exists('id_user', $params) && is_integer($params['id_user'])) {
            $this->selectUser($params['id_user']);
        } elseif (count(array_intersect_key(array_flip($this->templates), $params)) === count($this->templates)) {
            $errors = $this->validator($params);
            if (empty($errors)) {
                $this->createUser($params);
            } else {
                var_dump($errors);
            }
        } else {
            echo 'the number of parameters when creating a user should not exceed 5 and correspond to the template';
        }
    }

    /**
     * @param array $fields
     * @return bool
     * Make a sql-query, assign the passed values to the object properties,
     * and bind the parameters via the bindParam method
     * Trying to run a query
     */
    public function createUser(array $fields): bool
    {
        $query = "INSERT INTO
                    users
                SET
                    name=:name, last_name=:last_name, dt_birth=:dt_birth, gender=:gender, city=:city";

        $stmt = $this->conn->prepare($query);

        $this->name = $fields['name'];
        $this->lastName = $fields['last_name'];
        $this->birthday = $fields['birthday'];
        $this->gender = $fields['gender'];
        $this->city = $fields['city'];

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":last_name", $this->lastName);
        $stmt->bindParam(":dt_birth", $this->birthday);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":city", $this->city);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     * Binding, making a request, executing the request
     */
    public function removeUser(): bool
    {
        $sql = "DELETE from users where id_user = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return false;//whether it's necessary.
    }

    /**
     * @param int $id
     * @return array|false
     * Checking the received id, binding, making a request, executing the request
     * When we get an array, we assign the values obtained from the database to the object properties
     * If we get an empty value - get an error
     */
    public function selectUser(int $id): array|false
    {
        $sql = "SELECT * from users where id_user = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id_user'];
            $this->name = $row['name'];
            $this->lastName = $row['last_name'];
            $this->birthday = $row['dt_birth'];
            $this->gender = $row['gender'];
            $this->city = $row['city'];
            return $row;
        } else {
            echo 'there is no such user!';
            return false; // Check for non-existent users
        }
    }

    /**
     * @param User $user
     * @return string
     * @throws Exception
     *
     * format the date to 'd-m-Y H:i:s'
     * calculate the difference between $datetime = 'now' and $user->birthday
     * format it to the number of years and assign it to $user->birthday
     */
    static function conversionDate(User $user): string
    {
        $formattedDate = new DateTime($user->birthday);
        $formattedDate->format('d-m-Y H:i:s');
        $diff = (new DateTime())->diff($formattedDate);
        return $user->birthday = sprintf("%d", $diff->y);
    }

    /**
     * @param User $user
     * @return string
     */
    static function conversionGender(User $user): string
    {
        if ($user->gender == 1) {
            return $user->gender = 'муж';
        } else {
            return $user->gender = 'жен';
        }
    }

    /**
     * @param User $user
     * @return stdClass
     * @throws Exception
     * Convert the date and sex of a person, create an empty stdClass class and fill it with User's fields
     */
    public function formatPerson(User $user): stdClass
    {
        $user::conversionDate($user);
        $user::conversionGender($user);
        $a = new stdClass();
        foreach ($user as $item => $value) {
            $a->$item = $value;
        }
        return $a;
    }

    /**
     * @param array $fields
     * @return array|null
     */
    protected function validator(array $fields): array|null
    {
        $errors = [];
        if (isset($fields['id_user'])) {
            $fields['id_user'] = htmlspecialchars(strip_tags($fields['id_user']));
            if (!preg_match('/^[1-9]+\d*$/', $fields['id_user'])) {
                $errors['id_user'] = 'ID must contain only positive numbers!';
            }
        }

        $fields['name'] = htmlspecialchars(strip_tags($fields['name']));
        if (!preg_match('/^[a-zA-Z]+$/', $fields['name'])) {
            $errors['name'] = 'Name must contain only letters!';
        }
        $fields['last_name'] = htmlspecialchars(strip_tags($fields['last_name']));
        if (!preg_match('/^[a-zA-Z]+$/', $fields['last_name'])) {
            $errors['last_name'] = 'Last Name must contain only letters!';
        }
        $fields['birthday'] = htmlspecialchars(strip_tags($fields['birthday']));
        if (preg_match("/(\d{2})-(\d{2})-(\d{4})/", $fields['birthday'], $matches)) {
            if (!checkdate((int)$matches[2], (int)$matches[1], (int)$matches[3])) {
                $errors['birthday'] = "BIRTHDAY - Please enter a valid date in the format - dd-mm-yyyy";
            }
        } else {
            $errors['birthday'] = "BIRTHDAY - Only this birthday format - dd-mm-yyyy - is accepted.";
        }
        $fields['city'] = htmlspecialchars(strip_tags($fields['city']));
        if (!preg_match('/^[a-zA-Z]+$/', $fields['city'])) {
            $errors['city'] = 'City must contain only letters!';
        }
        $fields['gender'] = htmlspecialchars(strip_tags((string)$fields['gender']));
        if (!preg_match('/^[0-1]*$/', $fields['gender'])) {
            $errors['gender'] = 'select either 0 or 1';
        }

        return (!empty($errors)) ? $errors : null;
    }
}