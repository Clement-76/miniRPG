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
                'rewards' => $adventure['rewards'],
                'requiredLvl' => $adventure['required_lvl']
            ];

            $adventures[] = new Adventure($adventureFeatures);
        }

        return $adventures;
    }
}
