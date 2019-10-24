<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use FsCore\Utility\Utils;

class UserRegisterComponent extends Component {

    /**
     * initialize
     * @param  array  $config config
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);
        Utils::useTables($this, ['FsCore.Users']);
    }

    /**
     * register user from facebook
     * @param  array $fbUserIds Facebook user id
     * @param  string $fbAccessToken access token
     * @return array user entities
     */
    public function registerUserFromFacebook($fbUserIds, $fbAccessToken) {
        if (empty($fbUserIds) || empty($fbAccessToken)) {
            return [];
        }

        Utils::useComponents($this, ['FsCore.Facebook']);

        $this->Facebook->setAccessToken($fbAccessToken);
        $fbUsers = $this->Facebook->getProfiles($fbUserIds);

        $userList = [];
        if ($fbUsers['status'] == 0) {
            foreach ($fbUsers['data'] as $fbUserId => $fbUser) {
                $avatarImg = file_get_contents($fbUser['picture']['url']);
                $photoPath = Configure::read('Upload.Avatars') . 'avatar_' . $fbUser['id'] . '.jpg';
                $dir = dirname($photoPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($photoPath, $avatarImg);
                $fbUser['avatar'] = str_replace(WWW_ROOT, '', $photoPath);
                $userList[$fbUser['id']] = $fbUser;
            }

            return $this->Users->registerUserFromFacebook($userList);
        }

        return [];
    }

}
