<?php

declare(strict_types=1);

namespace System;

use DateTime;
use Exception;
use PDO;
use stdClass;

/**
 * 1. * Автор: Алексей Котельников
 * 2. *
 * 3. * Дата реализации: 27.07.2022 17:00
 * 4. *
 * 5. * Дата изменения:  xx.xx.2022 19:40
 * 6. *
 * 7. * Утилита для работы с базой данных*/
class User
{
    /**
     * класс: User
     * 3. Подробное описание класса, что он делает, как он делает, что, кому куда передает.
     * Конструктор класса либо создает человека в БД с заданной информацией,
     * либо берет информацию из БД по id (предусмотрена валидация данных);
     * createUser() - Сохранение полей экземпляра класса в БД;
     * removeUser() - Удаление человека из БД в соответствии с id объекта;
     * selectUser() - Выбор человека из БД в соответствии с переданным id;
     * conversionDate() - static преобразование даты рождения в возраст (полных лет);
     * conversionGender() - static преобразование пола из двоичной системы в текстовую (муж, жен);
     * formatPerson() - Форматирование человека с преобразованием возраста и (или) пола
     * в зависимости от параметров (возвращает новый экземпляр StdClass
     * со всеми полями изначального класса)
     * validator() - здесь быть не должно, но пока надо реализовать иные вещи :)
     */

    public int $id;
    public string $name;
    public string $lastName;
    public string $birthday;
    public int|string $gender;
    public string $city;
    protected $conn;
    private array $templates = ['name', 'last_name', 'birthday', 'gender', 'city'];

    /**
     * @param $db
     * @param array $params
     * при создании класса указываем определенное количество параметров.
     * Если в массиве будет присутсвовать ключ 'id_user' и он будет числом, то мы вызываем метод selectUser(),
     * который ищет в БД юзера с таким id.
     * Если не выполняется первое условие, то проверяем количество ключей и их соответсвие с ключами в поле $templates
     * Если все хорошо - идет вызов метода validator(), который проверяет на валидность переданные параметры
     * Если нет ошибок, то вызываем метод createUser()
     * Если же есть какие-то ошибки на одном из этих шагов, то идет выброс ошибки
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
     * Составляем sql-запрос, присваиваем переданные значения свойствам объекта, биндим параметры через метод bindParam
     * Пробуем выполнить запрос
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
     * бинд, составление запроса, выполнение запроса
     */
    public function removeUser(): bool
    {
        $sql = "DELETE from users where id_user = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return false;//надо ли это
    }

    /**
     * @param int $id
     * @return array|false
     * Проверка полученного id, бинд, составление запроса, выполнение запроса
     * при получении массива, присваиваем полученные из Бд значения свойствам объекта
     * при получении пустого значения - выдаем ошибку
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
            return false; // проверка на несуществующих пользователей
        }
    }

    /**
     * @param User $user
     * @return string
     * @throws Exception
     *
     * форматируем дату в 'd-m-Y H:i:s'
     * вычисляем разницу между $datetime = 'now' и $user->birthday
     * форматируем в количество лет и присваиваем $user->birthday
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
     * @param int|null $gender
     * @param string|null $date
     * @return stdClass
     * @throws Exception
     */
    public function formatPerson(User $user, int $gender = null, string $date = null): stdClass
    {
        $user::conversionDate($user);
        $user::conversionGender($user);
        $a = (array($user));//нахера делать это
        //а потом это
        //var_dump($user);//если после двух вышевызванных методов, мы получим измененный и заполненный объект

        return (object)$a;
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