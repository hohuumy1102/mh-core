<?php use Cake\Core\Configure; ?>
<?php echo $this->Html->script('/fs_core/plugins/select2/select2.full.min.js'); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <?= $this->element('FsCore.Crud/crud_main_nav') ?>

            <?= $this->Form->create($object, ['class' => 'form-horizontal', 'accept-charset' => 'utf-8', 'enctype' => 'multipart/form-data']) ?>
            <div class="box-body">
                <div class="form-group">
                    <label class="col-md-2 control-label text-capitalize"><?php echo __('Current Item'); ?></label>
                    <label class="col-md-10 mt7"><?php echo $object->displayField; ?></label>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label text-capitalize" for="position"><?php echo __('Position'); ?></label>
                    <div class="col-md-10">
                        <select id="position" name="position" class="form-control select2">
                            <option value="-1" selected="selected"><?php echo __('Before'); ?></option>
                            <option value="1"><?php echo __('After'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label text-capitalize" for="target_id"><?php echo __('Item'); ?></label>
                    <div class="col-md-10">
                        <select id="target_id" name="target_id" class="form-control select2">
                        <?php foreach ($sameLevelObjects as $targetObject): ?>
                            <?php if ($targetObject->id != $object->id):?>
                                <option value="<?php echo $targetObject->id; ?>"><?php echo $targetObject->displayField; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </select>
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
        $("i.glyphicon-trash").parents('a').on("click", function (e) {
            var result = confirm('<?php echo __('Are you sure?'); ?>');
            if (!result) {
                e.preventDefault();
                return result;
            }
        });
        $(".select2").select2();
    });
</script>