<?php

namespace ClementPatigny\Model;

class ChatManager extends Manager {

    /**
     * @return chatMessage[] array of chatMessage objects
     * @throws \Exception
     */
    public function getChatMessage() {
        $db = $this->connectDb();

        try {
            $q = $db->query(
                'SELECT messages.id,
             messages.content,
             messages.creation_date,
             users.pseudo,
             users.id 
             FROM minirpg_chat_messages AS messages
             INNER JOIN minirpg_users AS users
             ON messages.user_id = users.id'
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $messages = [];

        while ($message = $q->fetch()) {
            var_dump($message);
            die();

            $messageFeatures = [
                'id' => $message['content'],
                'content' => $message['title'],
                'author' => $message['id'],
                'authorId' => $message['creation_date'],
                'creationDate' => $message['category'],
            ];

            $messages[] = new chatMessage($messageFeatures);
        }

        return $messages;
    }
}
