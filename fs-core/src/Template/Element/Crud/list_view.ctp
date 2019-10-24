<?php use FsCore\Model\Entity\Photo; ?>
<?php echo $this->Html->css('/fs_core/plugins/daterangepicker/daterangepicker-bs3.css'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>

<?php echo $this->Html->script('/fs_core/plugins/select2/select2.full.min.js'); ?>
<?php echo $this->Html->script('/fs_core/plugins/input-mask/jquery.inputmask.js'); ?>
<?php echo $this->Html->script('/fs_core/plugins/input-mask/jquery.inputmask.date.extensions.js'); ?>
<?php echo $this->Html->script('/fs_core/plugins/input-mask/jquery.inputmask.extensions.js'); ?>
<?php echo $this->Html->script('/fs_core/plugins/daterangepicker/daterangepicker.js'); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <?= $this->element('FsCore.Crud/crud_main_nav') ?>

            <div class="box-body">
                <div class="mb20">
                    <?php $hasSearch = FALSE; ?>
                    <form id="searching_form" action="<?php echo $this->Url->build(['action' => 'index'], true); ?>" method="GET" accept-charset="utf-8" enctype="multipart/form-data">
                        <?php if (!empty($filterFields)): ?>
                            <?php $hasSearch = TRUE; ?>
                            <?php foreach ($filterFields as $field => $fieldInfo): ?>
                                <?php $currentValue = $fieldInfo['currentValue']; ?>
                                <div class="btn-group fLeft mr10">
                                    <button id="<?php echo $field . '_label'; ?>" type="button" class="btn btn-info current-selection"><?php echo (!empty($filterFields[$field]['options'][$currentValue]) ? $filterFields[$field]['options'][$currentValue] : __($field)); ?></button>
                                    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                        <span class="sr-only">&nbsp;</span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li class="<?php echo ($currentValue == -1 ? "active" : ""); ?>">
                                            <a data-value="-1" class="<?php echo $field . '_link'; ?>" href="#"><?php echo __($field); ?></a>
                                        </li>
                                        <?php foreach ($filterFields[$field]['options'] as $optionKey => $optionValue): ?>
                                            <li class="<?php echo ($currentValue == $optionKey ? "active" : ""); ?>">
                                                <a data-value="<?php echo $optionKey; ?>" class="<?php echo $field . '_link'; ?>" href="#"><?php echo $optionValue; ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <input id="<?php echo $field . '_input'; ?>" type="hidden" name="<?php echo $field; ?>" value="<?php echo $currentValue; ?>" />
                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        $('a.<?php echo $field . '_link'; ?>').click(function (e) {
                                            var value = $(this).data('value');
                                            $('#<?php echo $field . '_input'; ?>').val(value);
                                            var label = $(this).html();
                                            $('#<?php echo $field . '_label'; ?>').html(label);
                                            $(this).parents('ul').find('li').removeClass('active');
                                            $(this).parents('li').addClass('active');
                                            e.preventDefault();
                                        });
                                    });
                                </script>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($searchingFields)): ?>
                            <?php $hasSearch = TRUE; ?>
                            <div class="input-group w25p fLeft mr10">
                                <div class="input-group-addon">
                                    <i class="glyphicon glyphicon-search red"></i>
                                </div>
                                <input type="text" class="form-control pull-right" id="keyword" name="keyword" placeholder="<?php echo __('Keyword'); ?>" value="<?php echo !empty($this->request->query['keyword']) ? $this->request->query['keyword'] : '' ?>" />
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($dateTimeFilterField)): ?>
                            <?php $hasSearch = TRUE; ?>
                            <div class="input-group w25p fLeft mr10">
                                <div class="input-group-addon">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <input type="text" class="form-control pull-right" id="date_time" name="date_time" placeholder="<?php echo __('Select date'); ?>" value="<?php echo !empty($this->request->query['date_time']) ? $this->request->query['date_time'] : '' ?>" />
                            </div>
                        <?php endif; ?>

                        <?php if ($hasSearch): ?>
                            <input id="search_btn" class="btn btn-primary btn-minimize fLeft" type="submit" value="<?php echo __('Search'); ?>" />
                        <?php endif; ?>
                        <div class="clear"></div>
                    </form>
                </div>				

                <?php if (empty($objectList)) : ?>
                <p class="text-yellow description-block"><?php echo __('Empty Data'); ?></p>
                <?php else: ?>
                <table id="tableList" class="table table-bordered table-striped wordWrap">
                    <thead>
                        <tr>
                            <?php foreach (array_keys($objectList[0]) as $key): ?>
                                <?php if ($key != 'actions' && $key != 'id'): ?>
                                    <th><?= $this->Paginator->sort($key) ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <th><?= __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($objectList as $object): ?>
                        <tr>
                            <?php foreach ($object as $key => $value): ?>
                                <?php if (!empty($activationFields[$key])): ?>
                                    <td>
                                        <div class="btn-group" id="<?php echo $object['id']; ?>-<?php echo $key; ?>-btn">
                                            <button type="button" class="btn btn-info current-selection"><?php echo (!empty($activationFields[$key][$value]) ? $activationFields[$key][$value] : __($key)); ?></button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">&nbsp;</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <?php foreach ($activationFields[$key] as $optionKey => $optionValue): ?>
                                                    <li id="<?php echo $object['id']; ?>-<?php echo $key; ?>-<?php echo $optionKey; ?>" style="display: <?php echo ($optionKey == $value ? 'none' : 'block'); ?>;">
                                                        <a rel="async" href="#" ajaxify="<?php echo $this->Url->build(['action' => 'activate', $object['id'], $key, $optionKey], true); ?>">
                                                            <?php echo $optionValue; ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </td>
                                <?php elseif (is_array($value) && $key != 'actions'): ?>
                                    <td>
                                        <?php if (!empty($value['type']) && $value['type'] == 'box'): ?>
                                            <span class="<?php echo (!empty($value['class']) ? $value['class'] : ''); ?>"><?php echo $value['value']; ?></span>
                                        <?php elseif (!empty($value['path'])): ?>
                                            <?php $photoPath = $this->Cf->imageUrl($value['path']); ?>
                                            <a href="<?php echo $photoPath; ?>" class="thumbnail-link">
                                                <img src="<?php echo $photoPath; ?>" width="100" />
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                <?php elseif ($key != 'actions' && $key != 'id'): ?>
                                    <td><?php echo $value; ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <td class="actions">
                                <?php if (!empty($object['actions'])): ?>
                                    <?php foreach ($object['actions'] as $action): ?>
                                        <a class="mb5 btn btn-<?php echo $action['button']; ?>" href="<?php echo $action['url']; ?>" title="<?php echo __($action['label']); ?>">
                                            <i class="glyphicon glyphicon-<?php echo $action['icon']; ?> icon-white"></i>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <?php foreach (array_keys($objectList[0]) as $key): ?>
                                <?php if ($key != 'actions' && $key != 'id'): ?>
                                    <th><?= $this->Paginator->sort($key) ?></th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <th><?= __('Actions'); ?></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="paginator">
                    <ul class="pagination">
                    <?= $this->Paginator->prev('< ' . __('Previous')) ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next(__('Next') . ' >') ?>
                    </ul>
                    <p><?= $this->Paginator->counter() ?> - <?= __('Total records') . ' ' . $totalRecords; ?></p>
                </div>
                <?php endif; ?>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#date_time').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY h:mm A'});
        $('.btn-delete').html('<i class="glyphicon glyphicon-trash icon-white"></i>');
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
    });
</script>
