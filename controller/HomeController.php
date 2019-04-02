<?php

namespace ClementPatigny\Controller;

class HomeController extends AppController {
    public function displayHome() {
        if (isset($_SESSION['user'])) {
            $pageTitle = "miniRPG";
            $this->twig->addGlobal('session', $_SESSION['user']);
            echo $this->twig->render('home.twig', compact('pageTitle'));
        } else {
            header('Location: '. baseUrl . '/login');
            exit();
        }
    }
}
