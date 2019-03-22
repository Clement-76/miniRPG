<?php

namespace ClementPatigny\Model;

class Adventure implements \JsonSerializable {

    private $_id;
    private $_name;
    private $_duration;
    private $_rewards;
    private $_requiredLvl;
    private $_dollars;
    private $_xp;

    /**
     * Adventure constructor.
     * @param array $adventureFeatures
     */
    public function __construct(array $adventureFeatures) {
        $this->hydrate($adventureFeatures);
    }


    public function hydrate($adventureFeatures) {
        foreach ($adventureFeatures as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    public function jsonSerialize() {
        return [
            'id' => $this->_id,
            'name' => $this->_name,
            'duration' => $this->_duration,
            'rewards' => $this->_rewards,
            'requiredLvl' => $this->_requiredLvl
        ];
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->_id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id) {
        $this->_id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) {
        $this->_name = $name;
    }

    /**
     * @return int
     */
    public function getDuration(): int {
        return $this->_duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration) {
        $this->_duration = $duration;
    }

    /**
     * @return string
     */
    public function getRewards(): string {
        return $this->_rewards;
    }

    /**
     * @param string $rewards
     */
    public function setRewards(string $rewards) {
        $this->_rewards = $rewards;
    }

    /**
     * @return int
     */
    public function getRequiredLvl(): int {
        return $this->_requiredLvl;
    }

    /**
     * @param int $requiredLvl
     */
    public function setRequiredLvl(int $requiredLvl) {
        $this->_requiredLvl = $requiredLvl;
    }

    /**
     * @return int
     */
    public function getDollars(): int {
        return $this->_dollars;
    }

    /**
     * @param int $dollars
     */
    public function setDollars(int $dollars) {
        $this->_dollars = $dollars;
    }

    /**
     * @return int
     */
    public function getXp(): int {
        return $this->_xp;
    }

    /**
     * @param int $xp
     */
    public function setXp(int $xp) {
        $this->_xp = $xp;
    }
}
