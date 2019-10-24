<?php use Cake\Core\Configure; ?>
<?php $languageList = Configure::read('LanguageList'); ?>

<?php echo $this->Html->script('/fs_core/plugins/select2/select2.full.min.js'); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <?= $this->element('FsCore.Crud/crud_main_nav') ?>

            <?= $this->Form->create(false, ['class' => 'form-horizontal', 'accept-charset' => 'utf-8', 'enctype' => 'multipart/form-data']) ?>
            <div class="box-body">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <?php $index = 0; ?>
                        <?php foreach ($languageList as $languageCode => $languageLabel): ?>
                            <li class="<?php echo ($index === 0 ? 'active' : ''); ?>">
                                <a href="#language_<?php echo $languageCode; ?>" data-toggle="tab"><?php echo __($languageLabel); ?></a>
                            </li>
                            <?php $index++; ?>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content">
                        <?php $index = 0; ?>
                        <?php foreach ($languageList as $languageCode => $languageLabel): ?>
                            <div class="<?php echo ($index === 0 ? 'active' : ''); ?> tab-pane" id="language_<?php echo $languageCode; ?>">
                                <div class="form-group">
                                    <label class="col-md-2 control-label text-capitalize" for="seo_data[<?php echo $languageCode; ?>][keyword]"><?php echo __('Keyword'); ?></label>
                                    <div class="col-md-10">
                                        <select id="seo_data[<?php echo $languageCode; ?>][keyword]" name="seo_data[<?php echo $languageCode; ?>][keyword][]" class="form-control select2-tag" multiple="multiple">
                                            <?php if (!empty($seoList[$languageCode]->content['keyword'])): ?>
                                                <?php foreach ($seoList[$languageCode]->content['keyword'] as $key): ?>
                                                    <option value="<?php echo $key; ?>" selected="selected"><?php echo $key; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label text-capitalize" for="seo_data[<?php echo $languageCode; ?>][description]"><?php echo __('Page Description'); ?></label>
                                    <div class="col-md-10">
                                        <textarea class="form-control" id="seo_data[<?php echo $languageCode; ?>][description]" name="seo_data[<?php echo $languageCode; ?>][description]"><?php echo (!empty($seoList[$languageCode]->content['description']) ? $seoList[$languageCode]->content['description'] : ''); ?></textarea>
                                    </div>
                                </div>
                                <?php if (!empty($seoList[$languageCode]->content['thumbnail'])): ?>
                                <div class="form-group">
                                    <label class="col-md-2 control-label text-capitalize" for="seo_data[<?php echo $languageCode; ?>][thumbnail]"><?php echo __('Current Thumbnail'); ?></label>
                                    <div class="col-md-10">
                                        <?php $photoPath = $this->Cf->imageUrl($seoList[$languageCode]->content['thumbnail']); ?>
                                        <a href="<?php echo $photoPath; ?>" class="thumbnail-link">
                                            <img src="<?php echo $photoPath; ?>" width="100" />
                                        </a>
                                        <input type="hidden" name="seo_data[<?php echo $languageCode; ?>][thumbnail]" value="<?php echo $photoPath; ?>" />
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="form-group <?php echo (!empty($errors['seo_data'][$languageCode]['thumbnail_photo']) ? 'has-error' : ''); ?>">
                                    <label class="col-md-2 control-label text-capitalize" for="seo_data[<?php echo $languageCode; ?>][thumbnail_photo]"><?php echo __('New Thumbnail'); ?></label>
                                    <div class="col-md-10">
                                        <div class="input-group">
                                            <span class="input-group-btn">
                                                <span class="btn btn-primary btn-file">
                                                    <?php echo __('Choose file'); ?>
                                                    <input type="file" id="seo_data_<?php echo $languageCode; ?>_thumbnail_photo" name="seo_data_<?php echo $languageCode; ?>_thumbnail_photo" />
                                                </span>
                                            </span>
                                            <input type="text" class="form-control" readonly>
                                        </div>
                                        <?php if (!empty($errors['seo_data'][$languageCode]['thumbnail_photo'])): ?>
                                            <label class="error-message"><?php echo implode('<br />', $errors['seo_data'][$languageCode]['thumbnail_photo']); ?></label>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php $index++; ?>
                        <?php endforeach; ?>
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

        $(".select2-tag").select2({tags: true});

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
    });
</script>