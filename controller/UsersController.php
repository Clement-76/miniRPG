<?php

namespace ClementPatigny\Controller;

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

                if (password_verify($_POST['password_connect'], $user['password'])) {
                    $_SESSION['user'] = $user['userObj'];
                    header('Location: index.php?action=home.displayHome');
                    exit();
                } else {
                    $loginErrors = true;
                }
            }

            $pageTitle = "Connexion / Inscription";
            $activeForm = "login";

            echo $this->twig->render('login_register.twig', compact('loginErrors', 'pageTitle', 'activeForm'));
        } else {
            header('Location: index.php?action=home.displayHome');
            exit();
        }
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
                    $registerErrors['email'] = true;
                    $registerErrors['errors'] = true;
                }

                if ($_POST['email'] == $email) {
                    $registerErrors['email_not_available'] = true;
                    $registerErrors['errors'] = true;
                }

                try {
                    $pseudo = $userManager->getPseudo($_POST['pseudo']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                if ($_POST['pseudo'] == $pseudo) {
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
                        $userManager->createUser($userData);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    header('Location: index.php');
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
            header('Location: index.php?action=home.displayHome');
            exit();
        }
    }
}
