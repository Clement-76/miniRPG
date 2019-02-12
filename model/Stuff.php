<?php

namespace ClementPatigny\Model;

class Stuff {
    private $_name;
    private $_type;
    private $required_lvl;
    private $_stat;
    private $_rarity;

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
        $this->required_lvl = (int) $required_lvl;
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
     * @return mixed
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * @return mixed
     */
    public function getRequiredLvl() {
        return $this->required_lvl;
    }

    /**
     * @return mixed
     */
    public function getStat() {
        return $this->_stat;
    }

    /**
     * @return mixed
     */
    public function getRarity() {
        return $this->_rarity;
    }
}
