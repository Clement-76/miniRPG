<?php

namespace ClementPatigny\Model;

class UserManager extends Manager {
    /**
     * get the user in the db with his email or password
     * @param $login the email or password
     * @return object User
     * @throws \Exception
     */
    public function getUser($login) {
        $db = $this->connectDb();

        // determine if the login is the email or the pseudo
        if (preg_match("#^[a-z\d]+([.\-_]{1}[a-z\d]+)*@[a-z]+\.[a-z]+$#", $login)) {
            // email
            $q = $db->prepare('SELECT * FROM users WHERE email = ?');
        } else {
            // pseudo
            $q = $db->prepare('SELECT * FROM users WHERE pseudo = ?');
        }

        $q->execute([$login]);
        $user = $q->fetch();

        $userFeatures = [
            'id' => $user['id'],
            'pseudo' => $user['pseudo'],
            'email' => $user['email'],
            'password' => $user['password'],
            'role' => $user['role'],
            'confirmationKey' => $user['confirmation_key'],
            'confirmedEmail' => $user['confirmed_email'],
            'warnings' => $user['warnings'],
            'registrationDate' => $user['registration_date'],
            'tutorial' => $user['tutorial'],
            'life' => $user['life'],
            'attack' => $user['attack'],
            'defense' => $user['defense'],
            'dollar' => $user['$'],
            'T' => $user['T'],
            'xp' => $user['xp'],
            'remainingBattles' => $user['remaining_battles'],
            'lastBattle' => $user['last_battle'],
            'adventureBeginning' => $user['adventure_beginning'],
            'currentAdventureId' => $user['current_adventure_id']
        ];

        $userObj = new User($userFeatures);
        return $userObj;
    }
}
