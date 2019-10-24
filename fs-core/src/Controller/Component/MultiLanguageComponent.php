<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\I18n\I18n;
use FsCore\Controller\FsCoreController;
use FsCore\Utility\Utils;

class MultiLanguageComponent extends Component {

    public static $_currentLanguage = false;
    public static $_currentLanguageCode = false;
    protected static $_languageSessionKey = 'CurrentLanguage:Session';
    protected static $_languageSessionCode = 'CurrentLanguageCode:Session';

    public function __construct() {
        $this->autoDetectLanguage();
    }

    public function autoDetectLanguage() {
        $session = FsCoreController::$_instance->Session;
        if ($session->check(self::$_languageSessionCode) && $session->check(self::$_languageSessionKey)) {
            $languageCode = $session->read(self::$_languageSessionCode);
            self::$_currentLanguage = $session->read(self::$_languageSessionKey);
        } else {
            $languageList = Configure::read('LanguageList');
            $languageCode = Configure::read('DefaultLanguage');
            self::$_currentLanguage = strtolower($languageList[$languageCode]);
            self::$_currentLanguageCode = $languageCode;
            $session->write(self::$_languageSessionKey, self::$_currentLanguage);
            $session->write(self::$_languageSessionCode, $languageCode);
        }
        $localeList = Configure::read('LocaleList');
        I18n::locale($localeList[$languageCode]);
    }

    public function getCurrentLanguage() {
        if (!self::$_currentLanguage) {
            $session = FsCoreController::$_instance->Session;
            self::$_currentLanguage = $session->read(self::$_languageSessionKey);
        }
        if (!self::$_currentLanguage) {
            $languageList = Configure::read('LanguageList');
            self::$_currentLanguage = $languageCode[$this->getCurrentLanguageCode()];
        }
        return self::$_currentLanguage;
    }

    public function getCurrentLanguageCode() {
        if (self::$_currentLanguageCode === false) {
            $session = FsCoreController::$_instance->Session;
            self::$_currentLanguageCode = $session->read(self::$_languageSessionCode);
        }
        if (self::$_currentLanguageCode === false) {
            self::$_currentLanguageCode = Configure::read('DefaultLanguage');
        }
        return self::$_currentLanguageCode;
    }

    public function setCurrentLanguage($languageCode) {
        $languageList = Configure::read('LanguageList');
        if (!isset($languageList[$languageCode])) {
            return false;
        }
        $language = strtolower($languageList[$languageCode]);
        self::$_currentLanguage = $language;
        self::$_currentLanguageCode = $languageCode;
        $session = FsCoreController::$_instance->Session;
        $session->write(self::$_languageSessionKey, $language);
        $session->write(self::$_languageSessionCode, $languageCode);

        $localeList = Configure::read('LocaleList');
        I18n::locale($localeList[$languageCode]);

        return true;
    }

}
