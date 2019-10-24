<?php

namespace FsCore\Model\Table;

use FsCore\Model\Entity\Seo;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SeosTable extends Table {

    public function initialize(array $config) {
        parent::initialize($config);
        $this->table('seo');
    }

    public function validationDefault(Validator $validator) {
        $validator->integer('id')->allowEmpty('id', 'create');

        $validator->requirePresence('target_id')->integer('target_id')->notEmpty('target_id');
        $validator->requirePresence('target_type')->notEmpty('target_type');
        $validator->requirePresence('language')->notEmpty('language');
        return $validator;
    }

    public function beforeMarshal(Event $event, $data) {
        if (!empty($data['content']) && is_array($data['content'])) {
            $data['content'] = json_encode($data['content']);
        }
    }

}
