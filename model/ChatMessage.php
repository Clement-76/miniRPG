<?php

namespace ClementPatigny\Model;

class ChatMessage implements \JsonSerializable {

    private $_id;
    private $_content;
    private $_author;
    private $_authorId;
    private $_creationDate;
    private $_isOwner;

    /**
     * chatMessage constructor.
     * @param array $messageFeatures
     */
    public function __construct(array $messageFeatures) {
        $this->hydrate($messageFeatures);
    }

    public function hydrate($messageFeatures) {
        foreach($messageFeatures as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'content' => $this->getContent(),
            'author' => $this->getAuthor(),
            'authorId' => $this->getAuthorId(),
            'creationDate' => $this->getCreationDate(),
            'isOwner' => $this->getIsOwner()
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
    public function getContent(): string {
        return $this->_content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content) {
        $this->_content = $content;
    }

    /**
     * @return string
     */
    public function getAuthor(): string {
        return $this->_author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author) {
        $this->_author = $author;
    }

    /**
     * @return int
     */
    public function getAuthorId(): int {
        return $this->_authorId;
    }

    /**
     * @param int $authorId
     */
    public function setAuthorId(int $authorId) {
        $this->_authorId = $authorId;
    }

    /**
     * @return object
     */
    public function getCreationDate(): \DateTime {
        return $this->_creationDate;
    }

    /**
     * @param string $creationDate
     */
    public function setCreationDate(string $creationDate) {
        $this->_creationDate = new \DateTime($creationDate);
    }

    /**
     * @return bool
     */
    public function getIsOwner(): bool {
        return $this->_isOwner;
    }

    /**
     * @param bool $isOwner
     */
    public function setIsOwner(bool $isOwner) {
        $this->_isOwner = $isOwner;
    }
}
