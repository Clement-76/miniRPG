<?php

namespace ClementPatigny\Controller;

use ClementPatigny\Model\ChatManager;
use Twig\Error\Error;

class ChatController extends AppController {

    /**
     * return an array of ChatMessage objects in JSON format for ajax requests
     * @throws Error
     */
    public function getJSONChatMessages() {
        if (isset($_SESSION['user'])) {
            try {
                $chatManager = new ChatManager();
                $messages = $chatManager->getChatMessages();
            } catch (\Exception $e) {
                throw new Error($e->getMessage());
            }

            echo json_encode(['success', $messages]);
        } else {
            echo json_encode(['error', 'You\'re not connected !']);
        }
    }
}
