<?php

namespace ClementPatigny\Model;

abstract class Manager {

    protected $db = null;

    /**
     * @return \PDO
     * @throws \Exception if the connection to the db failed
     */
    protected function getDb() {
        if ($this->db == null) {
            try {
                $this->db = new \PDO('mysql:host=localhost;dbname=minirpg;charset=utf8', 'root', '');
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }

        return $this->db;
    }
}

