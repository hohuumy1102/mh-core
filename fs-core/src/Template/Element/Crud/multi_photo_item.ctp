<?php use Cake\Core\Configure; ?>
<?php $this->loadHelper('FsCore.Cf'); ?>
<?php $photoId = !empty($photo['id']) ? $photo['id'] : $photo->id; ?>
<?php $photoPath = !empty($photo['path']) ? $photo['path'] : $photo->path; ?>

<div class="fLeft mv10 mr10 border rounded5 pa10" id="multi_photo_<?php echo $photoId; ?>">
    <?php echo $this->Cf->upload($photoPath, ['class' => 'fLeft', 'width' => 60]); ?>
    <?php if (!empty($fieldInfo['additionFields'])) :
        $photoDescription = !empty($photo['description']) ? $photo['description'] : '';
        $photoTitle = !empty($photo['title']) ? $photo['title'] : '';
    ?>
    <input type="text" value="<?= $photoTitle?>" name="thumbnail_photo_title[<?= $photoId?>]">
    <textarea name="thumbnail_photo_description[<?= $photoId?>]"><?= $photoDescription?></textarea>
    <?php endif;?>

    <a href="#" class="photo_remove" title="<?php echo __('Remove'); ?>" rel="async" ajaxify="<?php echo $this->Url->build("backend/$controller/removeMultiPhotos/{$photoId}"); ?>">
        <?php echo $this->Html->image('/fs_core/images/cross_grey_small.png', ['class' => 'fLeft ml10', 'style' => 'left: 10px; top: 10px;']); ?>
    </a>
    <input type="hidden" name="<?php echo $field; ?>_photo[]" value="<?php echo $photoId; ?>" />
</div>
