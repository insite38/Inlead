<?php

abstract class BaseModel
{
    private $db;
    private $table;
    private $where = '';
    public $fillable = array();
    protected static $instance;

    public function __construct($db, $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function setCharsetEncoding()
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

    /** Возвращаем все из таблици
     *
     * @return string
     */
    public function getAll()
    {
        try {
            DB::setCharsetEncoding();
            $stm = $this->db->prepare("SELECT * FROM " . $this->table);
            $stm->execute();
            $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    /** Записываем в таблицу
     *
     * @return bool|string
     */
    public function insert()
    {
        try {
            DB::setCharsetEncoding();
            $stm = $this->db->prepare("INSERT INTO " . $this->table . " 
                                                (" . $this->getColumns() . ")
                                        VALUES (" . $this->getValues() . ") ");
            $stm->execute();
        } catch (Exception $e) {
            return $result = $e->getMessage();
        }
        return true;
    }

    public function update()
    {
        try {
            DB::setCharsetEncoding();
            $stm = $this->db->prepare("UPDATE " . $this->table . " 
                                         SET " . $this->getUpdateValue() . "
                                         WHERE " . $this->where . " ");
            $stm->execute();
        } catch (Exception $e) {
            return $result = $e->getMessage();
        }
        return true;
    }

    /** найти колонки в базе по значению
     *
     * @param $column
     * @return string
     */
    public function find($column)
    {
        try {
            DB::setCharsetEncoding();
            $sqlExample = "SELECT " . $column . " 
                           FROM " . $this->table . "
                           WHERE " . $this->where . " ";
            $stm = $this->db->prepare($sqlExample);
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return $result = $e->getMessage();
        }
        return $result;
    }

    /** получение данных по id
     *
     * @param $id
     * @return string
     */
    public function getById($id)
    {
        try {
            DB::setCharsetEncoding();
            $sqlExample = "SELECT * FROM " . $this->table . " WHERE `id` = '" . $id . "' ";
            $stm = $this->db->prepare($sqlExample);
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return $result = $e->getMessage();
        }
        return $result;
    }

    /** получение последнего id записаного в базу
     *
     * @return string
     */
    public static function getLastId()
    {
        $db = DB::getInstance();
        return $db->lastInsertId();
    }

    /** заполняем where в запросе
     *
     * @param $column
     * @param $value
     * @return $this
     */
    public function where($column, $value)
    {
        $this->where = " `" . $column . "` = '" . $value . "' ";
        return $this;
    }

    /** дописываем and в запрос
     *
     * @param $column
     * @param $value
     * @return $this
     */
    public function addAnd($column, $value)
    {
        $this->where .= " AND `" . $column . "` = '" . $value . "' ";
        return $this;
    }

    /** получаем данны для update
     *  из массива fillable
     *
     * @return string
     */
    private function getUpdateValue()
    {
        $items = '';

        foreach ($this->fillable as $name => $value){

            $items .= " `" . $name . "` = '" . $value . "', ";
        }

        return rtrim($items, ', ');
    }

    /** получаем название колонок в базе
     *  из массива $fillable
     *
     * @return string
     */
    private function getColumns()
    {
        $columns = '';

        foreach ($this->fillable as $name => $value){

            $columns .= "`" . $name . "`, ";
        }

        return rtrim($columns, ', ');
    }

    /** получаем значения соответствующие колонкам
     *
     * @return string
     */
    private function getValues()
    {
        $values = '';

        foreach ($this->fillable as $name => $value){

            $values .= "'" . $value . "', ";
        }

        return rtrim($values, ', ');
    }

}