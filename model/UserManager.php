<?php

namespace ClementPatigny\Model;

class UserManager extends Manager {
    /**
     * add a new user to the db
     * @param $userFeatures
     * @throws \Exception
     */
    public function createUser($userFeatures) {
        $db = $this->connectDb();

        try {
            $q = $db->prepare('INSERT INTO minirpg_users(pseudo, email, password, confirmation_key) VALUE(:pseudo, :email, :password, :confirmation_key)');

            $q->bindValue(':pseudo', $userFeatures['pseudo'], \PDO::PARAM_STR);
            $q->bindValue(':email', $userFeatures['email'], \PDO::PARAM_STR);
            $q->bindValue(':password', $userFeatures['password'], \PDO::PARAM_STR);
            $q->bindValue(':confirmation_key', $userFeatures['confirmationKey'], \PDO::PARAM_STR);

            $q->execute();
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

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

    /**
     * @param string $pseudo
     * @return string
     * @throws \Exception
     */
    public function getPseudo($pseudo) {
        $db = $this->connectDb();

        try {
            $q = $db->prepare('SELECT pseudo FROM minirpg_users WHERE pseudo = ?');
            $q->execute([$pseudo]);
            $pseudo = $q->fetch()['pseudo'];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $pseudo;
    }

    /**
     * @param string $email
     * @return string
     * @throws \Exception
     */
    public function getEmail($email) {
        $db = $this->connectDb();

        try {
            $q = $db->prepare('SELECT email FROM minirpg_users WHERE email = ?');
            $q->execute([$email]);
            $email = $q->fetch()['email'];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $email;
    }
}
