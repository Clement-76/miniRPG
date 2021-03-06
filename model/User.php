<?php

namespace ClementPatigny\Model;

class User implements \JsonSerializable {
    private $_maxRemainingBattles = 5;
    private $_timeToRecoverAnAttempt = 30 * 60; // in seconds
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
    private $_baseLife;
    private $_naturalAttack;
    private $_naturalDefense;
    private $_dollar;
    private $_T;
    private $_xp;
    private $_lvl;
    private $_remainingBattles;
    private $_lastBattle;
    private $_newAttemptTimerStartTime;
    private $_adventureBeginning;
    private $_currentAdventureId;
    private $_inventory;

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

    public function jsonSerialize() {
        $adventureBeginning = $this->_adventureBeginning == null ? null : $this->_adventureBeginning->getTimestamp() * 1000;

        return [
            'id' => $this->_id,
            'pseudo' => $this->_pseudo,
            'xp' => $this->_xp,
            'life' => $this->_life,
            'naturalAttack' => $this->_naturalAttack,
            'naturalDefense' => $this->_naturalDefense,
            'dollars' => $this->_dollar,
            'T' => $this->_T,
            'role' => $this->_role,
            'inventory' => $this->_inventory,
            'adventureBeginning' => $adventureBeginning,
            'currentAdventureId' => $this->_currentAdventureId,
            'lvl' => $this->_lvl,
            'registrationDate' => $this->_registrationDate->format('d/m/Y'),
            'warnings' => $this->_warnings,
            'banned' => $this->_banned,
            'newAttemptTimerStartTime' => $this->_newAttemptTimerStartTime,
            'maxRemainingBattles' => $this->_maxRemainingBattles,
            'timeToRecoverAnAttempt' => $this->_timeToRecoverAnAttempt,
            'battles' => $this->_remainingBattles,
        ];
    }

