<?php

namespace FsCore\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use FsCore\Utility\Utils;

class FsTable extends Table {

    protected $multiLanguages = [];
    protected $singlePhotos = [];
    protected $multiPhotos = [];
    protected $hasOneRelations = [];
    protected $hasManyRelations = [];
    protected $belongRelations = [];
    protected $deleteRelationship = true;

    public function initialize(array $config) {
        parent::initialize($config);
        $languageList = Configure::read('LanguageList');
        $tableType = $this->getTableType();

        if (!empty($this->multiLanguages)) {
            foreach ($this->multiLanguages as $field) {
                $upper = ucfirst($field);
                foreach ($languageList as $languageCode => $languageLabel) {
                    $this->hasOne("{$upper}{$languageLabel}", [
                        'className' => 'FsCore.LanguageContents',
                        'foreignKey' => 'target_id',
                        'conditions' => [
                            "{$upper}{$languageLabel}.language" => $languageCode,
                            "{$upper}{$languageLabel}.target_type" => $tableType,
                            "{$upper}{$languageLabel}.field" => $field,
                        ]
                    ]);
                }
            }
        }

        if (!empty($this->singlePhotos)) {
            foreach ($this->singlePhotos as $field => $name) {
                $name = ucfirst($name);
                $this->belongsTo($name, [
                    'className' => 'FsCore.Photos',
                    'foreignKey' => $field,
                ]);
            }
        }

        if (!empty($this->multiPhotos)) {
            foreach ($this->multiPhotos as $field => $name) {
                $name = ucfirst($name);
                $this->hasMany($name, [
                    'className' => 'FsCore.MultiPhotos',
                    'foreignKey' => 'target_id',
                    'conditions' => [
                        'Galleries.target_type' => $tableType,
                        'Galleries.field' => $field,
                    ]
                ]);
            }
        }

        if (!empty($this->hasOneRelations)) {
            foreach ($this->hasOneRelations as $field => $name) {
                $name = ucfirst($name);
                $this->hasOne($name, [
                    'className' => $name,
                    'foreignKey' => $field,
                ]);
            }
        }

        if (!empty($this->hasManyRelations)) {
            foreach ($this->hasManyRelations as $field => $name) {
                $name = ucfirst($name);
                $this->hasMany($name, [
                    'className' => $name,
                    'foreignKey' => $field,
                ]);
            }
        }

        if (!empty($this->belongRelations)) {
            foreach ($this->belongRelations as $field => $name) {
                $name = ucfirst($name);
                $this->belongsTo($name, [
                    'className' => $name,
                    'foreignKey' => $field,
                ]);
            }
        }
    }

    protected function deleteMultiLang($id) {
        if (empty($id) || empty($this->multiLanguages)) {
            return false;
        }
        $conditions = [
            'LanguageContents.target_type' => $this->getTableType(),
        ];
        if (is_array($id)) {
            $conditions['LanguageContents.target_id IN'] = $id;
        } else {
            $conditions['LanguageContents.target_id'] = $id;
        }
        Utils::useTables($this, ['LanguageContents']);
        $this->LanguageContents->deleteAll($conditions);
        return true;
    }

    protected function deleteSinglePhotos($id) {
        if (empty($id) || empty($this->singlePhotos)) {
            return false;
        }
        $conditions = [];
        if (is_array($id)) {
            $conditions['id IN'] = $id;
        } else {
            $conditions['id'] = $id;
        }
        $recordList = $this->find('all', [
                    'conditions' => $conditions,
                ])->toArray();
        $idList = [];
        foreach ($recordList as $record) {
            foreach ($this->singlePhotos as $field => $name) {
                if (!empty($record->$field)) {
                    $idList[] = $record->$field;
                }
            }
        }
        if (!empty($idList)) {
            Utils::useTables($this, ['Photos']);
            $this->Photos->deleteAll([
                'Photos.id IN' => $idList,
            ]);
        }
        return true;
    }

    protected function deleteMultiPhotos($id) {
        if (empty($id) || empty($this->multiPhotos)) {
            return false;
        }
        $conditions = [
            'MultiPhotos.target_type' => $this->getTableType(),
        ];
        if (is_array($id)) {
            $conditions['MultiPhotos.target_id IN'] = $id;
        } else {
            $conditions['MultiPhotos.target_id'] = $id;
        }
        Utils::useTables($this, ['MultiPhotos']);
        $this->MultiPhotos->deleteAll($conditions);
        return true;
    }

    protected function deleteHasOne($id) {
        if (empty($id) || empty($this->hasOneRelations)) {
            return false;
        }
        foreach ($this->hasOneRelations as $field => $name) {
            $conditions = [];
            if (is_array($id)) {
                $conditions["$field IN"] = $id;
            } else {
                $conditions[$field] = $id;
            }
            Utils::useTables($this, [$name]);
            $this->$name->deleteAll($conditions);
        }
        return true;
    }

    protected function deleteHasMany($id) {
        if (empty($id) || empty($this->hasManyRelations)) {
            return false;
        }
        foreach ($this->hasManyRelations as $field => $name) {
            $conditions = [];
            if (is_array($id)) {
                $conditions["$field IN"] = $id;
            } else {
                $conditions[$field] = $id;
            }
            Utils::useTables($this, [$name]);
            $this->$name->deleteAll($conditions);
        }
        return true;
    }

    protected function getTableType() {
        $name = get_class($this);
        $sections = explode('\\', $name);
        for ($i = count($sections) - 1; $i >= 0; $i--) {
            if (!empty($sections[$i])) {
                $name = $sections[$i];
                break;
            }
        }

        return str_replace('Table', '', $name);
    }

    public function beforeDelete(Event $event, EntityInterface $entity, ArrayObject $options) {
        if (!empty($entity->id) && $this->deleteRelationship) {
            $this->deleteMultiLang($entity->id);
            $this->deleteSinglePhotos($entity->id);
            $this->deleteMultiPhotos($entity->id);
            $this->deleteHasOne($entity->id);
            $this->deleteHasMany($entity->id);
        }
    }

    public function deleteAll($conditions) {
        $recordList = $this->find('all', [
                    'conditions' => $conditions,
                ])->toArray();
        $idList = [];
        foreach ($recordList as $record) {
            $idList[] = $record->id;
        }
        if ($this->deleteRelationship) {
            $this->deleteMultiLang($idList);
            $this->deleteSinglePhotos($idList);
            $this->deleteMultiPhotos($idList);
            $this->deleteHasOne($idList);
            $this->deleteHasMany($idList);
        }
        return parent::deleteAll($conditions);
    }

}
