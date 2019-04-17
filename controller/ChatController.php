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

            if (isset($_POST['lastIdRetrieved'])) {
                try {
                    $chatManager = new ChatManager();
                    $messages = $chatManager->getChatMessages($_POST['lastIdRetrieved']);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                // if there are new messages
                if (!empty($messages)) {
                    // the id of the last element retrieved
                    $lastIdRetrieved = $messages[0]->getId();

                    // reverse the messages to have them from the oldest to the newest
                    $messages = array_reverse($messages);

                    echo json_encode([
                        'status' => 'success',
                        'messages' => $messages,
                        'lastIdRetrieved' => $lastIdRetrieved
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'success',
                        'messages' => 'nothing',
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'lastIdRetrieved is missing'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'not connected'
            ]);
        }
    }

    /**
     * check the message and if there are no errors
     * instantiate the ChatManager to insert the message
     * @throws \Exception
     */
    public function addMessage() {
        if (isset($_SESSION['user'])) {
            if (isset($_POST['message']) && !empty($_POST['message'])) {
                $newMessage = htmlspecialchars($_POST['message']);
                // replaces multiple line breaks by one line break
                $newMessage = preg_replace("#(\n|\r)+#", "\n", $newMessage);
                $newMessage = preg_replace("#(.+)(\n)*#", "<p>$1</p>", $newMessage);

                // bold markdown
                $newMessage = preg_replace("#\*(.+)\*#", "<i>$1</i>", $newMessage);

                // underline markdown
                $newMessage = preg_replace("#_(.+)_#", "<span class='underline'>$1</span>", $newMessage);

                try {
                    $chatManager = new ChatManager();
                    $newMessage = $chatManager->insertMessage([
                        'authorId' => $_SESSION['user']->getId(),
                        'content' => $newMessage,
                    ]);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                echo json_encode([
                    'status' => 'success',
                    'newMessage' => $newMessage
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No message received'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'You\'re not connected !'
            ]);
        }
    }
}
