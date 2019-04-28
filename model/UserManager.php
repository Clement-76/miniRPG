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

            if ($user != false) {
                $stuffManager = new StuffManager();
                $stuff = $stuffManager->getPossessionsStuff($user['id']);

                $userFeatures = [
                    'id' => $user['id'],
                    'pseudo' => $user['pseudo'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'confirmationKey' => $user['confirmation_key'],
                    'confirmedEmail' => $user['confirmed_email'],
                    'warnings' => $user['warnings'],
                    'banned' => $user['banned'],
                    'registrationDate' => $user['registration_date'],
                    'tutorial' => $user['tutorial'],
                    'baseLife' => $user['life'],
                    'naturalAttack' => $user['attack'],
                    'naturalDefense' => $user['defense'],
                    'dollar' => $user['$'],
                    'T' => $user['T'],
                    'xp' => $user['xp'],
                    'remainingBattles' => $user['remaining_battles'],
                    'lastBattle' => $user['last_battle'],
                    'newAttemptTimerStartTime' => $user['new_attempt_timer_start_time'],
                    'adventureBeginning' => $user['adventure_beginning'],
                    'currentAdventureId' => $user['current_adventure_id'],
                    'inventory' => $stuff
                ];

                $userObj = new User($userFeatures);

                return [
                    'password' => $user['password'],
                    'userObj' => $userObj
                ];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getUserById($userId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('SELECT * FROM minirpg_users WHERE id = ?');
            $q->execute([$userId]);
            $user = $q->fetch();

            if ($user != false) {
                $stuffManager = new StuffManager();
                $stuff = $stuffManager->getPossessionsStuff($user['id']);

                $userFeatures = [
                    'id' => $user['id'],
                    'pseudo' => $user['pseudo'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'confirmationKey' => $user['confirmation_key'],
                    'confirmedEmail' => $user['confirmed_email'],
                    'warnings' => $user['warnings'],
                    'banned' => $user['banned'],
                    'registrationDate' => $user['registration_date'],
                    'tutorial' => $user['tutorial'],
                    'baseLife' => $user['life'],
                    'naturalAttack' => $user['attack'],
                    'naturalDefense' => $user['defense'],
                    'dollar' => $user['$'],
                    'T' => $user['T'],
                    'xp' => $user['xp'],
                    'remainingBattles' => $user['remaining_battles'],
                    'lastBattle' => $user['last_battle'],
                    'newAttemptTimerStartTime' => $user['new_attempt_timer_start_time'],
                    'adventureBeginning' => $user['adventure_beginning'],
                    'currentAdventureId' => $user['current_adventure_id'],
                    'inventory' => $stuff
                ];

                $userObj = new User($userFeatures);

                return $userObj;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * add a new user to the db
     * @param $userFeatures
     * @return string the user id
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

        return $db->lastInsertId();
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

    /**
     * start a new adventure for the user currently connected
     * @param $adventureId
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function startAdventure($adventureId, $userId) {
        $db = $this->getDb();

        try {
            $q = $db->prepare(
                'UPDATE minirpg_users
                 SET adventure_beginning = NOW(), current_adventure_id = ?
                 WHERE id = ?'
            );
            $q->execute([$adventureId, $userId]);
            $count = $q->rowCount();
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $count > 0;
    }

    /**
     * updates the number of dollars and xp of the user and reset the user's adventure
     * @param $userId
     * @param $dollars
     * @param $xp
     * @throws \Exception
     */
    public function updateUser($userId, $dollars, $xp) {
        try {
            $db = $this->getDb();
            $q = $db->prepare(
                'UPDATE minirpg_users 
                 SET current_adventure_id = NULL,
                 adventure_beginning = NULL,
                 $ = :dollars,
                 xp = :xp
                 WHERE id = :userId'
            );

            $q->execute([
                ':userId' => $userId,
                ':dollars' => $dollars,
                ':xp' => $xp
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * resets the adventure of users who are in the adventure that has as id adventureId
     * @param $adventureId
     * @throws \Exception
     */
    public function resetAdventuresUsersWhereAdventureId($adventureId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare(
                'UPDATE minirpg_users 
                 SET current_adventure_id = NULL,
                 adventure_beginning = NULL,
                 WHERE current_adventure_id = ?'
            );

            $q->execute([$adventureId]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * returns all users
     * @return User[] array of User objects
     * @throws \Exception
     */
    public function getUsers() {
        try {
            $db = $this->getDb();
            $q = $db->query('SELECT * FROM minirpg_users ORDER BY warnings DESC');
            $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $users = [];

        while ($user = $q->fetch()) {
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
                'baseLife' => $user['life'],
                'naturalAttack' => $user['attack'],
                'naturalDefense' => $user['defense'],
                'dollar' => $user['$'],
                'T' => $user['T'],
                'xp' => $user['xp'],
                'remainingBattles' => $user['remaining_battles'],
                'lastBattle' => $user['last_battle'],
                'newAttemptTimerStartTime' => $user['new_attempt_timer_start_time'],
                'adventureBeginning' => $user['adventure_beginning'],
                'currentAdventureId' => $user['current_adventure_id']
            ];

            $users[] = new User($userFeatures);
        }


        return $users;
    }

    /**
     * increments the number of warnings of the user
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function updateUserWarnings($userId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('UPDATE minirpg_users SET warnings = warnings + 1 WHERE id = :userId');
            $q->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $notErrors = $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $notErrors;
    }

    /**
     * set the banned column of the user to 1
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function updateBanned($userId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare('UPDATE minirpg_users SET banned = 1 WHERE id = :userId');
            $q->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $notErrors = $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $notErrors;
    }

    /**
     * returns users who have an xp between $minXp and $maxXp
     * @param $minXp
     * @param $maxXp
     * @param $userId
     * @return User[] an array of User objects
     * @throws \Exception
     */
    public function getUsersWithLvlDifference($minXp, $maxXp, $userId) {
        try {
            $db = $this->getDb();
            $q = $db->prepare(
                'SELECT * 
                 FROM minirpg_users 
                 WHERE xp >= :minXp
                 AND xp <= :maxXp
                 AND id != :userId
                 AND banned = 0
                 ORDER BY xp DESC'
            );

            $q->bindValue(':minXp', $minXp, \PDO::PARAM_INT);
            $q->bindValue(':maxXp', $maxXp, \PDO::PARAM_INT);
            $q->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $users = [];

        while ($user = $q->fetch()) {
            $userFeatures = [
                'id' => $user['id'],
                'pseudo' => $user['pseudo'],
                'email' => $user['email'],
                'role' => $user['role'],
                'confirmationKey' => $user['confirmation_key'],
                'confirmedEmail' => $user['confirmed_email'],
                'warnings' => $user['warnings'],
                'banned' => $user['banned'],
                'registrationDate' => $user['registration_date'],
                'tutorial' => $user['tutorial'],
                'baseLife' => $user['life'],
                'naturalAttack' => $user['attack'],
                'naturalDefense' => $user['defense'],
                'dollar' => $user['$'],
                'T' => $user['T'],
                'xp' => $user['xp'],
                'remainingBattles' => $user['remaining_battles'],
                'lastBattle' => $user['last_battle'],
                'newAttemptTimerStartTime' => $user['new_attempt_timer_start_time'],
                'adventureBeginning' => $user['adventure_beginning'],
                'currentAdventureId' => $user['current_adventure_id']
            ];

            $users[] = new User($userFeatures);
        }

        return $users;
    }

    /**
     * @param $userId
     * @param $remainingBattles
     * @return bool
     * @throws \Exception
     */
    public function updateUserRemainingBattles($userId, $remainingBattles) {
        try {
            $db = $this->getDb();
            $q = $db->prepare(
                'UPDATE minirpg_users 
                 SET remaining_battles = :remainingBattles,
                 last_battle = NOW()
                 WHERE id = :userId'
            );

            $q->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $q->bindValue(':remainingBattles', $remainingBattles, \PDO::PARAM_INT);
            $notErrors = $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $notErrors;
    }

    /**
     * @param $userId
     * @param $date
     * @return bool
     * @throws \Exception
     */
    public function updateUserAttemptTimerStartTime($userId, $date) {
        try {
            $db = $this->getDb();
            $q = $db->prepare(
                'UPDATE minirpg_users 
                 SET new_attempt_timer_start_time = :date
                 WHERE id = :userId'
            );

            $q->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $q->bindValue(':date', $date, \PDO::PARAM_STR);
            $notErrors = $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $notErrors;
    }
}
