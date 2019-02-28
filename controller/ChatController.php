<?php

namespace ClementPatigny\Controller;

use ClementPatigny\Model\ChatManager;

class ChatController extends AppController {

    /**
     * return an array of ChatMessage objects in JSON format for ajax requests
     * @throws \Exception
     */
    public function getJSONChatMessages() {
        if (isset($_SESSION['user'])) {
            try {
                $chatManager = new ChatManager();
                $messages = $chatManager->getChatMessages();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            echo json_encode(['success', $messages]);
        } else {
            echo json_encode(['error', 'You\'re not connected !']);
        }
    }

    /**
     * check the message and if there are no errors
     * instantiate the ChatManager to insert the message
     * @throws \Exception
     */
    public function createMessage() {
        if (isset($_SESSION['user'])) {
            if (isset($_POST['message']) && !empty($_POST['message'])) {
                try {
                    $chatManager = new ChatManager();
                    $newMessage = $chatManager->insertMessage([
                        'authorId' => $_SESSION['user']->getId(),
                        'content' => $_POST['message'],
                    ]);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                echo json_encode(['success', $newMessage]);
            } else {
                echo json_encode(['error', 'No message received']);
            }
        } else {
            echo json_encode(['error', 'You\'re not connected !']);
        }
    }
}
