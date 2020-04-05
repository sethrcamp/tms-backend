<?php

class Database {

    private static $db=null;

    public static function getInstance() {
        if(self::$db!=null){
            return self::$db;
        }
        date_default_timezone_set ( "America/Indianapolis" );
        $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME;
        $username = DB_USERNAME;
        $password = DB_PASSWORD;
        $db = new PDO($dsn, $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //ensures all values are returned as there type, rather than all as strings

        //set timezone to eastern
        //taken from https://stackoverflow.com/questions/34428563/set-timezone-in-php-and-mysql
        $now = new DateTime();
        $mins = $now->getOffset() / 60;
        $sgn = ($mins < 0 ? -1 : 1);
        $mins = abs($mins);
        $hrs = floor($mins / 60);
        $mins -= $hrs * 60;
        $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
        $db->exec("SET time_zone='$offset';");

        self::$db= $db;
        return self::$db;
    }

}