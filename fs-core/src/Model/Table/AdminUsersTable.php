<?php

namespace FsCore\Model\Table;

use FsCore\Model\Entity\AdminUser;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AdminUsers Model
 *
 * @property \Cake\ORM\Association\BelongsTo $AdminRoles
 */
class AdminUsersTable extends Table {

    /**
     * Initialize method
     *
     * @param array $config
     *            The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('admin_users');
        $this->displayField('email');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('AdminRoles', [
            'foreignKey' => 'admin_role_id',
            'className' => 'FsCore.AdminRoles'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator
     *            Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator) {
        $validator->integer('id')->allowEmpty('id', 'create');

        $validator->email('email')
                ->requirePresence('email', 'create')
                ->notEmpty('email');

        $validator->requirePresence('password', 'create')->notEmpty('password');
        $validator->allowEmpty('password', 'update');
        $validator->requirePresence('password_confirm', 'create')->notEmpty('password_confirm');
        $validator->allowEmpty('password_confirm', 'update');

        $validator->integer('status')
                ->requirePresence('status', 'create')
                ->notEmpty('status');

        $validator->dateTime('last_login')->allowEmpty('last_login');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules
     *            The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->isUnique([
                    'email'
        ]));
        $rules->add($rules->existsIn([
                    'admin_role_id'
                        ], 'AdminRoles'));
        $rules->add(
                function ($entity, $options) {
            if (empty($entity->password_confirm) && !$entity->dirty('password')) {
                unset($entity->password_confirm);
                unset($entity->password);
                return true;
            }
            $ret = $entity->verifyPassword($entity->password_confirm);
            unset($entity->password_confirm);
            return $ret;
        }, ['errorField' => 'password_confirm', 'message' => __('The password confirm does not match with password.')]
        );
        return $rules;
    }

    public function getStatusList() {
        return [
            ACTIVE => __('Active'),
            INACTIVE => __('Temporary Locked'),
            AdminUser::STATUS_SUSPEND => __('Suspended'),
        ];
    }

    public function beforeMarshal(Event $event, $data) {
        if (isset($data['email']) && empty($data['email'])) {
            unset($data['email']);
        }
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
    }

}
