<?php
class Messages extends BaseMessages {

    public function getMessages() {
        $messages = array();
        foreach($this as $key => $message) {
            if(in_array($key, array('_rev', '_id', 'type'))) {
                continue;
            }
            $messages[$key] = $message;
        }

        return $messages;
    }
}
