<?php
$config = include('../app/config/config.php');

use app\models\DbManager;
use app\router\Router;

mb_internal_encoding("UTF-8");

require("../vendor/autoload.php");
//Funkce pro autoload třídy, php ji používá automaticky díky "zaregistrování" níže
/**
 * @param $class
 */
function autoloadFunction($class)
{
    require ("../". preg_replace("/[\\ ]+/", "/", $class). ".php");
}

//registrace funkce pro její použití jako php autoload funkce
spl_autoload_register("autoloadFunction");

//připojení k db
DbManager::connect($config->Db->host, $config->Db->username, $config->Db->pass, $config->Db->database);

//vytvoření instance směrovače a jeho zpracování url a následné vypsání základního pohledu
$router = new Router();
$router->process(array($_SERVER['REQUEST_URI']));