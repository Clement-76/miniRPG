<?php

namespace ClementPatigny\Model;

class Stuff {
    private $_id;
    private $_name;
    private $_type;
    private $_required_lvl;
    private $_stat;
    private $_rarity;
    private $_equipped;

    /**
     * Stuff constructor.
     * @param array $stuff
     */
    public function __construct(array $stuff) {
        $this->hydrate($stuff);
    }

    public function hydrate($stuff) {
        foreach($stuff as $key => $value) {
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
     * @param string $name
     */
    public function setName($name) {
        if (is_string($name)) {
            $this->_name = $name;
        }
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        if (is_string($type)) {
            $this->_type = $type;
        }
    }

    /**
     * @param int $required_lvl
     */
    public function setRequiredLvl($required_lvl) {
        $this->_required_lvl = (int) $required_lvl;
    }

    /**
     * @param int $stat
     */
    public function setStat($stat) {
        $this->_stat = (int) $stat;
    }

    /**
     * @param int $rarity
     */
    public function setRarity($rarity) {
        $this->_rarity = (int) $rarity;
    }

    /**
     * @param int $equipped
     */
    public function setEquipped($equipped) {
        if ($equipped == 1) {
            $this->_equipped = true;
        } else {
            $this->_equipped = false;
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
    public function getName() {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * @return int
     */
    public function getRequiredLvl() {
        return $this->_required_lvl;
    }

    /**
     * @return int
     */
    public function getStat() {
        return $this->_stat;
    }

    /**
     * @return int
     */
    public function getRarity() {
        return $this->_rarity;
    }

    /**
     * @return bool
     */
    public function getEquipped() {
        return $this->_equipped;
    }
}
