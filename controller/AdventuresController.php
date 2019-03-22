<?php

namespace ClementPatigny\Controller;

use ClementPatigny\Model\AdventureManager;
use ClementPatigny\Model\StuffManager;
use ClementPatigny\Model\UserManager;

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

    public function finishedAdventure() {
        if (isset($_SESSION['user'])) {

            // if the user is in an adventure
            if ($_SESSION['user']->getCurrentAdventureId() != 0) {
                try {
                    $adventuresManager = new AdventureManager();
                    $adventure = $adventuresManager->getAdventure($_SESSION['user']->getCurrentAdventureId());
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                // if the user is in an adventure that is done
                if ($this->checkAdventureStatus($adventure) === 'end') {
                    // reset value
                    $_SESSION['user']->setAdventureBeginning(null);
                    $_SESSION['user']->setCurrentAdventureId(null);

                    // create the rewards
                    $randomPercentageXp = mt_rand(70, 100) / 100;
                    $xpGained = $randomPercentageXp * $adventure->getXp();

                    $randomPercentageDollars = mt_rand(70, 100) / 100;
                    $dollarsGained = $randomPercentageDollars * $adventure->getDollars();

                    $stuffManager = new StuffManager();
                    $stuffInfo = $stuffManager->getStuffInfoWhereMaxRequiredLvl($_SESSION['user']->getLvl());
                    $stuffIdGained = array_rand($stuffInfo)['id'];

                    $stuffManager->createPossessionStuff($_SESSION['user']->getId(), $stuffIdGained);

                    // remove the current adventure
                    $adventuresManager->resetAdventure($_SESSION['user']->getId());

                    // echo the rewards (xp, stuff, $)
                    echo json_encode([
                        'status' => 'success'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'You\'re not in an adventure or your adventure isn\'t over'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'You\'re not in an adventure'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected !'
            ]);
        }
    }

    public function checkAdventureStatus($adventure = null) {
        if (isset($_SESSION['user']) && $adventure != null) {

            if ($_SESSION['user']->getAdventureBeginning() == null) {
                $isInAdventure = false;
            } else {
                $adventureDuration = $adventure->getDuration();
                $adventureBeginning = $_SESSION['user']->getAdventureBeginning()->getTimestamp();
                $date = new \DateTime();
                $now = $date->getTimestamp();

                if ($adventureBeginning + $adventureDuration <= $now) {
                    // adventure done
                    $isInAdventure = 'end';
                } else {
                    $isInAdventure = true;
                }
            }

            return $isInAdventure;
        }
    }

    /**
     * start an adventure if the user isn't already in an adventure
     * @throws \Exception
     */
    public function startAdventure() {
        if (isset($_SESSION['user'])) {
            try {
                $adventuresManager = new AdventureManager();
                $adventure = $adventuresManager->getAdventure($_POST['adventureId']);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            // if the adventure exists
            if ($adventure != false) {
                // if the user isn't already in an adventure
                if ($this->checkAdventureStatus($adventure) == false) {
                    if ($_SESSION['user']->getLvl() >= $adventure->getRequiredLvl()) {
                        try {
                            $userManager = new UserManager();
                            $notErrors = $userManager->startAdventure($adventure->getId(), $_SESSION['user']->getId());
                        } catch (\Exception $e) {
                            throw new \Exception($e->getMessage());
                        }

                        if ($notErrors) {
                            $_SESSION['user']->setAdventureBeginning('NOW');
                            $_SESSION['user']->setCurrentAdventureId($_POST['adventureId']);

                            echo json_encode([
                                'status' => 'success',
                                'adventureBeginning' => $_SESSION['user']->getAdventureBeginning(),
                                'CurrentAdventureId' => $_SESSION['user']->getCurrentAdventureId()
                            ]);
                        } else {
                            echo json_encode([
                                'status' => 'error',
                                'message' => 'an unexpected error occurred'
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'You don\'t have the lvl to start this adventure'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'You\'re already in an adventure or you don\'t got your rewards'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'This adventure doesn\'t exist !'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected !'
            ]);
        }
    }
}
