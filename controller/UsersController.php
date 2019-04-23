<?php

namespace ClementPatigny\Controller;

use ClementPatigny\Model\StuffManager;
use ClementPatigny\Model\UserManager;

class UsersController extends AppController {
    /**
     * displays the form to login and if the form
     * has been submitted checks the data and login the user
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function login() {
        if (!isset($_SESSION['user'])) {
            $loginErrors = false;
            $bannedErrors = false;

            if (isset($_POST['login_connect']) && isset($_POST['password_connect'])) {
                if (preg_match('#^[a-z\d]+([.\-_]{1}[a-z\d]+)*@[a-z\d]+\.[a-z]+$#', $_POST['login_connect'])) {
                    $loginType = 'email';
                } else {
                    $loginType = 'pseudo';
                }

                try {
                    $userManager = new UserManager();
                    $user = $userManager->getUser($_POST['login_connect'], $loginType);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                if ($user != false) {
                    if (password_verify($_POST['password_connect'], $user['password'])) {
                        if ($user['userObj']->getBanned() == false) {
                            $_SESSION['user'] = $user['userObj'];
                            header('Location: '. baseUrl . '/home');
                            exit();
                        } else {
                            $bannedErrors = true;
                        }
                    } else {
                        $loginErrors = true;
                    }
                } else {
                    $loginErrors = true;
                }
            }

            $pageTitle = "Connexion / Inscription";
            $activeForm = "login";

            echo $this->twig->render('login_register.twig', compact('loginErrors', 'bannedErrors', 'pageTitle', 'activeForm'));
        } else {
            header('Location: '. baseUrl . '/home');
            exit();
        }
    }

    /**
     * logout the user by destroying session variables
     */
    public function logout() {
        $_SESSION = [];
        session_destroy();

        header('Location: '. baseUrl . '/login');
        exit();
    }

    /**
     * displays the form to register and if the form
     * has been submitted checks the data and creates a new user
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function register() {
        if (!isset($_SESSION['user'])) {
            $registerErrors['errors'] = false;

            if (isset($_POST['email']) && isset($_POST['pseudo']) && isset($_POST['password'])) {
                try {
                    $userManager = new UserManager();
                    $email = $userManager->getEmail($_POST['email']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                if (!preg_match('#^[a-z\d]+([.\-_]{1}[a-z\d]+)*@[a-z\d]+\.[a-z]+$#', $_POST['email'])) {
                    $registerErrors['email'] = !preg_match('#^[a-z\d]+([.\-_]{1}[a-z\d]+)*@[a-z\d]+\.[a-z]+$#', $_POST['email']);
                    $registerErrors['errors'] = true;
                }

                if (strtoupper($_POST['email']) == strtoupper($email)) {
                    $registerErrors['email_not_available'] = true;
                    $registerErrors['errors'] = true;
                }

                try {
                    $pseudo = $userManager->getPseudo($_POST['pseudo']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                if (strtoupper($_POST['pseudo']) == strtoupper($pseudo)) {
                    $registerErrors['pseudo_not_available'] = true;
                    $registerErrors['errors'] = true;
                }

                if (!preg_match('#^[a-zA-Z\d\_\-.]{0,25}$#', $_POST['pseudo'])) {
                    $registerErrors['pseudo'] = true;
                    $registerErrors['errors'] = true;
                }

                $lowercase = preg_match('#[a-z]#', $_POST['password']);
                $number = preg_match('#\d#', $_POST['password']);

                if (!$lowercase || !$number || strlen($_POST['password']) < 8) {
                    $registerErrors['password'] = true;
                    $registerErrors['errors'] = true;
                }

                if (!isset($_POST['terms'])) {
                    $registerErrors['password'] = true;
                    $registerErrors['terms'] = true;
                }

                if (!$registerErrors['errors']) {
                    $confirmKey = "";
                    for ($i = 0; $i < 16; $i++) {
                        $confirmKey .= mt_rand(0, 9);
                    }

                    $userData = [
                        'email' => $_POST['email'],
                        'pseudo' => $_POST['pseudo'],
                        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                        'confirmationKey' => $confirmKey
                    ];

                    try {
                        $userId = $userManager->createUser($userData);
                        $stuffManager = new StuffManager();
                        $stuffIds = $stuffManager->getStuffIdsWhereMaxRequiredLvl(1);
                        $randomStuffIndex = array_rand($stuffIds);
                        $stuffId = $stuffIds[$randomStuffIndex];
                        $stuffManager->createPossessionStuff($userId, $stuffId);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    header('Location: '. baseUrl . '/login');
                    exit();
                }
            }

            $pageTitle = "Inscription / Connexion";
            $activeForm = "register";

            if (!$registerErrors['errors']) {
                echo $this->twig->render('login_register.twig', compact('registerErrors', 'pageTitle', 'activeForm'));
            } else {
                $email = $_POST['email'];
                $pseudo = $_POST['pseudo'];
                echo $this->twig->render('login_register.twig', compact('registerErrors', 'pageTitle', 'activeForm', 'email', 'pseudo'));
            }
        } else {
            header('Location: '. baseUrl . '/home');
            exit();
        }
    }

    public function getJSONUser() {
        if (isset($_SESSION['user'])) {

            try {
                $userManager = new UserManager();
                $user = $userManager->getUser($_SESSION['user']->getPseudo(), 'pseudo');
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            $_SESSION['user'] = $user['userObj'];

            echo json_encode([
                'status' => 'success',
                'userFeatures' => $_SESSION['user']
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected !'
            ]);
        }
    }

    /**
     * @param int $lvlToConvert the lvl to convert in xp
     * @param string $extremum 'min' or 'max', specify if the function will returns
     * the max or min xp of the lvl to convert
     * @return int
     * @throws \Exception
     */
    public function lvlToXp($lvlToConvert = null, $extremum = 'min') {
        if ($lvlToConvert !== null) {
            $xpTotal = 0;

            for ($lvl = 2; $lvl <= $lvlToConvert; $lvl++) {
                $xpToThisLvl = 100 * pow($lvl + 1, 2);
                $xpTotal += $xpToThisLvl;
            }

            if ($extremum === 'min') {
                return $xpTotal;
            } else if ($extremum === 'max') {
                if ($lvlToConvert < 100) {
                    // - 1 to return the max xp of the lvl just before lvl up
                    $xpToNextLvl = 100 * pow($lvl + 1, 2) - 1;
                    return $xpTotal + $xpToNextLvl;
                } else {
                    return $xpTotal;
                }
            } else {
                throw new \Exception('Bad value for extremum variable');
            }
        } else {
            header('HTTP/1.0 404 Not Found');
            exit();
        }
    }

