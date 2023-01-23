<?php

namespace App\Model;

use Nette;
use Nette\Utils\Random;

class MainDbManager {

    use Nette\SmartObject;

    protected $database;
    protected $cache;

    public function __construct(Nette\Database\Explorer $database) {
        $this->database = $database;
    }

    public function addDatabase($name){
        $guid=Random::generate(30);
        $this->database->query('INSERT INTO `databases` (`guid`, `share`, `name`) VALUES (?, ?,?)', $guid,Random::generate(30),$name);
        return $guid;
    }

    public function getDatabase($guid){
        return $this->database->table('databases')->where('guid',$guid)->fetch();
    }

    public function getDatabaseByShare($share){
        return $this->database->table('databases')->where('share',$share)->fetch();
    }

    //save code
    public function saveCode($guid,$code){
        $this->database->query('UPDATE `databases` SET `code` = ? WHERE `databases`.`guid` = ?', $code,$guid);
    }

    //saveTitle
    public function saveName($guid,$name){
        $this->database->query('UPDATE `databases` SET `name` = ? WHERE `databases`.`guid` = ?', $name,$guid);
    }

}