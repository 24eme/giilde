<?php

class MessagesClient extends acCouchdbClient {
    public $messages = null;

    public static function getInstance() {

        return acCouchdbManager::getClient("Messages");
    }

    public function retrieveMessages() {
        if (!$this->messages) {

            $this->messages = $this->find('MESSAGES');
        }

        if (!$this->messages) {
            $this->messages = new Messages();
        }

      return $this->messages;
    }

    public function getMessage($id) {
        if(!$this->retrieveMessages()->exist($id)) {
            return null;
        }

        return $this->retrieveMessages()->get($id);
    }
}
