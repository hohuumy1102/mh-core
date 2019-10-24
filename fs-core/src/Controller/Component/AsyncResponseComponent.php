<?php

namespace FsCore\Controller\Component;

use FsCore\Utility\Utils;
use Cake\Controller\Component;

class AsyncResponseComponent extends Component {

    protected $data = array();

    public function __construct() {
        $this->data['status'] = 1;
    }

    public function setError($err_id) {
        $this->data['error'] = $err_id;
    }

    public function setErrorSummary($err_summary) {
        $this->data['errorSummary'] = $err_summary;
    }

    public function setErrorDescription($err_desc) {
        $this->data['errorDescription'] = $err_desc;
    }

    public function redirect($url) {
        echo json_encode(array(
            'redirect' => $url
        ));
        exit(0);
    }

    public function prepend($selector, $content, $context = false) {
        $content = json_encode($content);
        if ($context !== false) {
            $this->data['onload'][] = "$('$selector',$context).prepend($content)";
        } else {
            $this->data['onload'][] = "$('$selector').prepend($content)";
        }
        return $this;
    }

    public function append($selector, $content, $context = false) {
        $content = json_encode($content);
        if ($context !== false) {
            $this->data['onload'][] = "$('$selector',$context).append($content)";
        } else {
            $this->data['onload'][] = "$('$selector').append($content)";
        }
        return $this;
    }

    public function remove($selector, $context = false) {
        if ($context !== false)
            $this->data['onload'][] = "$('$selector',$context).remove()";
        else
            $this->data['onload'][] = "$('$selector').remove()";
        return $this;
    }

    public function attr($key, $val) {
        $current = array_pop($this->data['onload']);
        $this->data['onload'][] = $current . ".attr('$key', '$val')";
        return $this;
    }

    public function html($selector, $content, $context = false) {
        $content = json_encode($content);
        if ($context != false) {
            $this->data['onload'][] = "$('$selector',$context).html($content)";
        } else {
            $this->data['onload'][] = "$('$selector').html($content)";
        }
        return $this;
    }

    public function run($code) {
        $this->data['onload'][] = $code;
    }

    public function addVar($key, $val) {
        $this->data['payload'][$key] = $val;
    }

    public function getData() {
        if (empty($this->data['onload']) && empty($this->data['payload'])) {
            $this->data['status'] = 0;
        }
        return $this->data;
    }

}
