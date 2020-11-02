<?php

namespace app\models;

use app\router\Router;
use PDO;
use PDOException;
use app\classes;

/**
 * Class DbManager
 * @package app\models
 */
class DbManager
{
    /**
     * @var PDO $connection
     */
    public static PDO $connection;

    /**
     * @var array $settings
     */
    private static array $settings = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @param $host
     * @param $user
     * @param $password
     * @param $database
     */
    public static function connect($host, $user, $password, $database)
    {
        if (!isset(self::$connection)) {
            self::$connection = @new PDO(
                "mysql:host=$host;dbname=$database",
                $user,
                $password,
                self::$settings
            );
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|PDOException
     */
    public static function requestSingle(string $sql, $params = array())
    {
        try {
            $result = self::$connection->prepare($sql);
            $result->execute($params);
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $excepiton) {
            Router::reroute("error/500");
        }
    }

    /**
     * @param string $sql
     * @param string $class
     * @param array $params
     * @return mixed
     */
    public static function requestSingleClass(string $sql, string $class, $params = array())
    {
        $class = "\\app\\classes\\".$class."Class";
        try {
            $result = self::$connection->prepare($sql);
            $result->execute($params);
            $result->setFetchMode(PDO::FETCH_CLASS, $class);
            return $result->fetch();
        } catch (PDOException $excepiton) {
            Router::reroute("error/500");
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|PDOException
     */
    public static function requestSingleWOAssoc(string $sql, $params = array())
    {
        try {
            $result = self::$connection->prepare($sql);
            $result->execute($params);
            return $result->fetch();
        } catch (PDOException $excepiton) {
            Router::reroute("error/500");
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|PDOException
     */
    public static function requestMultiple(string $sql, $params = array())
    {
        try {
            $result = self::$connection->prepare($sql);
            $result->execute($params);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $excepiton) {
            Router::reroute("error/500");
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return mixed|PDOException
     */
    public static function requestUnit(string $sql, $params = array())
    {
        try {
            $result = self::requestSingleWOAssoc($sql, $params);
            return ($result == null ? null : $result[0]);
        } catch (PDOException $excepiton) {
            Router::reroute("error/500");
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return boolean|PDOException
     */
    public static function requestInsert(string $sql, $params = array())
    {
        try {
            $result = self::$connection->prepare($sql);
            return $result->execute($params);
        } catch (PDOException $excepiton) {
            Router::reroute("error/500");
        }

    }

    /**
     * @param string $sql
     * @param array $params
     * @return int|PDOException
     */
    public static function requestAffect(string $sql, $params = array())
    {
        try {
            $result = self::$connection->prepare($sql);
            $result->execute($params);
            return $result->rowCount();
        } catch (PDOException $excepiton) {
            Router::reroute("error/500");
        }
    }
}
