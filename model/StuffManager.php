<?php

namespace ClementPatigny\Model;

class StuffManager extends Manager {
    /**
     * @param $userId
     * @return Stuff[] collection of stuff objects
     * @throws \Exception
     */
    public function getPossessionsStuff($userId) {
        $db = $this->connectDb();
        $q = $db->prepare('SELECT * FROM possessions_stuff WHERE user_id = ?');
        $q->execute([$userId]);

//        $userStuff = [];

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

            $userStuff[] = new Stuff($stuffFeatures);
        }

        return $userStuff;
    }

    /**
     * @param $stuffId
     * @return object
     * @throws \Exception
     */
    public function getStuff($stuffId) {
        $db = $this->connectDb();
        $q = $db->prepare('SELECT * FROM stuff WHERE id = ?');
        $q->execute([$stuffId]);
        
        $stuffFeatures = $q->fetch();

        return $stuffFeatures;
    }
}
