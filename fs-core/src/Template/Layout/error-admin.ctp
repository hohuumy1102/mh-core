<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $this->Html->meta('icon') ?>
    <?= $this->fetch('meta') ?>
    <title><?= $this->fetch('title') ?></title>

    <?= $this->Html->css('FsCore.bootstrap.min');?>
    <?= $this->Html->css('FsCore.animate');?>
    <?= $this->Html->css('FsCore.style');?>
    <?= $this->Html->css('FsCore.font-awesome');?>
</head>

<body class="gray-bg">

<div class="middle-box text-center animated fadeInDown">
    <?= $this->fetch('content') ?>
</div>

<!-- Mainly scripts -->
<?= $this->Html->script('FsCore.jquery-2.1.1');?>
<?= $this->Html->script('FsCore.bootstrap.min');?>

</body>

</html>
