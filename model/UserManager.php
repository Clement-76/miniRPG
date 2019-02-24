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
