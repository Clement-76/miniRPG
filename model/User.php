<?php

namespace ClementPatigny\Model;

class User {
    private $_id;
    private $_pseudo;
    private $_email;
    private $_password;
    private $_role;
    private $_confirmationKey;
    private $_confirmedEmail;
    private $_warnings;
    private $_banned;
    private $_registrationDate;
    private $_tutorial;
    private $_life;
    private $_attack;
    private $_defense;
    private $_dollar;
    private $_T;
    private $_xp;
    private $_lvl;
    private $_remainingBattles;
    private $_lastBattle;
    private $_adventureBeginning;
    private $_currentAdventureId;

    /**
     * User constructor.
     * @param array $userFeatures
     */
    public function __construct(array $userFeatures) {
        $this->hydrate($userFeatures);
    }

    public function hydrate($userFeatures) {
        foreach ($userFeatures as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->_id = (int) $id;
    }

    /**
     * @param string $pseudo
     */
    public function setPseudo($pseudo) {
        if (is_string($pseudo)) {
            $this->_pseudo = $pseudo;
        }
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        if (is_string($email)) {
            $this->_email = $email;
        }
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        if (is_string($password)) {
            $this->_password = $password;
        }
    }

    /**
     * @param string $role
     */
    public function setRole($role) {
        if (is_string($role)) {
            $this->_role = $role;
        }
    }

    /**
     * @param int $confirmationKey
     */
    public function setConfirmationKey($confirmationKey) {
        $this->_confirmationKey = (int) $confirmationKey;
    }

    /**
     * @param int $confirmedEmail
     */
    public function setConfirmedEmail($confirmedEmail) {
        if ($confirmedEmail == 1) {
            $this->_confirmedEmail = true;
        } else {
            $this->_confirmedEmail = false;
        }
    }

    /**
     * @param int $warnings
     */
    public function setWarnings($warnings) {
        $this->_warnings = (int) $warnings;
    }

    /**
     * @param int $banned
     */
    public function setBanned($banned) {
        if ($banned == 1) {
            $this->_banned = true;
        } else {
            $this->_banned = false;
        }
    }

    /**
     * @param string $registrationDate
     */
    public function setRegistrationDate($registrationDate) {
        $this->_registrationDate = new \DateTime($registrationDate);
    }

    /**
     * @param int $tutorial
     */
    public function setTutorial($tutorial) {
        if ($tutorial == 1) {
            $this->_tutorial = true;
        } else {
            $this->_tutorial = false;
        }
    }

    /**
     * @param int $life
     */
    public function setLife($life) {
        $this->_life = (int) $life;
    }

    /**
     * @param int $attack
     */
    public function setAttack($attack) {
        $this->_attack = (int) $attack;
    }

    /**
     * @param int $defense
     */
    public function setDefense($defense) {
        $this->_defense = (int) $defense;
    }

    /**
     * @param int $dollar
     */
    public function setDollar($dollar) {
        $this->_dollar = (int) $dollar;
    }

    /**
     * @param int $T
     */
    public function setT($T) {
        $this->_T = (int) $T;
    }

    /**
     * @param int $xp
     */
    public function setXp($xp) {
        $this->_xp = (int) $xp;
        $this->setLvl($this->_xp);
    }

    /**
     * @param int $actualXp
     */
    public  function setLvl($actualXp) {
        $actualLvl = 1;
        $xpTotal = 0;

        for ($lvl = 2; $lvl <= 100; $lvl++) {
            $xp = 100 * pow($lvl + 1, 2);
            $xpTotal += $xp;

            if ($xpTotal > $actualXp) {
                $actualLvl = $lvl - 1;
                break;
            } else if ($xpTotal == $actualXp) {
                $actualLvl = $lvl - 1;
                break;
            }
        }

        $this->_lvl = $actualLvl;
    }

    /**
     * @param int $remainingBattles
     */
    public function setRemainingBattles($remainingBattles) {
        $this->_remainingBattles = (int) $remainingBattles;
    }

    /**
     * @param string $lastBattle
     */
    public function setLastBattle($lastBattle) {
        $this->_lastBattle = $lastBattle;
    }

    /**
     * @param string $adventureBeginning the date when the adventure started
     */
    public function setAdventureBeginning($adventureBeginning) {
        $this->_adventureBeginning = $adventureBeginning;
    }

    /**
     * @param int $currentAdventureId
     */
    public function setCurrentAdventureId($currentAdventureId) {
        $this->_currentAdventureId = (int) $currentAdventureId;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getPseudo() {
        return $this->_pseudo;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->_email;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->_password;
    }

    /**
     * @return string
     */
    public function getRole() {
        return $this->_role;
    }

    /**
     * @return int
     */
    public function getConfirmationKey() {
        return $this->_confirmationKey;
    }

    /**
     * @return int
     */
    public function getConfirmedEmail() {
        return $this->_confirmedEmail;
    }

    /**
     * @return int
     */
    public function getWarnings() {
        return $this->_warnings;
    }

    /**
     * @return int
     */
    public function getBanned() {
        return $this->_banned;
    }

    /**
     * @return string
     */
    public function getRegistrationDate() {
        return $this->_registrationDate;
    }

    /**
     * @return int
     */
    public function getTutorial() {
        return $this->_tutorial;
    }

    /**
     * @return int
     */
    public function getLife() {
        return $this->_life;
    }

    /**
     * @return int
     */
    public function getAttack() {
        return $this->_attack;
    }

    /**
     * @return int
     */
    public function getDefense() {
        return $this->_defense;
    }

    /**
     * @return int
     */
    public function getDollar() {
        return $this->_dollar;
    }

    /**
     * @return int
     */
    public function getT() {
        return $this->_T;
    }

    /**
     * @return int
     */
    public function getXp() {
        return $this->_xp;
    }

    /**
     * @return int
     */
    public function getLvl() {
        return $this->_lvl;
    }

    /**
     * @return int
     */
    public function getRemainingBattles() {
        return $this->_remainingBattles;
    }

    /**
     * @return string
     */
    public function getLastBattle() {
        return $this->_lastBattle;
    }

    /**
     * @return string
     */
    public function getAdventureBeginning() {
        return $this->_adventureBeginning;
    }
    /**
     * @return int
     */
    public function getCurrentAdventureId() {
        return $this->_currentAdventureId;
    }
}