    /**
     * returns users that are almost the same lvl (according to the value of $lvlDiff
     * @throws \Exception
     */
    public function getJSONUsersWithLvlDifference() {
        if (isset($_SESSION['user'])) {

            $lvlDiff = 2;
            $userLvl = $_SESSION['user']->getLvl();
            $minXp = $this->lvlToXp($userLvl - $lvlDiff);
            $maxXp = $this->lvlToXp($userLvl + $lvlDiff, 'max');

            try {
                $userManager = new UserManager();
                $players = $userManager->getUsersWithLvlDifference($minXp, $maxXp, $_SESSION['user']->getId());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            if (empty($players)) $players = false;

            echo json_encode([
                'status' => 'success',
                'players' => $players
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected'
            ]);
        }
    }

    /**
     * returns all users in JSON
     * @throws \Exception
     */
    public function getJSONUsers() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {

            try {
                $userManager = new UserManager();
                $users = $userManager->getUsers();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            echo json_encode([
                'status' => 'success',
                'users' => $users
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
    public function banUser() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            if (isset($_POST['userId'])) {
                try {
                    $userManager = new UserManager();
                    $notErrors = $userManager->updateBanned($_POST['userId']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

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
                    'message' => 'userId is undefined'
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
     * adds a warnings to the user
     * @throws \Exception
     */
    public function warnUser() {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() == 'admin') {
            if (isset($_POST['userId'])) {
                try {
                    $userManager = new UserManager();
                    $notErrors = $userManager->updateUserWarnings($_POST['userId']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

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
                    'message' => 'userId is undefined'
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
     * @param null $lvl the lvl to convert to xp
     * @return int
     */
    public function getTotalXpNeededToNextLvl($lvl = null) {
        if ($lvl != null) {
            return 100 * pow($lvl + 1, 2);
        } else {
            header('HTTP/1.0 404 Not Found');
            exit();
        }
    }

    /**
     * @throws \Exception
     */
    public function fightUser() {
        if (isset($_SESSION['user'])) {
            if (isset($_POST['userId'])) {
                $errors = [];

                try {
                    $userManager = new UserManager();
                    $user = $userManager->getUserById($_POST['userId']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                // if the user doesn't exist
                if ($user == false) {
                    echo json_encode([
                        'status' => 'error',
                        'messages' => 'This user doesn\'t exist'
                    ]);
                    exit();
                }

                if ($_SESSION['user']->getRemainingBattles() <= 0) {
                    $errors[] = 'No more attempts available';
                }

                $lvlDiff = 2;
                $enemyTooStrong = $_SESSION['user']->getLvl() + $lvlDiff < $user->getLvl();
                $enemyTooWeak = $_SESSION['user']->getLvl() - $lvlDiff > $user->getLvl();

                if ($enemyTooStrong || $enemyTooWeak) {
                    $errors[] = 'You don\'t have the lvl to fight this user';
                }

                if ($_POST['userId'] == $_SESSION['user']->getId()) {
                    $errors[] = 'You can\'t fight against yourself';
                }

                if ($user->getBanned()) {
                    $errors[] = 'This user is banned';
                }

                // if there are no errors
                if (empty($errors)) {
                    $results = $_SESSION['user']->fight($user);
                    $logs = $results['logs'];

                    try {
                        $userManager = new UserManager();
                        $userManager->updateUserRemainingBattles($_SESSION['user']->getId(), $_SESSION['user']->getRemainingBattles());
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    if ($results['win']) {
                        $myLvl = $_SESSION['user']->getLvl();
                        $percentageMultiplier = ($user->getLvl() - $myLvl) * 25 / 100;
                        $xpNeededForThisLvl = $this->getTotalXpNeededToNextLvl($myLvl);
                        $percentageMalusLvl = (101 - $myLvl) / 30 / ($myLvl + 8); // we need more fights when we have an higher lvl
                        $xpGained = round($xpNeededForThisLvl * $percentageMalusLvl);
                        $xpGained += round($xpGained * $percentageMultiplier);

                        $totalXp = $_SESSION['user']->getXp() + $xpGained;
                        $_SESSION['user']->setXp($totalXp);

                        $userManager->updateUser($_SESSION['user']->getId(), $_SESSION['user']->getDollar(), $totalXp);

                        echo json_encode([
                            'status' => 'success',
                            'win' => true,
                            'logs' => $logs,
                            'xpGained' => $xpGained,
                            'xp' => $totalXp
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'success',
                            'win' => false,
                            'logs' => $logs,
                        ]);
                    }
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'messages' => $errors
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'userId is undefined'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected'
            ]);
        }
    }
}
