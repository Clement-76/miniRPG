<?php

namespace ClementPatigny\Model;

class StuffManager extends Manager {
    /**
     * @param $userId
     * @return Stuff[] collection of stuff objects
     * @throws \Exception
     */
    public function getPossessionsStuff($userId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('SELECT * FROM minirpg_possessions_stuff WHERE user_id = ?');
            $q->execute([$userId]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $allStuff = [];

        while ($userStuff = $q->fetch()) {
            $stuff = $this->getStuff($userStuff['stuff_id']);

            $stuffFeatures = [
                'id' => $userStuff['id'],
                'equipped' => $userStuff['equipped'],
                'name' => $stuff['name'],
                'type' => $stuff['type'],
                'requiredLvl' => $stuff['required_lvl'],
                'stat' => $stuff['stat'],
                'rarity' => $stuff['rarity'],
            ];

            $allStuff[] = new Stuff($stuffFeatures);
        }

        return $allStuff;
    }

    /**
     * @param $stuffId
     * @return object
     * @throws \Exception
     */
    public function getStuff($stuffId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('SELECT * FROM minirpg_stuff WHERE id = ?');
            $q->execute([$stuffId]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $stuffFeatures = $q->fetch();

        return $stuffFeatures;
    }

    public function setEquippedValue($stuffId, $value) {
        $db = $this->getDb();

        try {
            $q = $db->prepare(
                'UPDATE minirpg_possessions_stuff AS stuff
                 SET equipped = :value
                 WHERE stuff.id = :stuffId'
            );

            $success = $q->execute([
                ':value' => $value,
                ':stuffId' => $stuffId
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $success;
    }

    /**
     * @param $maxRequiredLvl
     * @return mixed
     * @throws \Exception
     */
    public function getStuffIdsWhereMaxRequiredLvl($maxRequiredLvl) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('SELECT stuff.id FROM minirpg_stuff AS stuff WHERE required_lvl <= ?');
            $q->execute([$maxRequiredLvl]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $stuffIds = [];

        while ($stuff = $q->fetch()) {
            $stuffIds[] = $stuff['id'];
        }

        return $stuffIds;
    }

    public function getPossessionStuff($possessionStuffId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('SELECT * FROM minirpg_possessions_stuff WHERE minirpg_possessions_stuff.id = ?');
            $q->execute([$possessionStuffId]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $userStuff = $q->fetch();
        $stuff = $this->getStuff($userStuff['stuff_id']);

        $stuffFeatures = [
            'id' => $userStuff['id'],
            'equipped' => $userStuff['equipped'],
            'name' => $stuff['name'],
            'type' => $stuff['type'],
            'requiredLvl' => $stuff['required_lvl'],
            'stat' => $stuff['stat'],
            'rarity' => $stuff['rarity'],
        ];

        $stuff = new Stuff($stuffFeatures);

        return $stuff;
    }

    /**
     * add a new stuff to the user and returns the stuff added
     * @param $userId
     * @param $stuffId
     * @return Stuff
     * @throws \Exception
     */
    public function createPossessionStuff($userId, $stuffId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('INSERT INTO minirpg_possessions_stuff(user_id, stuff_id) VALUES(:userId, :stuffId)');
            $q->execute([
                ':userId' => $userId,
                ':stuffId' => $stuffId
            ]);

            $stuff = $this->getPossessionStuff($db->lastInsertId());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $stuff;
    }

    public function getAllStuff() {
        try {
            $db = $this->getDb();
            $q = $db->prepare('SELECT * FROM minirpg_stuff');
            $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $allStuff = [];

        while ($stuff = $q->fetch()) {
            $stuffFeatures = [
                'id' => $stuff['id'],
                'name' => $stuff['name'],
                'type' => $stuff['type'],
                'stat' => $stuff['stat'],
                'requiredLvl' => $stuff['required_lvl'],
                'rarity' => $stuff['rarity'],
            ];

            $allStuff[] = new Stuff($stuffFeatures);
        }

        return $allStuff;
    }

    /**
     * delete the stuff with the id and delete the possessions stuff with this id in stuff_id
     * @param $stuffId
     * @throws \Exception
     */
    public function deleteStuffAndPossesionsStuff($stuffId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('
                DELETE FROM minirpg_possessions_stuff WHERE stuff_id = :stuffId;
                DELETE FROM minirpg_stuff WHERE id = :stuffId;
            ');

            $q->bindValue(':stuffId', $stuffId, \PDO::PARAM_INT);
            $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
