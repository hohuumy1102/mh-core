<div class="box-header with-border">
    <?php if (!empty($formSearch)): ?>
    <?php echo $this->Form->create('search', ['type' => $formSearch['type'], 'name' => $formSearch['name'], 'class' => 'form-horizontal']);?>
    <?php foreach ($formSearch['elements'] as $field => $element): ?>
    <div class="row">
        <?php if ($element['input'] == 'dropdown'): ?>
            <select id="<?= $field?>" name="<?= $field?>" class="form-control select2">
                <option value="0" <?php echo (empty($this->request->query[$field]) ? 'selected="selected"' : ''); ?>><?php echo __('Select ' . $element['label']); ?></option>
                <?php foreach ($element['options'] as $optionVal => $optionKey): ?>
                    <option value="<?php echo $optionVal; ?>" <?php echo (!empty($this->request->query[$field]) && $this->request->query[$field] == $optionVal ? 'selected="selected"' : ''); ?>><?php echo $optionKey; ?></option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($element['input'] == 'daterangepicker'): ?>
            <div class="col-xs-12">
                <div class="form-group col-xs-5">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <input type="text" class="form-control pull-right" id="<?= $field?>" name="<?= $field?>" placeholder="<?php echo __('Select range'); ?>" value="<?php echo !empty($this->request->query[$field]) ? $this->request->query[$field] : '' ?>" />
                    </div>
                </div>
            </div>
            <?php
            echo $this->Html->css('/fs_core/plugins/daterangepicker/daterangepicker-bs3.css');
            echo $this->Html->script('/fs_core/plugins/input-mask/jquery.inputmask.js');
            echo $this->Html->script('/fs_core/plugins/input-mask/jquery.inputmask.date.extensions.js');
            echo $this->Html->script('/fs_core/plugins/input-mask/jquery.inputmask.extensions.js');
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>';
            echo $this->Html->script('/fs_core/plugins/daterangepicker/daterangepicker.js');
            ?>
            <script type="text/javascript">
                $('#<?= $field?>').daterangepicker({format: 'MM/DD/YYYY'});
            </script>
        <?php elseif ($element['input'] == 'textbox'): ?>
            <div class="col-xs-12">
                <div class="form-group col-xs-5">
                    <div class="input-group">
                        <input type="text" class="form-control pull-right" id="<?= $field?>" name="<?= $field?>" placeholder="<?php echo __('Image code'); ?>" value="<?php echo !empty($this->request->query[$field]) ? $this->request->query[$field] : '' ?>" />
                    </div>
                </div>
            </div>
        <?php endif?>
    </div>
    <?php endforeach; ?>
    <?php foreach ($formSearch['actions'] as $field => $element): ?>
    <div class="col-sm-1">
        <button type="submit" class="btn btn-default btn-flat" id="<?=$field?>" name="<?=$field?>" > <?= $element['label']?></button>
    </div>
    <?php endforeach; ?>
    <?php echo $this->Form->end() ?>
    <?php endif; ?>

    <?php if (!empty($navList)): ?>
        <?php foreach ($navList as $nav): ?>
        <a class="btn btn-default btn-flat" href="<?php echo $nav['url']; ?>">
            <i class="glyphicon glyphicon-<?php echo $nav['icon']; ?>"></i>
            <?php echo __($nav['label']); ?>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!empty($formSearch)): ?>
<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2();

    });
</script>
<?php endif; ?>