<?php

namespace ClementPatigny\Model;

class ChatManager extends Manager {

    /**
     * @return chatMessage[] array of chatMessage objects
     * @throws \Exception
     */
    public function getChatMessages() {
        $db = $this->connectDb();

        try {
            $q = $db->query(
                'SELECT messages.id,
             messages.content,
             messages.creation_date,
             users.pseudo AS author,
             users.id AS author_id
             FROM minirpg_chat_messages AS messages
             INNER JOIN minirpg_users AS users
             ON messages.user_id = users.id'
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $messages = [];

        while ($message = $q->fetch()) {
            $messageFeatures = [
                'id' => $message['id'],
                'content' => $message['content'],
                'author' => $message['author'],
                'authorId' => $message['author_id'],
                'creationDate' => $message['creation_date'],
            ];

            $messages[] = new chatMessage($messageFeatures);
        }

        return $messages;
    }
}
