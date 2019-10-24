<?php echo $this->Html->script('/fs_core/ckeditor/ckeditor.js'); ?>
<?php
    $connector = '/filemanager/connectors/connector.php';
    $filebrowserImageBrowseUrl = '/filemanager/browser/browser.html?Type=Image&Connector=' . $connector;
    $filebrowserImageUploadUrl = '/filemanager/connectors/upload.php?Type=Image';
    $filebrowserFileBrowseUrl = '/filemanager/browser/browser.html?Connector=' . $connector;
?>
<script type="text/javascript">
    <?php foreach ($elements as $element): ?>
        $('textarea#<?php echo $element; ?>').each(function () {
            CKEDITOR.replace($(this).attr('name'), {
                filebrowserImageBrowseUrl: '<?= $filebrowserImageBrowseUrl ?>',
                filebrowserImageUploadUrl: '<?= $filebrowserImageUploadUrl ?>',
                filebrowserBrowseUrl: '<?= $filebrowserFileBrowseUrl ?>'
            });
        });
    <?php endforeach; ?>
</script>