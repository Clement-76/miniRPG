<?php

namespace ClementPatigny\Model;

class AdventureManager extends Manager {

    /**
     * @return Adventure[] array of Adventure objects
     * @throws \Exception
     */
    public function getAdventures() {
        $db = $this->getDb();

        try {
            $q = $db->query(
                'SELECT *
                FROM minirpg_adventures AS adventures
                ORDER BY adventures.duration'
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $adventures = [];

        while ($adventure = $q->fetch()) {
            $adventureFeatures = [
                'id' => $adventure['id'],
                'name' => $adventure['name'],
                'duration' => $adventure['duration'],
                'requiredLvl' => $adventure['required_lvl'],
                'dollars' => $adventure['dollars'],
                'xp' => $adventure['xp']
            ];

            $adventures[] = new Adventure($adventureFeatures);
        }

        return $adventures;
    }

    /**
     * returns 0 if the adventure doesn't exist
     * and returns an Adventure object if she exists
     * @param $adventureId
     * @return Adventure|int
     * @throws \Exception
     */
    public function getAdventure($adventureId) {
        $db = $this->getDb();

        try {
            $q = $db->prepare('SELECT * FROM minirpg_adventures WHERE id = ?');
            $q->execute([$adventureId]);
            $count = $q->rowCount();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        if ($count > 0) {
            $adventure = $q->fetch();

            $adventureFeatures = [
                'id' => $adventure['id'],
                'name' => $adventure['name'],
                'duration' => $adventure['duration'],
                'requiredLvl' => $adventure['required_lvl'],
                'dollars' => $adventure['dollars'],
                'xp' => $adventure['xp']
            ];

            $adventure = new Adventure($adventureFeatures);

            return $adventure;
        } else {
            return false;
        }
    }
}
