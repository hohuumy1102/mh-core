<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace FsCore\Controller;

use FsCore\Utility\Utils;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;

class FsCoreController extends Controller {

    public static $_globalObjects = [
        'components' => [],
        'tables' => []
    ];
    public $helpers = ['FsCore.Cf', 'FsCore.Minify'];
    public static $_instance = null;
    public $Session = null;
    protected $seoTarget = false;
    protected $seoId = false;
    protected $seoKeyword = false;
    protected $pageTitle = false;
    protected $pageDesc = false;
    protected $pageImage = false;

    /*
     * Ajax response
     * - status: 0 (no error), 1 (error)...
     * - message: response description
     */
    protected $ajaxResponse = [
        'status' => 0,
        'message' => ''
    ];

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

        self::$_instance = $this;
        $this->Session = $this->request->session();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginAction' => '/',
            'storage' => 'Session'
        ]);
        Utils::useComponents($this, ['FsCore.MultiLanguage']);
        $this->set('authUser', $this->Auth->user());
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event) {
        if (!array_key_exists('_serialize', $this->viewVars) &&
                in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
        Utils::useComponents($this, ['FsCore.MultiLanguage']);
        $languaCode = $this->MultiLanguage->getCurrentLanguageCode();
        $this->set('currentLangCode', $languaCode);
        $this->set('currentAction', $this->request->params['action']);

        if ($this->seoId !== false && !empty($this->seoTarget)) {
            Utils::useTables($this, ['FsCore.Seos']);
            $seo = $this->Seos->find('all', [
                        'conditions' => [
                            'target_id' => $this->seoId,
                            'target_type' => $this->seoTarget,
                            'language' => $languaCode,
                        ],
                    ])->first();
            if (!empty($seo)) {
                $seo->content = json_decode($seo->content, true);
                $this->seoKeyword = !empty($seo->content['keyword']) ? implode(', ', $seo->content['keyword']) : '';
                $this->pageDesc = !empty($seo->content['description']) ? $seo->content['description'] : '';
                $this->pageImage = !empty($seo->content['thumbnail']) ? $seo->content['thumbnail'] : '';
            }
        }
        $this->set('pageDesc', $this->pageDesc);
        $this->set('seoKeyword', $this->seoKeyword);
        $this->set('pageTitle', $this->pageTitle);
        $this->set('pageImage', $this->pageImage);
    }

    /**
     * sendAjax send ajax
     * @param  string $status       status
     * @param  string $errorMessage error message
     * @return null
     */
    protected function sendAjax($status = null, $errorMessage = '') {
        $this->viewBuilder()->layout(false);
        $this->autoRender = false;

        if ($status !== null) {
            $this->ajaxResponse['status'] = $status;
            $this->ajaxResponse['message'] = $errorMessage;
        }

        $this->response->body(json_encode($this->ajaxResponse));
        $this->response->type('json');
        return null;
    }

    protected function _setLanguage($languageCode = LANGUAGE_VIETNAMESE) {
        Utils::useComponents($this, ['FsCore.MultiLanguage']);
        $this->MultiLanguage->setCurrentLanguage($languageCode);
    }

    protected function sendAsyncResponse() {
        $this->viewBuilder()->layout(false);
        $this->autoRender = false;
        $this->ajaxResponse = $this->AsyncResponse->getData();
        $this->response->body(json_encode($this->ajaxResponse));
        $this->response->type('json');
        return null;
    }

}
