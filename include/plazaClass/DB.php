<?php
/**
 * PDO Singleton Class v.1.0
 *
 * @author Ademílson F. Tonato
 * @link https://twitter.com/ftonato
 *
 */
class DB
{
    protected static $instance;
    protected function __construct() {}

    public static function getInstance()
    {
        if(empty(self::$instance)) {
            $db_info = array(
                "db_host" => "localhost",
                "db_port" => "3306",
                "db_user" => "root",  //  alicedomin_us";
                "db_pass" => "",  //  Lk7RtkGd";
                "db_name" => "alicedom_db",  //  alicedomin_db";
                "db_charset" => "UTF-8");
            try {
                self::$instance = new PDO("mysql:host=".$db_info['db_host'].';port='.$db_info['db_port'].';dbname='.$db_info['db_name'], $db_info['db_user'], $db_info['db_pass']);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                self::$instance->query('SET NAMES utf8');
                self::$instance->query('SET CHARACTER SET utf8');
            } catch(PDOException $error) {
                echo $error->getMessage();
            }
        }
        return self::$instance;
    }

    public static function setCharsetEncoding()
    {
        if (self::$instance == null) {
            self::connect();
        }
        self::$instance->exec(
            "SET NAMES 'utf8';
			SET character_set_connection=utf8;
			SET character_set_client=utf8;
			SET character_set_results=utf8");
    }

    public static function getLastID()
    {
        $db = DB::getInstance();
        return $db->lastInsertId();
    }

    public static function query($query)
    {
        try {
            $db = DB::getInstance();
            DB::setCharsetEncoding();
            $sqlExample = $query;
            $stm = $db->prepare($sqlExample);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {

            $result = $e->getMessage();
        }

        return $result;
    }

    public static function queryColumn($query)
    {
        try {
            $db = DB::getInstance();
            DB::setCharsetEncoding();
            $sqlExample = $query;
            $stm = $db->prepare($sqlExample);
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {

            $result = $e->getMessage();
        }

        return $result;
    }

    public static function read($table, $columns, $where = '')
    {
        try {
            $db = DB::getInstance();
            DB::setCharsetEncoding();
            $sqlExample = "SELECT " . $columns . " FROM " . $table . " " . $where . "";
            $stm = $db->prepare($sqlExample);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {

            $result = $e->getMessage();
        }
        return $result;
    }

    public static function readColumn($table, $columns, $where = '')
    {
        try {
            $db = DB::getInstance();
            DB::setCharsetEncoding();
            $sqlExample = "SELECT " . $columns . " FROM " . $table . " " . $where . "";
            $stm = $db->prepare($sqlExample);
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {

            $result = $e->getMessage();
        }
        return $result;
    }
}
?>