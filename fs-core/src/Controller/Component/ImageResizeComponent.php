<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use FsCore\Utility\Utils;

class ImageResizeComponent extends Component {

    var $mimes;

    public function imageCreateTrueColor($width, $height, $fillBg = FALSE) {
        $img = imagecreatetruecolor($width, $height);
        if (is_resource($img)) {
            if ($fillBg) {
                $white = imagecolorallocate($img, 255, 255, 255);
                imagefill($img, 0, 0, $white);
            } else {
                if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
                    imagealphablending($img, FALSE);
                    imagesavealpha($img, TRUE);
                }
            }
            return $img;
        }
        return FALSE;
    }

    public function _getMimeByExt($ext) {
        $ext = trim($ext, '.');
        $ext = strtolower($ext);
        switch ($ext) {
            case 'gif': return 'image/gif';
            case 'jpeg':
            case 'jpg' :
            case 'jpe' : return 'image/jpeg';
            case 'png' : return 'image/png';
        }

        return FALSE;
    }

    function _getMime($path) {
        $size = @getimagesize($path);
        return (isset($size['mime'])) ? $size['mime'] : FALSE;
    }

    public function loadImageToEdit($path, $mimeType = '', $externalLink = FALSE) {
        if (!$externalLink && !file_exists($path))
            return FALSE;

        if ($mimeType == '') {
            $mimeType = $this->_getMime($path);
            if ($mimeType === FALSE)
                return FALSE;
        }

        switch ($mimeType) {
            case 'image/jpeg':
                ini_set('gd.jpeg_ignore_warning', 1);
                $image = @imagecreatefromjpeg($path);
                if (!is_resource($image)) {
                    $image = $this->_loadImageToEditRetry($path);
                }
                break;
            case 'image/png':
                $image = @imagecreatefrompng($path);
                if (!is_resource($image)) {
                    $image = $this->_loadImageToEditRetry($path);
                }
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($path);
                if (!is_resource($image)) {
                    $image = $this->_loadImageToEditRetry($path);
                }
                break;
            default:
                $image = FALSE;
                break;
        }

        if (is_resource($image)) {
            if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
                imagealphablending($image, FALSE);
                imagesavealpha($image, TRUE);
            }
        }

        return $image;
    }

    private function _loadImageToEditRetry($path) {
        $mimeType = $this->_getMime($path);
        if ($mimeType === FALSE)
            return FALSE;

        switch ($mimeType) {
            case 'image/jpeg':
                ini_set('gd.jpeg_ignore_warning', 1);
                $image = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($path);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($path);
                break;
            default:
                $image = FALSE;
                break;
        }

        return $image;
    }

    public function constrainDimensions($currentWidth, $currentHeight, $maxWidth = 0, $maxHeight = 0) {
        if (!$maxWidth && !$maxHeight) {
            return array($currentWidth, $currentHeight);
        }

        $widthRatio = $heightRatio = 1.0;
        $didWidth = $didHeight = FALSE;

        if ($maxWidth > 0 && $currentWidth > 0 && $currentWidth > $maxWidth) {
            $widthRatio = $maxWidth / $currentWidth;
            $didWidth = TRUE;
        }

        if ($maxHeight > 0 && $currentHeight > 0 && $currentHeight > $maxHeight) {
            $heightRatio = $maxHeight / $currentHeight;
            $didHeight = TRUE;
        }

        // Calculate the larger/smaller ratios
        $smallerRatio = min($widthRatio, $heightRatio);
        $largerRatio = max($widthRatio, $heightRatio);

        if (intval($currentWidth * $largerRatio) > $maxWidth || intval($currentHeight * $largerRatio) > $maxHeight) {
            // The larger ratio is too big. It would result in an overflow.
            $ratio = $smallerRatio;
        } else {
            // The larger ratio fits, and is likely to be a more "snug" fit.
            $ratio = $largerRatio;
        }

        $w = intval($currentWidth * $ratio);
        $h = intval($currentHeight * $ratio);

        // Sometimes, due to rounding, we'll end up with a result like this: 465x700 in a 177x177 box is 117x176... a pixel short
        // We also have issues with recursive calls resulting in an ever-changing result. Contraining to the result of a constraint should yield the original result.
        // Thus we look for dimensions that are one pixel shy of the max value and bump them up
        if ($didWidth && $w == $maxWidth - 1) {
            $w = $maxWidth; // Round it up
        }
        if ($didHeight && $h == $maxHeight - 1) {
            $h = $maxHeight; // Round it up
        }
        return array($w, $h);
    }

    public function imageResizeDimensions($origW, $origH, $destW, $destH, $crop = FALSE, $origType = '', $image = NULL, $externalLink = FALSE) {
        if ($origW <= 0 || $origH <= 0) {
            return FALSE;
        }
        // at least one of dest_w or dest_h must be specific
        if ($destW <= 0 && $destH <= 0) {
            return FALSE;
        }

        if ($crop) {
            // crop the largest possible portion of the original image that we can size to $destW x $destH
            $aspect_ratio = $origW / $origH;
            $new_w = min($destW, $origW);
            $new_h = min($destH, $origH);

            if (!$new_w) {
                $new_w = intval($new_h * $aspect_ratio);
            }

            if (!$new_h) {
                $new_h = intval($new_w / $aspect_ratio);
            }

            $size_ratio = max($new_w / $origW, $new_h / $origH);

            $crop_w = round($new_w / $size_ratio);
            $crop_h = round($new_h / $size_ratio);

            $s_x = floor(($origW - $crop_w) / 2);
            $s_y = floor(($origH - $crop_h) / 2);
        } else {
            // don't crop, just resize using $destW x $destH as a maximum bounding box
            $crop_w = $origW;
            $crop_h = $origH;

            $s_x = 0;
            $s_y = 0;

            list( $new_w, $new_h ) = $this->constrainDimensions($origW, $origH, $destW, $destH);
        }

        // if the resulting image would be the same size or larger we don't want to resize it
        if ($new_w >= $origW && $new_h >= $origH) {
            return FALSE;
        }

        // the return array matches the parameters to imagecopyresampled()
        // int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
        return array(0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h, $origType, $image, $externalLink);
    }

    /*
     * Rename an image with a full path to a new name in the same directory.
     */

    function renameImage($currentFullPath, $newName) {
        $info = pathinfo($currentFullPath);
        $dir = $info['dirname'];
        $ext = $info['extension'];
        $newPath = $dir . '/' . $newName . ".$ext";
        return rename($currentFullPath, $newPath) ? $newPath : FALSE;
    }

    public function getDims($file, $maxW, $maxH, $crop = FALSE) {
        $externalLink = (substr($file, 0, 7) == 'http://') ? TRUE : FALSE;
        if (!$externalLink) {
            $externalLink = (substr($file, 0, 8) == 'https://') ? TRUE : FALSE;
        }

        $image = $this->loadImageToEdit($file, '', $externalLink);

        if (!is_resource($image)) {
            return FALSE;
        }

        $size = @getimagesize($file);

        if (!$size) {
            return FALSE;
        }

        list($origW, $origH, $origType) = $size;

        if ($externalLink && $origW <= $maxW && $origH <= $maxH) {
            return array(0, 0, 0, 0, $origW, $origH, $origW, $origH, $origType, $image, $externalLink);
        } else {
            return $this->imageResizeDimensions($origW, $origH, $maxW, $maxH, $crop, $origType, $image, $externalLink);
        }
    }

    public function resizeImage($file, $maxW, $maxH, $crop = FALSE, $suffix = NULL, $dims = NULL, $destPath = NULL, $returnResource = FALSE, $quality = 90) {
        if ($dims == NULL) {
            $dims = $this->getDims($file, $maxW, $maxH, $crop);
        }
        list($dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH, $origType, $image, $externalLink) = $dims;

        if (!$dims) {
            if (!$externalLink) {
                return FALSE;
            }
            $newimage = & $image;
        } else {
            $fillBg = IMAGETYPE_PNG == $origType && $externalLink;
            $newimage = $this->imageCreateTrueColor($dstW, $dstH, $fillBg);

            imagecopyresampled($newimage, $image, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

            // convert from full colors to index colors, like original PNG.
            if (IMAGETYPE_PNG == $origType && function_exists('imageistruecolor') && !imageistruecolor($image)) {
                imagetruecolortopalette($newimage, false, imagecolorstotal($image));
            }

            // we don't need the original in memory anymore
            imagedestroy($image);
        }

        // $suffix will be appended to the destination filename, just before the extension
        if (!$suffix) {
            $suffix = "{$dstW}x{$dstH}";
        }

        $info = pathinfo($file);
        if ($externalLink) {
            $externalPath = Configure::read('Upload.CacheFolder');
            $dir = $externalPath . date('Y') . '/' . date('m') . '/' . date('d') . '/';
        } else {
            $dir = rtrim($info['dirname'], '/');
        }
        $ext = $info['extension'];

        if (!is_null($destPath) && $_destPath = realpath($destPath)) {
            $dir = $_destPath;
        }

        if ($externalLink) {
            $destFilename = "{$dir}/{$suffix}.";
        } else {
            $destFilename = "{$dir}/{$suffix}_{$dstW}x{$dstH}.";
        }

        if (IMAGETYPE_GIF == $origType) {
            $mimeType = 'image/gif';
            $destFilename .= 'gif';
            if (!imagegif($newimage, $destFilename)) {
                return FALSE;
            }
        } elseif (IMAGETYPE_PNG == $origType) {
            $mimeType = 'image/png';
            if ($externalLink || (strcasecmp($ext, 'jpg') != 0 && strcasecmp($ext, 'jpeg') != 0 && strcasecmp($ext, 'png') != 0)) {
                $ext = 'png';
            }
            $destFilename .= $ext;
            if (!file_exists($destFilename) && !imagepng($newimage, $destFilename, (10 - $quality / 10))) {
                return FALSE;
            }
        } elseif (IMAGETYPE_JPEG == $origType) {
            $mimeType = 'image/jpeg';
            if ($externalLink || (strcasecmp($ext, 'jpg') != 0 && strcasecmp($ext, 'jpeg') != 0 && strcasecmp($ext, 'png') != 0))
                $ext = 'jpg';
            $destFilename .= $ext;
            if (!file_exists($destFilename) && !imagejpeg($newimage, $destFilename, $quality)) {
                return FALSE;
            }
        } else {
            $mimeType = 'image/jpeg';
            $destFilename .= 'jpg';
            if (!imagejpeg($newimage, $destFilename, $quality)) {
                return FALSE;
            }
        }

        // Set correct file permissions
        $stat = stat(dirname($destFilename));
        $perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
        @chmod($destFilename, $perms);

        if ($returnResource) {
            return array('image' => $newimage, 'mime' => $mimeType);
        } else {
            return $destFilename;
        }
    }

    public function scaleImageResouce($img, $fwidth, $fheight) {
        $sX = imagesx($img);
        $sY = imagesy($img);

        // check if it has roughly the same w / h ratio
        $diff = round($sX / $sY, 2) - round($fwidth / $fheight, 2);

        if (-0.1 < $diff && $diff < 0.1) {
            // scale the full size image
            $dst = $this->imageCreateTrueColor($fwidth, $fheight);

            if (imagecopyresampled($dst, $img, 0, 0, 0, 0, $fwidth, $fheight, $sX, $sY)) {
                imagedestroy($img);
                $img = $dst;
                return $img;
            }
        }

        return FALSE;
    }

    function imageDisplay($resource, $mimeType, $source_image = '') {
        if ($source_image != '') {
            header("Content-Disposition: filename={$source_image};");
        }

        header("Content-Type: {$mimeType}");
        header('Content-Transfer-Encoding: binary');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

        switch ($mimeType) {
            case 'image/jpeg':
                header('Content-Type: image/jpeg');
                return imagejpeg($resource, null, 90);
            case 'image/png':
                header('Content-Type: image/png');
                return imagepng($resource);
            case 'image/gif':
                header('Content-Type: image/gif');
                return imagegif($resource);
            default:
                return FALSE;
        }
    }

    public function saveImageFile($image, $path, $mimeType = '') {
        if ($mimeType == '') {
            $file_ext = end(explode('.', $path));
            if (FALSE == ($mimeType = $this->_getMimeByExt($file_ext))) {
                return FALSE;
            }
        }

        switch ($mimeType) {
            case 'image/jpeg':
                return imagejpeg($image, $path, 90);
                break;
            case 'image/png':
                return imagepng($image, $path);
                break;
            case 'image/gif':
                return imagegif($image, $path);
                break;
        }

        return FALSE;
    }

    public function rotateImageResource($img, $angle) {
        if (function_exists('imagerotate')) {
            $rotated = imagerotate($img, $angle, 0);
            if (is_resource($rotated)) {
                imagedestroy($img);
                $img = $rotated;
            }
        }
        return $img;
    }

    /**
     * cropImageResource
     * Cat hinh tu toa do $x, $y chieu dai $w va rong $h.
     * @param Resource $img
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     */
    public function cropImageResource($img, $x, $y, $w, $h) {
        $dst = $this->imageCreateTrueColor($w, $h);
        if (is_resource($dst)) {
            if (imagecopy($dst, $img, 0, 0, $x, $y, $w, $h)) {
                imagedestroy($img);
                $img = $dst;
            }
        }
        return $img;
    }

    /**
     * flipImageResource
     * Lap hinh
     * @param Resource $img
     * @param bool $horz
     * @param bool $vert
     */
    public function flipImageResource($img, $horz, $vert) {
        $w = imagesx($img);
        $h = imagesy($img);
        $dst = $this->imageCreateTrueColor($w, $h);

        if (is_resource($dst)) {
            $sx = $vert ? ($w - 1) : 0;
            $sy = $horz ? ($h - 1) : 0;
            $sw = $vert ? -$w : $w;
            $sh = $horz ? -$h : $h;

            if (imagecopyresampled($dst, $img, 0, 0, $sx, $sy, $w, $h, $sw, $sh)) {
                imagedestroy($img);
                $img = $dst;
            }
        }

        return $img;
    }

}
