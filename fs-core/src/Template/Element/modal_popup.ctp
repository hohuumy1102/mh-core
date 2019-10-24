<?php use Cake\Core\Configure; ?>
<div class="modal" id="modal-dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?php echo __('Notice'); ?></h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" id="close-btn" class="btn btn-default pull-left" data-dismiss="modal"><?php echo __('Ok'); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<div id="modal-image" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-imagelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <img src="" class="img-responsive" />
            </div>
        </div>
    </div>
</div>