    /**
     * @return bool true if the user has one or more extra attempt
     */
    public function hasOneOrMoreExtraAttempt() {
        if (!$this->hasMaxRemainingBattles()) {
            if ($this->_newAttemptTimerStartTime == null) {
                $this->_newAttemptTimerStartTime = new \DateTime();
            }

            $now = new \DateTime();
            $now = $now->getTimestamp();
            $timerStart = $this->_newAttemptTimerStartTime->getTimestamp();

            return $now >= $this->_timeToRecoverAnAttempt + $timerStart;
        } else {
            return false;
        }
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getNumberAdditionalAttempts(): int {
        $now = new \DateTime();
        $now = $now->getTimestamp();
        $oldTimerValue = $this->_newAttemptTimerStartTime;
        $timeSpent = $now - $oldTimerValue->getTimestamp();

        return floor($timeSpent / $this->_timeToRecoverAnAttempt);
    }

    /**
     * @return int|string
     */
    public function getNewAttemptTimerValue() {
        if (!$this->hasMaxRemainingBattles()) {
            if (!$this->hasOneOrMoreExtraAttempt()) {
                $now = new \DateTime();
                $now = $now->getTimestamp();
                $oldTimerValue = $this->_newAttemptTimerStartTime;
                $timeSpent = $now - $oldTimerValue->getTimestamp();
                $remainingTimeOldTimer = $timeSpent % $this->_timeToRecoverAnAttempt;
                $newTimerTimestamp = $now - $remainingTimeOldTimer;
                $newTimerValue = new \DateTime();
                $newTimerValue->setTimestamp($newTimerTimestamp);

                return $newTimerValue->format('Y-m-d H:i:s');
            } else {
                $now = new \DateTime();
                return $now->format('Y-m-d H:i:s');
            }
        } else {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function hasMaxRemainingBattles() {
        return $this->_remainingBattles == $this->_maxRemainingBattles;
    }

    /**
     * if the player owns the stuff returns it if not returns false
     * @param $stuffId
     * @return bool
     */
    public function hasStuff($stuffId) {
        foreach ($this->_inventory as $stuff) {
            if ($stuff->getId() == $stuffId) {
                return $stuff;
            }
        }

        return false;
    }

    /**
     * if there is stuff already equipped, returns it if not returns false
     * @param $type
     * @return bool
     */
    public function hasStuffEquipped($type) {
        foreach ($this->_inventory as $stuff) {
            if ($stuff->getEquipped() && $stuff->getType() == $type) {
                return $stuff;
            }
        }

        return false;
    }

    /**
     * @param Stuff $stuff
     */
    public function addStuff(Stuff $stuff) {
        $this->_inventory[] = $stuff;
    }

    /**
     * @param User $target
     * @return array contains the logs of the fight and a boolean
     * (true if the user won, false if he lost)
     */
    public function fight(User $target) {
        $this->_remainingBattles--;
        $logs = [];
        $win = null;
        $targetLife = $target->getLife();
        $myLife = $this->_life;

        $logs[] = $target->getPseudo() . ' possède ' . $targetLife . ' points de vitalité';

        while ($targetLife > 0 && $myLife > 0) {
            $damage =  round(($this->getTotalAttack() * (mt_rand(80, 120) / 100)) - $target->getTotalDefense());
            $damage = $damage >= 0 ? $damage : 0;
            $targetLife -= $damage;
            $logs[] = 'Vous attaquez ' . $target->getPseudo();
            $logs[] = $target->getPseudo() . ' perd ' . $damage . ' points de vie';

            if ($targetLife > 0) {
                $logs[] = $target->getPseudo() . ' a encore ' . $targetLife . ' points de vie';
            } else {
                $logs[] = $target->getPseudo() . ' est mort.';
                $win = true;
                break;
            }

            $damage = round(($target->getTotalAttack() * (mt_rand(80, 120) / 100)) - $this->getTotalDefense());
            $damage = $damage >= 0 ? $damage : 0;
            $myLife -= $damage;
            $logs[] = 'Votre adversaire riposte et vous occasionne ' . $damage . ' dommages';

            if ($myLife > 0) {
                $logs[] = 'Il vous reste ' . $myLife . ' points de vie';
            } else {
                $logs[] = 'Vous êtes mort.';
                $win = false;
                break;
            }
        }

        return [
            'win' => $win,
            'logs' => $logs
        ];
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->_id = (int)$id;
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
        $this->_confirmationKey = (int)$confirmationKey;
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
        $this->_warnings = (int)$warnings;
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

    public function setLife() {
        $this->_life = $this->_baseLife + (($this->_lvl - 1) * 10);
    }

    /**
     * @param int $attack
     */
    public function setNaturalAttack($attack) {
        $this->_naturalAttack = (int)$attack;
    }

    /**
     * @param int $defense
     */
    public function setNaturalDefense($defense) {
        $this->_naturalDefense = (int)$defense;
    }

    /**
     * @param int $dollar
     */
    public function setDollar($dollar) {
        $this->_dollar = (int)$dollar;
    }

    /**
     * @param int $T
     */
    public function setT($T) {
        $this->_T = (int)$T;
    }

    /**
     * @param int $xp
     */
    public function setXp($xp) {
        $this->_xp = (int)$xp;
        $this->setLvl($this->_xp);
    }

    /**
     * @param int $actualXp
     */
    public function setLvl($actualXp) {
        $actualLvl;
        $xpTotal = 0;

        for ($lvl = 2; $lvl <= 100; $lvl++) {
            $xp = 100 * pow($lvl + 1, 2);
            $xpTotal += $xp;

            if ($xpTotal > $actualXp) {
                $actualLvl = $lvl - 1;
                break;
            } else if ($xpTotal == $actualXp) {
                $actualLvl = $lvl;
                break;
            }
        }

        if ($actualXp > $xpTotal) {
            $actualLvl = 100;
        }

        $this->_lvl = $actualLvl;
        $this->setLife();
    }

    /**
     * @param int $remainingBattles
     */
    public function setRemainingBattles($remainingBattles) {
        if ($remainingBattles > $this->_maxRemainingBattles) {
            $this->_remainingBattles = $this->_maxRemainingBattles;
        } else {
            $this->_remainingBattles = $remainingBattles;
        }
    }

    /**
     * @return int
     */
    public function getMaxRemainingBattles(): int {
        return $this->_maxRemainingBattles;
    }

    /**
     * @param string $lastBattle
     */
    public function setLastBattle($lastBattle) {
        $this->_lastBattle = new \DateTime($lastBattle);
    }

    /**
     * @param string $adventureBeginning the date when the adventure started
     */
    public function setAdventureBeginning($adventureBeginning) {
        if (!empty($adventureBeginning)) {
            $this->_adventureBeginning = new \DateTime($adventureBeginning);
        } else {
            $this->_adventureBeginning = null;
        }
    }

    /**
     * @param int $currentAdventureId
     */
    public function setCurrentAdventureId($currentAdventureId) {
        $this->_currentAdventureId = (int) $currentAdventureId;
    }

    /**
     * @param Stuff[] $allStuff collection of Stuff objects
     */
    public function setInventory($allStuff) {
        if ($allStuff == null) {
            $this->_inventory = [];
        } else {
            foreach ($allStuff as $stuff) {
                $this->_inventory[] = $stuff;
            }
        }
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
     * @return bool
     */
    public function getConfirmationKey() {
        return $this->_confirmationKey;
    }

    /**
     * @return bool
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
     * @return bool
     */
    public function getBanned() {
        return $this->_banned;
    }

    /**
     * @return object DateTime
     */
    public function getRegistrationDate() {
        return $this->_registrationDate;
    }

    /**
     * @return bool
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
    public function getNaturalAttack() {
        return $this->_naturalAttack;
    }

    /**
     * @return int
     */
    public function getNaturalDefense() {
        return $this->_naturalDefense;
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
     * @return object DateTime
     */
    public function getLastBattle() {
        return $this->_lastBattle;
    }

    /**
     * @return object DateTime
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

    /**
     * @return Stuff[]
     */
    public function getInventory() {
        return $this->_inventory;
    }

    /**
     * @return int
     */
    public function getTotalAttack(): int {
        $stuff = $this->hasStuffEquipped('sword');

        if ($stuff != false) {
            return $this->_naturalAttack + $stuff->getStat();
        } else {
            return $this->_naturalAttack;
        }
    }

    /**
     * @return int
     */
    public function getTotalDefense(): int {
        $stuff = $this->hasStuffEquipped('shield');

        if ($stuff != false) {
            return $this->_naturalDefense + $stuff->getStat();
        } else {
            return $this->_naturalDefense;
        }
    }

    /**
     * @param mixed $newAttemptTimerStartTime string or null
     * @throws \Exception
     */
    public function setNewAttemptTimerStartTime($newAttemptTimerStartTime): void {
        if ($newAttemptTimerStartTime == null) {
            $this->_newAttemptTimerStartTime = null;
        } elseif (is_string($newAttemptTimerStartTime)) {
            $this->_newAttemptTimerStartTime = new \DateTime($newAttemptTimerStartTime);
        } else {
            throw new \Exception('TypeError : $newAttemptTimerStartTime must be string or null');
        }
    }

    /**
     * @return \DateTime or null
     */
    public function getNewAttemptTimerStartTime() {
        return $this->_newAttemptTimerStartTime;
    }

    /**
     * @return int
     */
    public function getTimeToRecoverAnAttempt(): int {
        return $this->_timeToRecoverAnAttempt;
    }

    /**
     * @return int
     */
    public function getBaseLife(): int {
        return $this->_baseLife;
    }

    /**
     * @param int $baseLife
     */
    public function setBaseLife(int $baseLife): void {
        $this->_baseLife = $baseLife;
    }
}
