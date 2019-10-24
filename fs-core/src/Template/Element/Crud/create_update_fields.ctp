<?php foreach ($fieldList as $field => $fieldInfo): ?>
    <div class="form-group <?php echo (!empty($errors[$field]) ? 'has-error' : ''); ?>">
        <label class="col-md-2 control-label text-capitalize" for="<?php echo $field; ?>"><?php echo __($fieldInfo['label']); ?></label>
        <div class="col-md-10">
            <?php if ($fieldInfo['input'] == 'dropdown'): ?>
                <select id="<?php echo $field; ?>" name="<?php echo $field; ?>" class="form-control select2">
                    <?php foreach ($fieldInfo['options'] as $optionVal => $optionKey): ?>
                        <option value="<?php echo $optionVal; ?>" <?php echo ($optionVal == $fieldInfo['currentValue'] ? 'selected="selected"' : ''); ?>><?php echo $optionKey; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($fieldInfo['input'] == 'multi_select'): ?>
                <select id="<?php echo $field; ?>" name="<?php echo $field; ?>[]" class="form-control select2" multiple="multiple">
                    <?php foreach ($fieldInfo['options'] as $optionVal => $optionKey): ?>
                        <option value="<?php echo $optionVal; ?>" <?php echo (!empty($fieldInfo['currentValue'][$optionVal]) ? 'selected="selected"' : ''); ?>><?php echo $optionKey; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($fieldInfo['input'] == 'multi_tag'): ?>
                <select id="<?php echo $field; ?>" name="<?php echo $field; ?>[]" class="form-control select2-tag" multiple="multiple">
                    <?php foreach ($fieldInfo['options'] as $optionVal => $optionKey): ?>
                        <option value="<?php echo $optionVal; ?>" <?php echo (!empty($fieldInfo['currentValue'][$optionVal]) ? 'selected="selected"' : ''); ?>><?php echo $optionKey; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($fieldInfo['input'] == 'textarea'): ?>
                <textarea class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>"><?php echo $fieldInfo['currentValue']; ?></textarea>
            <?php elseif ($fieldInfo['input'] == 'ckeditor'): ?>
                <textarea class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>"><?php echo $fieldInfo['currentValue']; ?></textarea>
                <?= $this->element('FsCore.ckeditor', ['elements' => [$field]]) ?>
            <?php elseif ($fieldInfo['input'] == 'datepicker'): ?>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" class="form-control datepicker" value="<?php echo htmlentities($fieldInfo['currentValue']); ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>" />
                </div>
            <?php elseif ($fieldInfo['input'] == 'colorpicker'): ?>
                <div class="input-group my-colorpicker2">
                    <div class="input-group-addon">
                        <i></i>
                    </div>
                    <input type="text" class="form-control" value="<?php echo htmlentities($fieldInfo['currentValue']); ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>" />
                </div>
            <?php elseif ($fieldInfo['input'] == 'timepicker'): ?>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <input type="text" class="form-control timepicker" value="<?php echo htmlentities($fieldInfo['currentValue']); ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>" />
                </div>
            <?php elseif ($fieldInfo['input'] == 'suffix'): ?>
                <div class="input-group">
                    <input type="text" class="form-control" value="<?php echo htmlentities($fieldInfo['currentValue']); ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>" />
                    <div class="input-group-addon">
                        <?php if (!empty($fieldInfo['icon'])): ?>
                            <i class="fa fa-<?php echo $fieldInfo['icon']; ?>"></i>
                        <?php else: ?>
                            <?php echo $fieldInfo['extra']; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($fieldInfo['input'] == 'prefix'): ?>
                <div class="input-group">
                    <div class="input-group-addon">
                        <?php if (!empty($fieldInfo['icon'])): ?>
                            <i class="fa fa-<?php echo $fieldInfo['icon']; ?>"></i>
                        <?php else: ?>
                            <?php echo $fieldInfo['extra']; ?>
                        <?php endif; ?>
                    </div>
                    <input type="text" class="form-control" value="<?php echo htmlentities($fieldInfo['currentValue']); ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>" />
                </div>
            <?php elseif ($fieldInfo['input'] == 'bothfix'): ?>
                <div class="input-group">
                    <div class="input-group-addon">
                        <?php if (!empty($fieldInfo['prefix_icon'])): ?>
                            <i class="fa fa-<?php echo $fieldInfo['prefix_icon']; ?>"></i>
                        <?php else: ?>
                            <?php echo $fieldInfo['prefix_extra']; ?>
                        <?php endif; ?>
                    </div>
                    <input type="text" class="form-control" value="<?php echo htmlentities($fieldInfo['currentValue']); ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>" />
                    <div class="input-group-addon">
                        <?php if (!empty($fieldInfo['suffix_icon'])): ?>
                            <i class="fa fa-<?php echo $fieldInfo['suffix_icon']; ?>"></i>
                        <?php else: ?>
                            <?php echo $fieldInfo['suffix_extra']; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($fieldInfo['input'] == 'checkbox_toggle'): ?>
                <input <?php echo ($fieldInfo['currentValue'] ? 'checked="checked"' : ''); ?> type="checkbox" data-toggle="toggle" id="<?php echo $field; ?>" name="<?php echo $field; ?>" />
            <?php elseif ($fieldInfo['input'] == 'none'): ?>
                <?php echo $fieldInfo['currentValue']; ?>
            <?php elseif ($fieldInfo['input'] == 'photo'): ?>
                <?php if (!empty($fieldInfo['currentValue'])): ?>
                    <?php $photoPath = $this->Cf->imageUrl($fieldInfo['currentValue']->path); ?>
                    <a href="<?php echo $photoPath; ?>" class="thumbnail-link">
                        <img src="<?php echo $photoPath; ?>" width="100" />
                    </a>
                    <input type="hidden" name="<?php echo $field; ?>_id" value="<?php echo $fieldInfo['currentValue']->id; ?>" />
                <?php endif; ?>
            <?php elseif ($fieldInfo['input'] == 'upload'): ?>
                <div class="input-group">
                    <span class="input-group-btn">
                        <span class="btn btn-primary btn-file">
                            <?php echo __('Choose file'); ?>
                            <input type="file" id="<?php echo $field; ?>" name="<?php echo $field; ?>" />
                        </span>
                    </span>
                    <input type="text" class="form-control" readonly>
                </div>
                <?php if (!empty($fieldInfo['width'])): ?>
                    <span class="help-block">
                        <?php echo __('Min Width: ') . $fieldInfo['width'] . ' px'; ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($fieldInfo['height'])): ?>
                    <span class="help-block">
                        <?php echo __('Min Height: ') . $fieldInfo['height'] . ' px'; ?>
                    </span>
                <?php endif; ?>
            <?php elseif ($fieldInfo['input'] == 'multi-photo'): ?>
                <div id="uploaded_files-<?php echo $field; ?>">
                <?php if (!empty($fieldInfo['currentValue']) && is_array($fieldInfo['currentValue'])): ?>
                    <?php foreach ($fieldInfo['currentValue'] as $photo): ?>
                        <?= $this->element('FsCore.Crud/multi_photo_item', ['photo' => $photo, 'field' => $field, 'fieldInfo' => $fieldInfo]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($uploadingPhotos)): ?>
                    <?php foreach ($uploadingPhotos as $photo): ?>
                        <?= $this->element('FsCore.Crud/multi_photo_item', ['photo' => $photo, 'field' => $field, 'fieldInfo' => $fieldInfo]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
                <div class="clear"></div>
                <div id="mulit-upload-<?php echo $field; ?>">Upload</div>
                <script type="text/javascript">
                    $(document).ready(function () {
                        var settings = {
                            url: "<?php echo $this->Url->build("backend/$controller/uploadMultiPhotos/{$field}", true); ?>",
                            method: "POST",
                            allowedTypes: "jpg,png,gif",
                            fileName: "myfile",
                            multiple: true,
                            onSuccess: function (files, data, xhr) {
                                if (typeof (data.html) != 'undefined') {
                                    $('#uploaded_files-<?php echo $field; ?>').append(data.html);
                                }
                            }, onError: function (files, status, errMsg) {
                            }
                        }
                        $("#mulit-upload-<?php echo $field; ?>").uploadFile(settings);
                    });
                </script>
            <?php elseif ($fieldInfo['input'] == 'google-map'): ?>
                <?= $this->element('FsCore.Crud/pagelet_google_map', ['field' => $field, 'value' => $fieldInfo['currentValue']]) ?>
            <?php else: ?>
                <input type="<?php echo $fieldInfo['input']; ?>" class="form-control" value="<?php echo ($fieldInfo['input'] == 'password' ? '' : htmlentities($fieldInfo['currentValue'])); ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo __($fieldInfo['label']); ?>" />
            <?php endif; ?>
            <?php if (!empty($errors[$field])): ?>
                <label class="error-message"><?php echo implode('<br />', $errors[$field]); ?></label>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
