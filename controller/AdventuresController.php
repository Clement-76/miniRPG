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
                    $userManager = new UserManager();
                    $adventuresManager = new AdventureManager();
                    $adventure = $adventuresManager->getAdventure($_SESSION['user']->getCurrentAdventureId());
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                // if the user is in an adventure that is done
                if ($this->checkAdventureStatus($adventure) === 'end') {
                    // reset values
                    $_SESSION['user']->setAdventureBeginning(null);
                    $_SESSION['user']->setCurrentAdventureId(null);

                    // create the rewards
                    $randomPercentageXp = mt_rand(70, 100) / 100;
                    $xpGained = ceil($randomPercentageXp * $adventure->getXp());
                    $totalXp = ceil($_SESSION['user']->getXp() + $xpGained);

                    $randomPercentageDollars = mt_rand(70, 100) / 100;
                    $dollarsGained = ceil($randomPercentageDollars * $adventure->getDollars());
                    $totalDollars = ceil($_SESSION['user']->getDollar() + $dollarsGained);

                    $stuffManager = new StuffManager();
                    $stuffIds = $stuffManager->getStuffIdsWhereMaxRequiredLvl($adventure->getRequiredLvl());
                    $randomStuffIndex = array_rand($stuffIds);
                    $stuffGainedId = $stuffIds[$randomStuffIndex];

                    $stuffGained = $stuffManager->createPossessionStuff($_SESSION['user']->getId(), $stuffGainedId);
                    $userManager->updateUser($_SESSION['user']->getId(), $totalDollars, $totalXp);

                    // put new values
                    $_SESSION['user']->addStuff($stuffGained);
                    $_SESSION['user']->setDollar($totalDollars);
                    $_SESSION['user']->setXp($totalXp);

                    echo json_encode([
                        'status' => 'success',
                        'stuff' => $stuffGained,
                        'dollars' => $totalDollars,
                        'xp' => $totalXp,
                        'dollarsGained' => $dollarsGained,
                        'xpGained' => $xpGained
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
        } else {
            header('HTTP/1.0 404 Not Found');
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
                            echo json_encode(['status' => 'error', 'message' => 'an unexpected error occurred']);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'You don\'t have the lvl to start this adventure']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'You\'re already in an adventure or you don\'t got your rewards']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'This adventure doesn\'t exist !']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'You\'re not connected !']);
        }
    }

    public function deleteAdventure() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            $errors = false;
            $message = '';
            $adventuresManager;

            if (!isset($_POST['adventureId'])) {
                $errors = true;
                $message = 'adventureId is undefined';
            } else {
                try {
                    $adventuresManager = new AdventureManager();
                    $adventure = $adventuresManager->getAdventure($_POST['adventureId']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                // if the adventure doesn't exist
                if ($adventure == false) {
                    $errors = true;
                    $message = 'This adventure doesn\'t exist';
                }
            }

            if (!$errors) {
                try {
                    $userManager = new UserManager();
                    $userManager->resetAdventuresUsersWhereAdventureId($_POST['adventureId']);
                    $adventuresManager->deleteAdventure($_POST['adventureId']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                echo json_encode([
                    'status' => 'success'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected or you\'re not an admin'
            ]);
        }
    }

    public function createAdventure() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            if (isset($_POST['name'], $_POST['duration'], $_POST['requiredLvl'], $_POST['dollars'], $_POST['xp'])) {

                $errors = false;

                if (!($_POST['duration'] > 0 && $_POST['requiredLvl'] > 0 && $_POST['dollars'] >= 0 && $_POST['xp'] >= 0)) {
                    $errors = true;
                }

                if (!$errors) {
                    try {
                        $adventureManager = new AdventureManager();
                        $adventure = $adventureManager->createAdventure($_POST['name'], $_POST['duration'], $_POST['requiredLvl'], $_POST['dollars'], $_POST['xp']);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    // if the request was successfully executed
                    if ($adventure != false) {
                        echo json_encode([
                            'status' => 'success',
                            'adventure' => $adventure
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'An unexpected error occurred'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Bad values'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Undefined POST data'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected or you\'re not an admin'
            ]);
        }
    }

    public function editAdventure() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            if (isset($_POST['id'], $_POST['name'], $_POST['duration'], $_POST['requiredLvl'], $_POST['dollars'], $_POST['xp'])) {

                $errors = false;

                if (!($_POST['duration'] > 0 && $_POST['requiredLvl'] > 0 && $_POST['dollars'] > 0 && $_POST['xp'] > 0)) {
                    $errors = true;
                }

                if (!$errors) {

                    try {
                        $adventureManager = new AdventureManager();
                        $notErrors = $adventureManager->updateAdventure($_POST['id'], $_POST['name'], $_POST['duration'], $_POST['requiredLvl'], $_POST['dollars'], $_POST['xp']);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    // if the request was successfully executed
                    if ($notErrors) {
                        echo json_encode(['status' => 'success']);
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'An unexpected error occurred'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Bad values'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Undefined POST data'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected or you\'re not an admin'
            ]);
        }
    }
}
