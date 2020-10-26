<?php

namespace app\models;

use app\exceptions\SignException;
use app\classes\User;

class SignManager
{
    /**
     * @param $login
     * @param $password
     * @return void
     * @throws SignException
     */
    static function SignIn($login, $password)
    {
        (session_status() === 1 ? session_start() : null);
        if (self::userExists($login)) {
            if (self::userActivated($login)) {
                $DBPass = DbManager::requestUnit("SELECT password FROM user WHERE username = ? OR email = ?", [$login, $login]);
                if (password_verify($password, $DBPass)) {
                    $_SESSION["user"] = UserManager::getUserFromDatabase($login);
                } else {
                    throw new SignException("Wrong password");
                }
            } else {
                throw new SignException("Account not activated");
            }
        } else {
            throw new SignException("Wrong login");
        }
    }

    static function SignUp(User $user)
    {
        (session_status() === 1 ? session_start() : null);
        if (!self::userExists($user->email)) :
            DbManager::$connection->beginTransaction();
            $user->password = password_hash($user->password, PASSWORD_DEFAULT);

            $userInsert = DbManager::requestInsert('
            INSERT INTO user (email,username,password,area_code,phone,role_id,activated, registered,last_active,first_name,last_name)
            VALUES(?,?,?,?,?,6,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,?,?)
            ', [$user->email, $user->username, $user->password, $user->area_code, $user->phone, $user->first_name, $user->last_name]);
            $userId = DbManager::requestUnit("SELECT id FROM user WHERE username = ?", [$user->username]);
            $user->setId($userId);
            $user->setUserIdToAddress();
            $invoiceAddress = $user->getInvoice_address();

            $invoiceAddressInsert = DbManager::requestInsert('
            INSERT INTO invoice_address (first_name,last_name,firm_name,address1,address2,city,country,zipcode,DIC,IC,user_id)
            VALUES(?,?,?,?,?,?,?,?,?,?,?)
            ', [$invoiceAddress->first_name, $invoiceAddress->last_name, $invoiceAddress->firm_name, $invoiceAddress->address1, $invoiceAddress->address2, $invoiceAddress->city, $invoiceAddress->country, $invoiceAddress->zipcode, $invoiceAddress->dic, $invoiceAddress->ic, $invoiceAddress->user_id]);;
            $user->invoice_address->id = (int)DbManager::$connection->lastInsertId();


            if ($userInsert != true || $invoiceAddressInsert != true) {
                DbManager::$connection->rollback();
                throw new SignException("Something went wrong in registration.");
            } else {
                DbManager::$connection->commit();
                $_SESSION["user"] = $user->getSessionInfo();
                return true;
            }
        else :
            throw new SignException("User already exists");
        endif;
    }

    static function SignOut(): void
    {
        (session_status() === 2 ? session_destroy() : null);
    }

    static function userExists($login)
    {
        return (self::checkUsername($login) || self::checkEmail($login));
    }

    static function userActivated($login)
    {
        return (DbManager::requestUnit("SELECT activated FROM user WHERE email = ? OR username = ?", [$login, $login]) === 1);
    }

    static function checkUsername($username)
    {
        return (DbManager::requestAffect("SELECT username FROM user WHERE username = ?", [$username]) === 1);
    }

    static function checkEmail($email)
    {
        return (DbManager::requestAffect("SELECT email FROM user WHERE email = ?", [$email]) === 1);
    }
}
