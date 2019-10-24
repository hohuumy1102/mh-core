<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use FsCore\Model\Entity\AdminPermission;
use FsCore\Utility\Utils;

class AdminCommonComponent extends Component {

    public function getAllIPWhiteList() {
        Utils::useTables($this, ['FsCore.AdminWhitelistIps']);

        $result = $this->AdminWhitelistIps->find('all', [
            'fields' => [
                'AdminWhitelistIps.ip'
            ]
        ]);

        if (!empty($result)) {
            return $result->toArray();
        }
        return [];
    }

    public function addIPWhiteList($ip) {
        if (empty($ip)) {
            return false;
        }
        Utils::useTables($this, ['FsCore.AdminWhitelistIps']);
        $entity = $this->AdminWhitelistIps->newEntity([
            'ip' => $ip
        ]);
        return $this->AdminWhitelistIps->save($entity);
    }

    public function checkSuperPermission() {
        Utils::useComponents($this, ['Auth']);
        $admin = $this->Auth->user();
        if (!empty($admin) && ($admin['id'] == 1 || $admin['admin_role_id'] == 1)) {
            return true;
        }
        return false;
    }

    public function checkPermission($controller, $action) {
        if ($this->checkSuperPermission()) {
            return true;
        }
        if (empty($controller)) {
            return false;
        }
        Utils::useComponents($this, ['Auth']);
        $admin = $this->Auth->user();
        Utils::useTables($this, ['AdminPermissions']);

        $permission = $this->AdminPermissions->find('all', [
                    'conditions' => [
                        'AdminPermissions.target_id' => $admin['id'],
                        'AdminPermissions.target_type' => AdminPermission::TARGET_TYPE_ADMIN,
                    ]
                ])->first();
        if (empty($permission)) {
            $permission = $this->AdminPermissions->find('all', [
                        'conditions' => [
                            'AdminPermissions.target_id' => $admin['admin_role_id'],
                            'AdminPermissions.target_type' => AdminPermission::TARGET_TYPE_ROLE,
                        ]
                    ])->first();
        }
        if (!empty($permission->content)) {
            $permission = json_decode($permission->content, true);
        } else {
            $permission = [];
        }
        // Check Permission
        if (!empty($action)) {
            return !empty($permission[$controller][$action]);
        }
        return !empty($permission[$controller]);
    }

    public function getAllActions() {
        $controllerList = Configure::read('ControllerList');
        $defaultActions = Configure::read('DefaultAction');
        $customActions = Configure::read('CustomAction');
        $actionList = [];
        foreach ($controllerList as $controller => $val) {
            $actionList[$controller] = $defaultActions;
            if (!empty($customActions[$controller])) {
                foreach ($customActions[$controller] as $action => $enable) {
                    if ($enable) {
                        $actionList[$controller][$action] = 1;
                    }
                }
            }
        }
        return $actionList;
    }

}
