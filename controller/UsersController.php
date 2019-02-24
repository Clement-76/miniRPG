<?php

namespace ClementPatigny\Controller;

use ClementPatigny\Model\UserManager;

class UsersController extends AppController {
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
            $errors['errors'] = false;

            if (isset($_POST['email']) && isset($_POST['pseudo']) && isset($_POST['password'])) {

                try {
                    $userManager = new UserManager();
                    $email = $userManager->getEmail($_POST['email']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                if (!preg_match('#^[a-z\d]+([.\-_]{1}[a-z\d]+)*@[a-z\d]+\.[a-z]+$#', $_POST['email'])) {
                    $errors['email'] = true;
                    $errors['errors'] = true;
                }

                if ($_POST['email'] == $email) {
                    $errors['email_not_available'] = true;
                    $errors['errors'] = true;
                }

                try {
                    $pseudo = $userManager->getPseudo($_POST['pseudo']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                if ($_POST['pseudo'] == $pseudo) {
                    $errors['pseudo_not_available'] = true;
                    $errors['errors'] = true;
                }

                if (!preg_match('#^[a-zA-Z\d\_\-.]{0,25}$#', $_POST['pseudo'])) {
                    $errors['pseudo'] = true;
                    $errors['errors'] = true;
                }

                $lowercase = preg_match('#[a-z]#', $_POST['password']);
                $number = preg_match('#\d#', $_POST['password']);

                if (!$lowercase || !$number || strlen($_POST['password']) < 8) {
                    $errors['password'] = true;
                    $errors['errors'] = true;
                }

                if (!isset($_POST['terms'])) {
                    $errors['password'] = true;
                    $errors['terms'] = true;
                }

                if (!$errors['errors']) {
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

            $pageTitle = "Connexion / Inscription";

            if (!$errors['errors']) {
                echo $this->twig->render('login_register.twig', compact('errors', 'pageTitle'));
            } else {
                $email = $_POST['email'];
                $pseudo = $_POST['pseudo'];
                echo $this->twig->render('login_register.twig', compact('errors', 'pageTitle', 'email', 'pseudo'));
            }
        } else {
            header('Location: index.php?action=');
            exit();
        }
    }
}
