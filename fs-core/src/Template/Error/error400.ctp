<?php
use Cake\Core\Configure;
use Cake\Error\Debugger;

$this->layout = 'error-admin';

if (Configure::read('debug')):
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error400.ctp');

    $this->start('file');
?>
<?php if (!empty($error->queryString)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h($error->queryString) ?>
    </p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
        <strong>SQL Query Params: </strong>
        <?php Debugger::dump($error->params) ?>
<?php endif; ?>
<?= $this->element('auto_table_warning') ?>
<?php
    if (extension_loaded('xdebug')):
        xdebug_print_function_stack();
    endif;

    $this->end();
else :
    $this->assign('title', __('Page Not Found'));
    ?>
    <h1>404</h1>
    <h3 class="font-bold"><?= __('Page Not Found')?></h3>

    <div class="error-desc">
        <?= __('Sorry, but the page you are looking for has not been found. Try checking the URL for error, then hit the refresh button on your browser or try found something else in our website.')?>
    </div>
<?php
endif;
?>

