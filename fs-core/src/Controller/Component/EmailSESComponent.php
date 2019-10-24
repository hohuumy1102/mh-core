<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Mailer\Email;
use Cake\Log\Log;

class EmailSESComponent extends Component {

    public function initialize(array $config) {
        parent::initialize($config);
    }

    /**
     * Preload configured values
     *
     */
    protected function _beforeSendEmail() {
        $this->Email = new Email('default');
        $this->Email->emailFormat('both');
        $this->Email->addHeaders(array('X-Mailer' => TITLE_X_MAILER));
    }

    public function reset() {
        $this->Email->reset();
    }

    private function send() {
        try {
            $this->Email->send();
            $this->logResult();
            $this->reset();

            return true;
        } catch (\Exception $e) {
            $this->logResult($e);
            return false;
        }
    }

    private function logResult($e = NULL) {
        if (is_array($this->Email->from())) {
            $from = implode(",", $this->Email->from());
        } else {
            $from = $this->Email->from();
        }
        if (is_array($this->Email->to())) {
            $to = implode(",", $this->Email->to());
        } else {
            $to = $this->Email->$to();
        }
        $attachments = $this->Email->attachments();

        $message = PHP_EOL . "-------------------------" . PHP_EOL;
        $message .= "Send From : '" . $from . "' " . ($e instanceof Exception ? 'unsuccessfully' : 'successfully') . PHP_EOL;
        $message .= "Send email to : '" . $to . "' " . ($e instanceof Exception ? 'unsuccessfully' : 'successfully') . PHP_EOL;
        $message .= "Subject email : " . $this->Email->subject() . PHP_EOL;
        $message .= "Attachment list : " . PHP_EOL;
        foreach ($attachments as $name => $file) {
            $message .= $file['file'] . PHP_EOL;
        }
        if ($e instanceof Exception) {
            $message .= "The error is : " . $e->getMessage() . PHP_EOL;
        }
        $message .= "-------------------------" . PHP_EOL;

        Log::info($message);
    }

}
