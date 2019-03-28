<?php

namespace ClementPatigny\Controller;

use ClementPatigny\Model\StuffManager;

class StuffController extends AppController {

    /**
     * equip stuff if the user owns the stuff
     * @throws \Exception
     */
    public function equipStuff() {
        if (isset($_SESSION['user'])) {
            if (isset($_POST['stuffId'])) {

                $stuff = $_SESSION['user']->hasStuff($_POST['stuffId']);

                // if the user owns the stuff
                if ($stuff != false) {

                    // if the stuff isn't already equipped
                    if (!$stuff->getEquipped()) {
                        $stuffEquipped = $_SESSION['user']->hasStuffEquipped($stuff->getType());

                        $stuffManager = new StuffManager();

                        // if there is already another stuff equipped
                        if ($stuffEquipped != false) {
                            // unequip it before equip the new stuff
                            $stuffEquipped->setEquipped(0);
                            $success = $stuffManager->setEquippedValue($stuffEquipped->getId(), 0);

                            if ($success) {
                                // equip the new stuff
                                $stuff->setEquipped(1);
                                $success = $stuffManager->setEquippedValue($stuff->getId(), 1);

                                if ($success) {
                                    echo json_encode(['status' => 'success']);
                                } else {
                                    echo json_encode(['status' => 'error', 'message' => 'an unexpected error occurred']);
                                }
                            } else {
                                echo json_encode([
                                    'status' => 'error',
                                    'message' => 'an unexpected error occurred'
                                ]);
                            }
                        } else {
                            // just equip the new stuff
                            $stuff->setEquipped(1);
                            $success = $stuffManager->setEquippedValue($stuff->getId(), 1);

                            if ($success) {
                                echo json_encode(['status' => 'success']);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'an unexpected error occurred']);
                            }
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'this stuff is already equipped']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'you don\'t own this stuff']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'stuffId is undefined']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'You\'re not connected !']);
        }
    }

    /**
     * @throws \Exception
     */
    public function unequipStuff() {
        if (isset($_SESSION['user'])) {
            if (isset($_POST['stuffId'])) {

                $stuff = $_SESSION['user']->hasStuff($_POST['stuffId']);

                // if the user owns the stuff
                if ($stuff != false) {

                    // if the stuff is equipped
                    if ($stuff->getEquipped()) {
                        $stuffManager = new StuffManager();
                        $stuff->setEquipped(0);
                        $success = $stuffManager->setEquippedValue($stuff->getId(), 0);

                        if ($success) {
                            echo json_encode(['status' => 'success']);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'an unexpected error occurred']);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'this stuff is already unequipped']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'you don\'t own this stuff']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'stuffId is undefined']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'You\'re not connected !']);
        }
    }

    /**
     * returns all stuff in JSON format
     * @throws \Exception
     */
    public function getJSONStuff() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            try {
                $stuffManager = new StuffManager();
                $allStuff = $stuffManager->getAllStuff();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            echo json_encode([
                'status' => 'success',
                'allStuff' => $allStuff
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected or you\'re not an admin'
            ]);
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteStuff() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            if (isset($_POST['stuffId'])) {
                if ($_POST['stuffId'] > 0) {
                    try {
                        $stuffManager = new StuffManager();
                        $stuffManager->deleteStuffAndPossesionsStuff($_POST['stuffId']);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'bad value'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'StuffId is undefined'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected or you\'re not an admin'
            ]);
        }
    }

    /**
     * edit a stuff
     */
    public function editStuff() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            if (isset($_POST['id'], $_POST['name'], $_POST['type'], $_POST['requiredLvl'], $_POST['stat'], $_POST['rarity'])) {

                $errors = false;

                if (!($_POST['requiredLvl'] > 0 && $_POST['stat'] > 0)) {
                    $errors = true;
                }

                if (!$errors) {
                    try {
                        $stuffManager = new StuffManager();
                        $notErrors = $stuffManager->updateStuff($_POST['id'], $_POST['name'], $_POST['type'], $_POST['requiredLvl'], $_POST['stat'], $_POST['rarity']);
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
                    'message' => 'Undefined POST values'
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
