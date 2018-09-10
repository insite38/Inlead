<?php

class MediaLibrary
{
    private $tableName = 'media_library';
    public $dataRelations = array('relations' => false);

    protected static $_instance;
    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    /*
     *      параметры связной таблицы
     *      добавляем в construct класса
     *
       $this->media = MediaLibrary::getInstance();
       $this->media->dataRelations = array(
        'relations' => true,        // если надо записать в третью таблицу (many to many)
        'table' => 'home_image',    // имя таблицы
        'id' =>                     // id связной записи устанавливаем для каждого вызова
        'columnName' => 'home_id'); // имя связной колонки
     */


    /*
     *      добавляем изображение и ложим его в папку
     */
    public function putImage($tmp_name, $path, $width, $height)
    {
        $image = image::getInstance();
        $newName = $image->genFileName();
        $newImage = $image->resizeEx($tmp_name, $newName, array($path, $width, $height));

        DB::query("INSERT INTO " . $this->tableName . " (`image`, `path`) 
                            VALUES ('" . $newImage . "', '" . $path . "')");
        $imageId = DB::getLastID();

        //  если true записываем данные в связную таблицу
        if ($this->dataRelations['relations']){

            DB::query("INSERT INTO " . $this->dataRelations['table'] . " 
                                (`" . $this->dataRelations['columnName'] . "`, `image_id`)
                                VALUES ('" . $this->dataRelations['id'] . "', '" . $imageId . "')");
        }
        return array( 'id' => $imageId, 'name' => $newImage);
    }

    /*
     *      Удаляем изображение
     */
    public function deleteImage($id)
    {
        if ($this->dataRelations['relations']){

            $image = $this->getRelationsImageDB();

            DB::query("DELETE FROM " . $this->tableName . " WHERE `id` = '" . $image['id'] . "' ");
            DB::query("DELETE FROM " . $this->dataRelations['table'] . " WHERE `image_id` = '" . $image['id'] . "' ");
            unlink($image['path'] . $image['image']);
        } else {

            $image = $this->getImageDB($id);

            DB::query("DELETE FROM " . $this->tableName . " WHERE `id` = '" . $id . "' ");
            unlink($image['path'] . $image['image']);
        }
    }

    /*
     *      Возвращаем данные о изображении
     */
    public function getImage($id)
    {
        if ($this->dataRelations['relations']){

            $image = $this->getRelationsImageDB();
        } else {

            $image = $this->getImageDB($id);
        }

        return array('image' => $image['image'], 'id' => $image['id']);
    }

    /*
     *      возвращаем данные о изображении
     *      если есть зависимая таблица (Many to many)
     */
    private function getRelationsImageDB()
    {
        return DB::queryColumn("SELECT media.id as id, media.image as image, media.path as path 
                                            FROM " . $this->tableName . " as media
                                            LEFT JOIN " . $this->dataRelations['table'] . " as relation
                                            ON relation.image_id = media.id 
                                            WHERE relation." . $this->dataRelations['columnName'] . " = '" . $this->dataRelations['id'] . "' ");
    }

    /*
     *      возвращаем данные о изображении
     *      с таблицы media_library
     */
    private function getImageDB($id)
    {
        return DB::queryColumn("SELECT `image`, `path` FROM " . $this->tableName . " WHERE `id` = '" . $id . "' ");
    }
}