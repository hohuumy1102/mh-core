<!DOCTYPE html>
<html>
    <head>
        <?= $this->Html->charset() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= $this->Html->meta('icon') ?>
        <?= $this->fetch('meta') ?>
        <title><?= $headerTitle; ?></title>

        <?php $favicon = $this->Url->build("/images/favicon.ico", true); ?>
        <link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon" />
        <link rel="shortcut icon" href="<?= $favicon; ?>" type="image/x-icon" />

        <?php echo $this->Html->css('/fs_core/bootstrap/css/bootstrap.min.css'); ?>
        <?php echo $this->Html->css('/fs_core/plugins/font-awesome/css/font-awesome.min.css'); ?>
        <?php echo $this->Html->css('/fs_core/plugins/ionicons/css/ionicons.min.css'); ?>
        <?php echo $this->Html->css('/fs_core/plugins/select2/select2.min.css'); ?>
        <?php echo $this->Html->css('/fs_core/adminlte/css/AdminLTE.min.css'); ?>
        <?php echo $this->Html->css('/fs_core/adminlte/css/skins/_all-skins.min.css'); ?>
        <?php echo $this->Html->css('/fs_core/css/default.css'); ?>

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <?php echo $this->Html->script('/fs_core/plugins/html5shiv/html5shiv.min.js'); ?>
        <![endif]-->

        <!-- jQuery -->
        <?php echo $this->Html->script('/fs_core/plugins/jQuery/jQuery-2.1.4.min.js'); ?>
        <?php echo $this->Html->script('/fs_core/bootstrap/js/bootstrap.min.js'); ?>
        <?php echo $this->Html->script('/fs_core/plugins/fastclick/fastclick.min.js'); ?>
        <?php echo $this->Html->script('/fs_core/adminlte/js/app.min.js'); ?>
        <?php echo $this->Html->script('/fs_core/js/common.js'); ?>
        <?php echo $this->Html->script('/fs_core/js/async_request.js'); ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <a target="_blank" href="<?= $this->Url->build('/'); ?>" class="logo">
                    <span class="logo-mini"><i class="fa fa-fw fa-home"></i></span>
                    <span class="logo-lg">
                        <i class="fa fa-fw fa-home"></i>
                    </span>
                </a>
                <nav class="navbar navbar-static-top" role="navigation">
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <?= $this->element('FsCore.top_menu') ?>
                        </ul>
                    </div>
                </nav>
            </header>

            <aside class="main-sidebar">
                <section class="sidebar">
                    <?= $this->element('FsCore.left_menu') ?>
                </section>
            </aside>

            <div class="content-wrapper">
                <section class="content-header">
                    <?= $this->element('FsCore.breadcrumb') ?>
                </section>
                <section class="content">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </section>
            </div>
            <footer class="main-footer">
                <div><strong><?php echo __('Copyright'); ?></strong> FindSoft &copy; <?php echo date('Y'); ?></div>
            </footer>
        </div>
        <?= $this->element('FsCore.modal_popup') ?>
        <?= $this->fetch('script') ?>
    </body>
</html>
