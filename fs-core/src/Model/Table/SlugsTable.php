<?php

namespace FsCore\Model\Table;

use FsCore\Model\Entity\Slug;
use Cake\ORM\Entity;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SlugsTable extends Table {

    public function initialize(array $config) {
        parent::initialize($config);
        $this->table('slugs');
    }

    public function validationDefault(Validator $validator) {
        $validator->integer('id')->allowEmpty('id', 'create');

        $validator->requirePresence('name')->notEmpty('name');
        $validator->requirePresence('target_id')->integer('target_id')->notEmpty('target_id');
        $validator->requirePresence('target_type')->notEmpty('target_type');
        $validator->requirePresence('language')->notEmpty('language');
        return $validator;
    }

    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->isUnique([
                    'name'
        ]));
        return $rules;
    }

    public function updateSlug($targetId, $targetType, $language, $slug) {
        $keyFields = [
            'target_id' => $targetId,
            'target_type' => $targetType,
            'language' => $language,
        ];
        $record = $this->find('all', [
                    'conditions' => $keyFields,
                ])->first();
        if (empty($record)) {
            $record = $this->newEntity($keyFields);
        }
        $uid = uniqid();
        $slug .= '-' . $targetId . $language;
        if ($record->name == $slug) {
            return;
        }
        $record->name = $slug;
        $this->save($record);
        return $record;
    }

}
