<?php

namespace ClementPatigny\Controller;

use ClementPatigny\Model\AdventureManager;

class AdventuresController extends AppController {

    /**
     * echo Adventure objects in JSON
     * @throws \Exception
     */
    public function getJSONAdventures() {
        if (isset($_SESSION['user'])) {
            try {
                $adventuresManager = new AdventureManager();
                $adventures = $adventuresManager->getAdventures();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            echo json_encode([
                'status' => 'success',
                'adventures' => $adventures
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'adventures' => 'You\'re not connected !'
            ]);
        }
    }
}
