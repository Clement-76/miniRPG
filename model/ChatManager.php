<?php

namespace ClementPatigny\Model;

class ChatManager extends Manager {

    /**
     * @param int $lastIdRetrieved = 0 if nothing has been retrieved
     * @return chatMessage[] array of chatMessage objects
     * @throws \Exception
     */
    public function getChatMessages($lastIdRetrieved) {
        $db = $this->connectDb();

        try {
            // get the last messages in the db
            if ($lastIdRetrieved == 0) {
                $q = $db->query(
                    'SELECT messages.id,
                    messages.content,
                    messages.creation_date,
                    users.pseudo AS author,
                    users.id AS author_id
                    FROM minirpg_chat_messages AS messages
                    INNER JOIN minirpg_users AS users
                    ON messages.user_id = users.id
                    ORDER BY messages.id DESC
                    LIMIT 0, 20'
                );
            } else {
                $q = $db->prepare(
                    'SELECT messages.id,
                    messages.content,
                    messages.creation_date,
                    users.pseudo AS author,
                    users.id AS author_id
                    FROM minirpg_chat_messages AS messages
                    INNER JOIN minirpg_users AS users
                    ON messages.user_id = users.id
                    WHERE messages.id > :lastIdRetrieved
                    AND users.id != :authorId
                    ORDER BY messages.id DESC
                    LIMIT 0, 20'
                );

                $q->bindValue(':lastIdRetrieved', $lastIdRetrieved, \PDO::PARAM_INT);
                $q->bindValue(':authorId', $_SESSION['user']->getId(), \PDO::PARAM_INT);
                $q->execute();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $messages = [];

        while ($message = $q->fetch()) {
            if ($message['author_id'] == $_SESSION['user']->getId()) {
                $owner = true;
            } else {
                $owner = false;
            }

            $messageFeatures = [
                'id' => $message['id'],
                'content' => $message['content'],
                'author' => $message['author'],
                'authorId' => $message['author_id'],
                'creationDate' => $message['creation_date'],
                'isOwner' => $owner
            ];

            $messages[] = new chatMessage($messageFeatures);
        }

        return $messages;
    }

    /**
     * insert a new message into the db and return the message
     * @param $message
     * @return chatMessage
     * @throws \Exception
     */
    public function insertMessage($message) {
        $db = $this->connectDb();

        try {
            $q = $db->prepare(
                'INSERT INTO minirpg_chat_messages(content, user_id) 
                 VALUES(:content, :authorId)'
            );

            $q->bindValue(':content', $message['content'], \PDO::PARAM_STR);
            $q->bindValue(':authorId', $message['authorId'], \PDO::PARAM_INT);
            $q->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        if ($message['authorId'] == $_SESSION['user']->getId()) {
            $owner = true;
        } else {
            $owner = false;
        }

        $messageFeatures = [
            'id' => $db->lastInsertId(),
            'content' => $message['content'],
            'author' => $_SESSION['user']->getPseudo(),
            'authorId' => $message['authorId'],
            'creationDate' => time(),
            'isOwner' => $owner
        ];

        return new chatMessage($messageFeatures);
    }
}
