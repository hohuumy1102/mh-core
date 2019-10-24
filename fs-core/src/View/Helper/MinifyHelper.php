<?php

namespace FsCore\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;
use Cake\View\View;

class MinifyHelper extends Helper {

    public $helpers = ['Url', 'FsCore.Cf'];
    public static $jsHtml = '';
    public static $jsBottomHtml = '';
    public static $cssHtml = '';
    public static $cssInline = false;
    public static $jsInline = false;

    const EXT_JS = 'js';
    const EXT_CSS = 'css';

    public function __construct(View $View, array $config = []) {
        parent::__construct($View, $config);
        self::$cssInline = Configure::read('Minify.cssInline');
        self::$jsInline = Configure::read('Minify.jsInline');
    }

    public function script($assets, $isBottom = false) {
        $cf = Configure::read('Minify');
        $useMinFiles = !empty($cf['min']) && $cf['min'];
        $assets = $this->_path($assets, self::EXT_JS, $useMinFiles);
        if (self::$jsInline) {
            if ($isBottom) {
                self::$jsBottomHtml .= $this->Cf->read($assets);
            } else {
                self::$jsHtml .= $this->Cf->read($assets);
            }
        } else {
            foreach ($assets as $file) {
                if ($isBottom) {
                    self::$jsBottomHtml .= '<script src="' . $this->Url->build($file, true) . '"></script>';
                } else {
                    self::$jsHtml .= '<script src="' . $this->Url->build($file, true) . '"></script>';
                }
            }
        }
    }

    public function css($assets) {
        $cf = Configure::read('Minify');
        $useMinFiles = !empty($cf['min']) && $cf['min'];
        $assets = $this->_path($assets, self::EXT_CSS, $useMinFiles);
        if (self::$cssInline) {
            self::$cssHtml .= $this->Cf->read($assets, true);
        } else {
            foreach ($assets as $file) {
                self::$cssHtml .= '<link rel="stylesheet" href="' . $this->Url->build($file, true) . '"/>';
            }
        }
    }

    protected function _path($assets, $ext, $useMinFiles) {
        if (!is_array($assets)) {
            $assets = [$assets];
        }
        $ver = (($ext == self::EXT_CSS && self::$cssInline) || ($ext == self::EXT_JS && self::$jsInline)) ? '' : '?v=' . Configure::read('Minify.ver');
        $ret = [];
        foreach ($assets as $asset) {
            if (strpos($asset, '.' . $ext) !== false) {
                $asset = str_replace('.' . $ext, '', $asset);
            }
            $file = str_replace('.min', '', $asset);
            if ($useMinFiles) {
                $fileList = glob(WWW_ROOT . $file . '.min.' . $ext);
                if (count($fileList) > 0) {
                    $file .= '.min';
                }
            }
            $file = $file . '.' . $ext;
            $ret[] = $file . $ver;
        }
        return $ret;
    }

    public function fetchCss() {
        if (self::$cssInline) {
            return '<style>' . self::$cssHtml . '</style>';
        } else {
            return self::$cssHtml;
        }
    }

    public function fetchJs() {
        if (self::$jsInline) {
            return '<script type="text/javascript">' . self::$jsHtml . '</script>';
        } else {
            return self::$jsHtml;
        }
    }

    public function fetchBottomJs() {
        if (self::$jsInline) {
            return '<script type="text/javascript">' . self::$jsBottomHtml . '</script>';
        } else {
            return self::$jsBottomHtml;
        }
    }

}
