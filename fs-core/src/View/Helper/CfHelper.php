<?php

namespace FsCore\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;
use Cake\View\View;
use FsCore\Utility\Utils;

class CfHelper extends Helper {

    /**
     * Let's load required helpers
     */
    public $helpers = ['Url', 'Html'];

    public function image($imagePath, $options = []) {
        return $this->Html->image($this->imageUrl($imagePath), $options);
    }

    public function imageUrl($imagePath) {
        $link = $this->Url->build(str_replace('//', '/', $imagePath), true);
        return $link;
    }

    public function upload($imagePath, $options = []) {
        if (empty($imagePath)) {
            return '';
        }
        return $this->Html->image($this->imageUrl($imagePath), $options);
    }

    public function read($assets, $replaceUrl = false) {
        $html = '';
        foreach ($assets as $file) {
            $pathSection = explode('/', $file);
            $parsedPath = [];
            for ($i = 0; $i < count($pathSection); $i++) {
                if (!empty($pathSection[$i])) {
                    $parsedPath[] = $pathSection[$i];
                }
            }
            unset($parsedPath[count($parsedPath) - 1]);
            $replace = $this->Url->build('/', true) . (!empty($parsedPath) ? implode('/', $parsedPath) . '/' : '');
            $file = WWW_ROOT . $file;
            if (file_exists($file)) {
                $fileStream = fopen($file, "r");
                $content = fread($fileStream, filesize($file));
                $content = trim($content);
                if ($replaceUrl) {
                    $content = str_replace("url( '", "url('", $content);
                    $content = str_replace('url( "', 'url("', $content);
                    $content = str_replace("url('", "url('$replace", $content);
                    $content = str_replace('url("', 'url("' . $replace, $content);
                    $content = str_replace('url(../', 'url(' . $replace . '../', $content);
                }
                $html .= $content;
            }
            fclose($fileStream);
        }
        return $html;
    }

}
