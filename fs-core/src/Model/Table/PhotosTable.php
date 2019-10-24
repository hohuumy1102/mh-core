<?php

namespace FsCore\Model\Table;

use FsCore\Model\Entity\Photo;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class PhotosTable extends Table {

    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('photos');
        $this->primaryKey('id');
    }

    public function validationDefault(Validator $validator) {
        $validator->integer('id')->allowEmpty('id', 'create');

        $validator->requirePresence('path', 'create')
                ->notEmpty('path');
        return $validator;
    }

}
