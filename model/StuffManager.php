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
    public function getStuffInfoWhereMaxRequiredLvl($maxRequiredLvl) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('SELECT * FROM minirpg_stuff WHERE required_lvl <= ?');
            $q->execute([$maxRequiredLvl]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $allStuff = [];

        while ($stuff = $q->fetch()) {
            $stuffInfo = [
                'id' => $stuff['id'],
                'rarity' => $stuff['rarity'],
            ];

            $allStuff[] = $stuffInfo;
        }

        return $allStuff;
    }

    /**
     * @param $userId
     * @param $stuffId
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
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
