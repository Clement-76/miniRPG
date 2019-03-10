<?php

namespace ClementPatigny\Model;

class UserManager extends Manager {
    /**
     * get the user in the db with his email or password
     * @param string $login the email or pseudo
     * @param string $loginType the type of the login, 'email' or 'pseudo'
     * @return array a User object and the password of the user
     * @throws \Exception
     */
    public function getUser($login, $loginType) {
        $db = $this->getDb();

        try {
            if ($loginType == 'email') {
                $q = $db->prepare('SELECT * FROM minirpg_users WHERE email = ?');
            } else {
                $q = $db->prepare('SELECT * FROM minirpg_users WHERE pseudo = ?');
            }

            $q->execute([$login]);
            $user = $q->fetch();

            $stuffManager = new StuffManager();
            $stuff = $stuffManager->getPossessionsStuff($user['id']);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $userFeatures = [
            'id' => $user['id'],
            'pseudo' => $user['pseudo'],
            'email' => $user['email'],
            'password' => $user['password'],
            'role' => $user['role'],
            'confirmationKey' => $user['confirmation_key'],
            'confirmedEmail' => $user['confirmed_email'],
            'warnings' => $user['warnings'],
            'banned' => $user['banned'],
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
            'currentAdventureId' => $user['current_adventure_id'],
            'inventory' => $stuff
        ];

        $userObj = new User($userFeatures);
        return [
            'password' => $user['password'],
            'userObj' => $userObj
        ];
    }

    /**
     * add a new user to the db
     * @param $userFeatures
     * @throws \Exception
     */
    public function createUser($userFeatures) {
        $db = $this->getDb();

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
     * @param string $pseudo
     * @return string
     * @throws \Exception
     */
    public function getPseudo($pseudo) {
        $db = $this->getDb();

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
        $db = $this->getDb();

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
