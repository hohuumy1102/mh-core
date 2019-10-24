<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use FsCore\Utility\Utils;

class UploadFileFromUrl {

    protected $fieldName = false;
    protected $url = false;

    public function __construct($fieldName = null, $url) {
        $this->url = $url;

        $pathinfo = pathinfo($url);
        $extension = substr($url, strrpos($url, '.') + 1, strrpos($url, '?') - strrpos($url, '.') - 1);

        $this->fieldName = $pathinfo['filename'] . '.' . $extension;
    }

    public function save($path) {

        $upload = file_put_contents($path, file_get_contents($this->url));

        if ($upload) {
            return true;
        }

        return false;
    }

    public function getName() {
        return $this->fieldName;
    }

    public function getSize() {
        $img = get_headers($this->url, 1);
        return $img["Content-Length"];
    }

}

/**
 * Handle file uploads via XMLHttpRequest
 */
class UploadFileXhr {

    protected $fieldName = false;

    public function __construct($fieldName) {
        $this->fieldName = $fieldName;
    }

    /**
     * Save the file to the specified path
     * @return boolean true on success
     */
    public function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize()) {
            return false;
        }

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    public function getName() {
        return $_GET[$this->fieldName];
    }

    public function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])) {
            return (int) $_SERVER["CONTENT_LENGTH"];
        } else {
            throw new Exception(__('Getting content length is not supported.'));
        }
    }

}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class UploadFileForm {

    protected $fieldName = false;

    public function __construct($fieldName) {
        $this->fieldName = $fieldName;
    }

    /**
     * Save the file to the specified path
     * @return boolean true on success
     */
    public function save($path) {
        if (!move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $path)) {
            return false;
        }
        return true;
    }

    public function getName() {
        return $_FILES[$this->fieldName]['name'];
    }

    public function getSize() {
        return $_FILES[$this->fieldName]['size'];
    }

}

/**
 * Handle uploaded file from user
 *
 * @author longpt
 *
 */
class UploadComponent extends Component {

    private $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'wmv');
    private $sizeLimit = 104857600;
    private $file = null;
    private $uploadedFileName = '';
    private $createDestination = true;
    private $source = null;
    private $destination = null;
    private $replaceOldFile = false;

    public function setAllowExtensions(array $allowedExtensions = array()) {
        // Only allow these specified extensions
        if (!empty($allowedExtensions)) {
            $allowedExtensions = array_map("strtolower", $allowedExtensions);
            $this->allowedExtensions = $allowedExtensions;
        }
    }

    public function setSizeLimit($sizeLimit = 52428800) {
        // File size limit
        $this->sizeLimit = $sizeLimit;
    }

    public function setup($fieldName, $url = null) {
        if (!empty($_FILES[$fieldName])) {
            $this->file = new UploadFileForm($fieldName);
        } elseif (!empty($_GET[$fieldName])) {
            // Ajax upload
            $this->file = new UploadFileXhr($fieldName);
        } elseif (!empty($url)) {
            $this->file = new UploadFileFromUrl($fieldName, $url);
        } else {
            $this->file = false;
        }
    }

    /**
     * set the source path for the uploaded file
     * @param string $path
     * @return UploadComponent
     */
    public function setSource($path) {
        // add trailing slash if there isn't one
        $last_char = substr($path, -1);
        if ($last_char !== '/')
            $path .= '/';

        $this->source = $path;

        return $this;
    }

    /**
     * set the destination path for the uploaded file
     * @param string $path
     * @return UploadComponent
     */
    public function setDestination($path) {
        // add trailing slash if there isn't one
        $last_char = substr($path, -1);
        if ($last_char !== '/') {
            $path .= '/';
        }

        $this->destination = $path;

        return $this;
    }

    public function setUploadedFileName($filename) {
        $this->uploadedFileName = $filename;
    }

    /**
     * setter for the create destination flag.
     * can be turned off if an error on missing destination is required
     * @param boolean $flag
     * @return UploadComponent
     */
    public function createDestination($flag = true) {
        $this->createDestination = $flag;
        return $this;
    }

    private function checkServerSettings() {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }
    }

    private function toBytes($str) {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    public function checkFileSize() {
        return ($this->file->getSize() != 0);
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    public function handleUpload($fieldName, $url = null, $replaceOldFile = false) {
        $this->setup($fieldName, $url);

        if (!$this->file) {
            return array('error' => __('No files were uploaded.'));
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return array('error' => __('File is empty'));
        }

        if ($size > $this->sizeLimit) {
            return array('error' => __('File is too large'));
        }

        $pathinfo = pathinfo($this->file->getName());
        $filename = strtolower($pathinfo['filename']);
        $ext = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => sprintf(__('File has an invalid extension, it should be one of %s'), $these));
        }

        // parse out class params to make the final destination string
        $destination = $this->destination . $filename . '.' . $ext;

        // create the destination unless otherwise set
        if ($this->createDestination) {
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        } else {
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                throw new Exception(__('Destination path does not exist'));
            }
        }

        if (!$replaceOldFile) {
            $filename = uniqid();
        }

        if ($this->file->save($this->destination . $filename . '.' . $ext)) {
            $this->setUploadedFileName($filename . '.' . $ext);
            return array('success' => true, 'file' => $this->uploadedFileName);
        } else {
            return array('error' => __('Could not save uploaded file. The upload was cancelled, or server error encountered.'));
        }
    }

    public function moveFile() {
        $sourceFile = $this->source . $this->uploadedFileName;
        if (file_exists($sourceFile)) {
            $pathinfo = pathinfo($sourceFile);
            $filename = md5(uniqid());
            $ext = $pathinfo['extension'];

            // create the destination unless otherwise set
            $destination = $this->destination . $filename . '.' . $ext;
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            /// don't overwrite previous files that were uploaded
            while (file_exists($this->destination . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }

            if (rename($sourceFile, $this->destination . $filename . '.' . $ext)) {
                return array('success' => true, 'file' => $filename . '.' . $ext);
            } else {
                return array('error' => __('Could not move uploaded file.'));
            }
        } else {
            return array('error' => __('File does not exist.'));
        }
    }

    /**
     * Return the uploaded file
     *
     */
    public function getUploadedFile() {
        return $this->uploadedFileName;
    }

}
