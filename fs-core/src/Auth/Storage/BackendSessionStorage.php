<?php

namespace FsCore\Auth\Storage;

use Cake\Auth\Storage\SessionStorage;

class BackendSessionStorage extends SessionStorage {

    protected $_defaultConfig = [
        'key' => 'Auth.Backend.User',
        'redirect' => 'Auth.Backend.redirect'
    ];

    public function write($user) {
        $this->_user = $user;

        if (session_status() !== \PHP_SESSION_ACTIVE) {
            $this->_session->renew();
        }
        $this->_session->write($this->_config['key'], $user);
    }

    public function delete() {
        $this->_user = false;

        $this->_session->delete($this->_config['key']);
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            $this->_session->renew();
        }
    }

}
