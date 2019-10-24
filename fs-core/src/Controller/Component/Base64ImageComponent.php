<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use FsCore\Utility\Utils;

class Base64ImageComponent extends Component {

    public function uploadBase64($photoData, $type = 'png') {
        if (empty($photoData) || !in_array($type, ['png', 'jpg'])) {
            return '';
        }
        $photoData = explode(';', $photoData);
        if (empty($photoData) || empty($photoData[1])) {
            return '';
        }
        $photoData = explode(',', $photoData[1]);
        if (empty($photoData) || empty($photoData[1])) {
            return '';
        }
        $photoData = $photoData[1];
        $photoData = base64_decode($photoData);
        $photoPath = Configure::read('Upload.PhotoFolder');
        if (empty($photoPath)) {
            $photoPath = WWW_ROOT . 'uploads/photo/';
        }
        $dir = $photoPath;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }
        $filename = time() . '_' . uniqid() . '.' . $type;
        file_put_contents($photoPath . $filename, $photoData);
        if (strpos($photoPath, WWW_ROOT) === false) {
            $photoPath = WWW_ROOT . $photoPath;
        }
        if ($type == 'png') {
            $photo = imagecreatefrompng($photoPath . $filename);
        } elseif ($type == 'jpg') {
            $photo = imagecreatefromjpeg($photoPath . $filename);
        }
        if (!$photo) {
            return false;
        }
        return $photoPath . $filename;
    }

}
