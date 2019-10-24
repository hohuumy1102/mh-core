<?php

namespace FsCore\Model\Table;

use FsCore\Model\Entity\LanguageContent;
use Cake\ORM\Entity;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class LanguageContentsTable extends Table {

    public function validationDefault(Validator $validator) {
        $validator->integer('id')->allowEmpty('id', 'create');

        $validator->requirePresence('target_id')->integer('target_id')->notEmpty('target_id');
        $validator->requirePresence('target_type')->notEmpty('target_type');
        $validator->requirePresence('language')->notEmpty('language');
        $validator->requirePresence('field')->notEmpty('field');
        return $validator;
    }

    public function updateLanguageContent($targetId, $targetType, $language, $field, $content) {
        $keyFields = [
            'target_id' => $targetId,
            'target_type' => $targetType,
            'language' => $language,
            'field' => $field,
        ];
        $record = $this->find('all', [
                    'conditions' => $keyFields,
                ])->first();
        if (empty($record)) {
            $record = $this->newEntity($keyFields);
        }
        $record->content = $content;
        $this->save($record);
        return $record;
    }

}
