<?php

namespace FsCore\Controller;

use FsCore\Utility\Utils;
use Cake\Controller\Controller;
use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Network\Session;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

class FsBackendController extends Controller {

    public $helpers = ['FsCore.Cf', 'FsCore.Minify'];
    public $components = [
        'Auth'
    ];
    protected $model = null;
    protected $stopAjax = false;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize() {
        parent::initialize();
        $this->Session = $this->request->session();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth');
        $this->configureAuth();
        $this->viewBuilder()->layout('FsCore.backend');
        Utils::useComponents($this, ['FsCore.AsyncResponse']);
    }

    public function beforeFilter(Event $event) {
        Utils::useComponents($this, ['FsCore.MultiLanguage', 'Backend.Navigation']);
        $this->set('currentLangCode', $this->MultiLanguage->getCurrentLanguageCode());
        $menuList = $this->Navigation->getNavList($this->request);
        $controller = !empty($this->request->params['controller']) ? $this->request->params['controller'] : false;
        $action = !empty($this->request->params['action']) ? $this->request->params['action'] : false;
        $this->set('menuList', $menuList);
        $this->set('controller', $controller);
        $this->set('action', $action);

        $ret = $this->checkPermission();
        if (!$ret) {
            return $this->showInvalidAction();
        }

        $this->initBreadcrumb();
    }

    protected function checkIPInWhiteList() {
        Utils::useComponents($this, ['FsCore.AdminCommon']);
        $ipList = $this->AdminCommon->getAllIPWhiteList();
        if (empty($ipList)) {
            $ip = Utils::getUserIP();
            $this->AdminCommon->addIPWhiteList($ip);
        } elseif (!Utils::checkIPInWhiteList($ipList)) {
            throw new Exception('Page not found', 404);
        }
    }

    /**
     * configure authentication
     *
     * @return void
     */
    protected function configureAuth() {
        $this->Auth->config('loginRedirect', [
            'controller' => 'AdminDashboard',
            'action' => 'index'
        ]);
        $this->Auth->config('loginAction', [
            'controller' => 'AdminUsers',
            'action' => 'login'
        ]);
        $this->Auth->config('logoutRedirect', [
            'controller' => 'AdminUsers',
            'action' => 'login'
        ]);
        $this->Auth->config('storage', 'FsCore.BackendSession');
        $this->Auth->config('authenticate', [
            AuthComponent::ALL => [
                'fields' => [
                    'username' => 'email',
                    'password' => 'password'
                ],
                'userModel' => 'FsCore.AdminUsers'
            ],
            'Form' => [
                'passwordHasher' => [
                    'className' => 'Default'
                ]
            ]
        ]);
    }

    protected function checkPermission() {
        Utils::useComponents($this, ['FsCore.AdminCommon', 'Auth']);
        $controller = $this->request->params['controller'];
        $action = $this->request->params['action'];
        $defaultActions = Configure::read('DefaultAction');
        $controllerList = Configure::read('ControllerList');
        $requirePermission = false;
        $hasPermission = true;
        if (!empty($defaultActions[$action]) && !empty($controllerList[$controller])) {
            $requirePermission = true;
        }
        $customActions = Configure::read('CustomAction');
        if ($requirePermission) {
            if (isset($customActions[$controller][$action]) && $customActions[$controller][$action] === 0) {
                $requirePermission = false;
            }
        } elseif (!empty($customActions[$controller]) && !empty($customActions[$controller][$action])) {
            $requirePermission = true;
        }
        if ($requirePermission) {
            $hasPermission = $this->AdminCommon->checkPermission($controller, $action);
        }
        return $hasPermission;
    }

    protected function showInvalidAction() {
        $error = __('You do not have permission for this feature.');
        if ($this->request->is('ajax')) {
            $this->AsyncResponse->run('showAlert("' . $error . '");');
            $this->stopAjax = true;
            $this->sendAsyncResponse();
        } else {
            $this->Flash->error($error, ['plugin' => 'FsCore']);
            return $this->redirect(['controller' => 'AdminDashboard']);
        }
    }

    protected function sendAsyncResponse() {
        $this->viewBuilder()->layout(false);
        $this->autoRender = false;
        $this->ajaxResponse = $this->AsyncResponse->getData();
        $this->response->body(json_encode($this->ajaxResponse));
        $this->response->type('json');
        return null;
    }

    protected function initBreadcrumb() {
        $controller = $this->request->params['controller'];
        $action = $this->request->params['action'];
        $title = __(Inflector::humanize(Inflector::underscore($controller)));

        $breadcrumb = array(
            array(
                'title' => __('Admin Panel'),
                'href' => '#',
                'class' => 'fake',
            ),
            array(
                'title' => $title,
                'href' => Router::url(['controller' => $controller, 'action' => 'index']),
                'class' => '',
            ),
        );
        if ($action != 'index') {
            $actionTitle = Inflector::humanize($action);
            $breadcrumb[] = array(
                'title' => $actionTitle,
                'href' => Router::url(['controller' => $controller, 'action' => $action]),
                'class' => '',
            );
            $title = __($actionTitle) . ' ' . __($title);
        }
        $this->set('breadcrumb', $breadcrumb);
        $this->set('headerTitle', $title);
    }

}
