<?php

namespace FsCore\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use FsCore\Utility\Utils;

class SlugComponent extends Component {

    public function getTargetId($slug, $type = false) {
        if (empty($slug)) {
            return 0;
        }
        Utils::useTables($this, ['FsCore.Slugs']);
        Utils::useComponents($this, ['FsCore.MultiLanguage']);
        $languageCode = $this->MultiLanguage->getCurrentLanguageCode();
        $conditions = [
            'name' => $slug,
            'language' => $languageCode,
        ];
        if (!empty($type)) {
            $conditions['target_type'] = $type;
        }
        $object = $this->Slugs->find('all', [
                    'conditions' => $conditions,
                ])->first();
        if (!empty($object)) {
            return $object->target_id;
        }
        return 0;
    }

    public function getTargetObject($slug, $type = false) {
        if (empty($slug)) {
            return null;
        }
        Utils::useTables($this, ['FsCore.Slugs']);
        Utils::useComponents($this, ['FsCore.MultiLanguage']);
        $languageCode = $this->MultiLanguage->getCurrentLanguageCode();
        $conditions = [
            'name' => $slug,
            'language' => $languageCode,
        ];
        if (!empty($type)) {
            $conditions['target_type'] = $type;
        }
        $object = $this->Slugs->find('all', [
                    'conditions' => $conditions,
                ])->first();
        if (!empty($object)) {
            $model = $object->target_type;
            Utils::useTables($this, [$model]);
            $targetObject = $this->$model->findById($object->target_id)->first();
            return $targetObject;
        }
        return null;
    }

    public function getSlugObject($targetId, $type, $languageCode = false) {
        if (empty($targetId) || empty($type)) {
            return null;
        }
        Utils::useTables($this, ['FsCore.Slugs']);
        if ($languageCode === false) {
            Utils::useComponents($this, ['FsCore.MultiLanguage']);
            $languageCode = $this->MultiLanguage->getCurrentLanguageCode();
        }
        $slug = $this->Slugs->find('all', [
                    'conditions' => [
                        'target_id' => $targetId,
                        'target_type' => $type,
                        'language' => $languageCode,
                    ],
                ])->first();
        return $slug;
    }

}
