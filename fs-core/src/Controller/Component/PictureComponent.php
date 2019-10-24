<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use FsCore\Utility\Utils;

class PictureComponent extends Component {

    public function getPhotoPath($photoPath, $targetWidth) {
        if (empty($photoPath)) {
            return '';
        }
        if (strpos($photoPath, WWW_ROOT) === false) {
            $photoPath = WWW_ROOT . $photoPath;
        }
        if (!file_exists($photoPath)) {
            return '';
        }
        $rawPhoto = $photoPath;
        $photoName = $this->_getPhotoName($photoPath);

        $cacheFolder = Configure::read('Upload.CacheFolder');
        if (!file_exists($cacheFolder)) {
            $oldUmask = umask(0);
            mkdir($cacheFolder, 0777);
            umask($oldUmask);
        }

        // Check xem có cache hình chưa
        $ret = glob($cacheFolder . $photoName . '_' . $targetWidth . '*.*');
        if (!empty($ret)) {
            $photoPath = array_shift($ret);
        } else {
            $size = @getimagesize($rawPhoto);
            $originalWidth = $size[0];
            $originalHeight = $size[1];
            if ($originalWidth <= $targetWidth) {
                $photoPath = $rawPhoto;
            } else {
                $targetHeigth = $originalHeight * $targetWidth / $originalWidth;
                Utils::useComponents($this, ['FsCore.ImageResize']);
                $photoPath = $this->ImageResize->resizeImage($rawPhoto, $targetWidth, $targetHeigth, false, $photoName, null, $cacheFolder);
            }
        }
        $photoPath = DS . str_replace(WWW_ROOT, '', $photoPath);
        return $photoPath;
    }

    private function _getPhotoName(&$photoPath) {
        $pathSections = explode('/', $photoPath);
        for ($i = count($pathSections) - 1; $i >= 0; $i--) {
            $photoName = trim($pathSections[$i]);
            if (!empty($photoName)) {
                break;
            }
        }
        $extension = pathinfo($photoName, PATHINFO_EXTENSION);
        $photoName = substr($photoName, 0, strlen($photoName) - (strlen($extension) + 1));
        $photoPath = substr($photoPath, 0, strlen($photoPath) - (strlen($extension) + 1));
        return $photoName;
    }

}
