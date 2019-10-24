<?php use Cake\Core\Configure; ?>
<?php $languageList = Configure::read('LanguageList'); ?>

<?php echo $this->Html->script('/fs_core/plugins/select2/select2.full.min.js'); ?>

<?php echo $this->Html->css('/fs_core/plugins/bootstrap-toggle/bootstrap-toggle.min.css'); ?>
<?php echo $this->Html->script('/fs_core/plugins/bootstrap-toggle/bootstrap-toggle.min.js'); ?>

<?php echo $this->Html->css('/fs_core/plugins/datepicker/datepicker3.css'); ?>
<?php echo $this->Html->script('/fs_core/plugins/datepicker/bootstrap-datepicker.js'); ?>

<?php echo $this->Html->css('/fs_core/plugins/datetimepicker/bootstrap-datetimepicker.css'); ?>
<?php echo $this->Html->script('/fs_core/plugins/datetimepicker/bootstrap-datetimepicker.js'); ?>

<?php echo $this->Html->css('/fs_core/multi_upload/uploadfile.css'); ?>
<?php echo $this->Html->script('/fs_core/multi_upload/jquery.uploadfile.min.js'); ?>

<?php echo $this->Html->css('/fs_core/plugins/colorpicker/bootstrap-colorpicker.min.css'); ?>
<?php echo $this->Html->script('/fs_core/plugins/colorpicker/bootstrap-colorpicker.min.js'); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <?= $this->element('FsCore.Crud/crud_main_nav') ?>

            <?= $this->Form->create($object, ['class' => 'form-horizontal', 'accept-charset' => 'utf-8', 'enctype' => 'multipart/form-data']) ?>
            <div class="box-body">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <?php if (!empty($inputTypes)): ?>
                        <li class="active">
                            <a href="#common_fields" data-toggle="tab"><?php echo __('Common Fields'); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($multiLangInputTypes)): ?>
                            <?php $index = 0; ?>
                            <?php foreach ($languageList as $languageCode => $languageLabel): ?>
                                <li class="<?php echo (empty($inputTypes) && $index === 0 ? 'active' : ''); ?>">
                                    <a href="#language_<?php echo $languageCode; ?>" data-toggle="tab"><?php echo __($languageLabel); ?></a>
                                </li>
                                <?php $index++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <div class="tab-content">
                        <?php if (!empty($inputTypes)): ?>
                        <div class="active tab-pane" id="common_fields">
                            <?= $this->element('FsCore.Crud/create_update_fields', ['fieldList' => $inputTypes]) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($multiLangInputTypes)): ?>
                            <?php $index = 0; ?>
                            <?php foreach ($languageList as $languageCode => $languageLabel): ?>
                                <div class="<?php echo (empty($inputTypes) && $index === 0 ? 'active' : ''); ?> tab-pane" id="language_<?php echo $languageCode; ?>">
                                    <?= $this->element('FsCore.Crud/create_update_fields', ['fieldList' => $multiLangInputTypes[$languageCode]]) ?>
                                </div>
                                <?php $index++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
            </div>
            <?= $this->Form->end() ?>
        </div><!-- /.box -->
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("a.thumbnail-link").on("click", function (e) {
            $('#modal-image').find('.modal-body img').attr('src', $(this).attr('href'));
            $('#modal-image').modal('show');
            e.preventDefault();
        });
        $("i.glyphicon-trash").parents('a').on("click", function (e) {
            var result = confirm('<?php echo __('Are you sure?'); ?>');
            if (!result) {
                e.preventDefault();
                return result;
            }
        });

        $(".select2").select2();
        $(".select2-tag").select2({tags: true});

        $(".datepicker").datepicker({
            format: 'dd/mm/yyyy',
        });
        $('.timepicker').datetimepicker({
            format: 'dd/mm/yyyy hh:ii:ss',
            showSecond: true,
            timeFormat: 'hh:mm:ss'
        });
        $(document).on('change', '.btn-file :file', function () {
            var input = $(this),
                    numFiles = input.get(0).files ? input.get(0).files.length : 1,
                    label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        $('.btn-file :file').on('fileselect', function (event, numFiles, label) {
            var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;
            if (input.length) {
                input.val(log);
            }
        });
        $('.my-colorpicker2').colorpicker();
    });
</script>