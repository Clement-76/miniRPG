<?php

namespace ClementPatigny\Model;

class Manager {
    /**
     * @return \PDO
     * @throws \Exception if the connection to the db failed
     */
    protected function connectDb() {
        try {
            $db = new \PDO('mysql:host=localhost;dbname=minirpg;charset=utf8', 'root', '');
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }

        return $db;
    }
}

