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
}
