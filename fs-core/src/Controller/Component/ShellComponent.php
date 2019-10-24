<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Console\ShellDispatcher;

class ShellComponent extends Component {

    protected $cakeConsole = ROOT . DS . 'bin' . DS . 'cake';

    public function run($shell, $background = false, $params = []) {
        //Build command
        $cmd = array($shell);

        $pre = '';
        $suf = '';

        //Params
        foreach ($params as $value) {
            $cmd[] = $value;
        }
        $cmd[] = 'app' . ' ' . APP;

        //Background
        if ($background == true) {
            $pre .= '/usr/bin/nohup';
            $suf .= ' > /dev/null & echo $!';
        }

        $cmd = $pre . ' ' . $this->cakeConsole . ' ' . implode(' ', $cmd) . $suf;
        $this->log('cmd: ' . $cmd);
        return shell_exec($cmd);
    }

}
