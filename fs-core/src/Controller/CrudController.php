<?php

namespace FsCore\Controller;

use FsCore\Utility\Utils;
use FsCore\Controller\FsBackendController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Date;
use Cake\ORM\Entity;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Validation\Validation;
use Sluggable\Utility\Slug;

abstract class CrudController extends FsBackendController {

    protected $slug = false;
    protected $model = null;
    protected $modelName = false;
    protected $invalidActions = [];
    protected $listUnsetFields = [];
    protected $unsetFields = [];
    protected $toggleFields = [];
    protected $multiLangFields = [];
    protected $hasOrder = [];
    protected $hasSeo = false;
    protected $hasListSeo = false;
    protected $activationFields = [];
    protected $filterFields = [];
    protected $searchingFields = [];
    protected $dateTimeFilterField = false;
    protected $singlePhotos = [];
    protected $multiPhotos = [];

    public function initialize() {
        parent::initialize();
        $action = $this->request->params['action'];
        if (!empty($this->invalidActions) && in_array($action, $this->invalidActions)) {
            return $this->showInvalidAction();
        }
        $id = false;
        if (($action == 'view' || $action == 'edit' || $action == 'move' || $action == 'seo') && !empty($this->request->params['pass'])) {
            $id = $this->request->params['pass'][0];
        }
        Utils::useTables($this, ['FsCore.LanguageContents', 'FsCore.Photos']);
        $navList = $this->_mainNav($action, $id);
        $navList = $this->_filterActionPermission($navList);
        $this->set('navList', $navList);
    }

    public function index() {
        foreach ($this->activationFields as $field => $options) {
            $this->filterFields[$field] = [
                'options' => $options,
            ];
        }
        foreach ($this->toggleFields as $field) {
            $this->filterFields[$field] = [
                'options' => [
                    ACTIVE => __('Active'),
                    INACTIVE => __('Inactive'),
                ],
            ];
        }
        $conditions = [];
        $contains = [];
        foreach ($this->filterFields as $field => $fieldInfo) {
            $this->filterFields[$field]['currentValue'] = isset($this->request->query[$field]) ? $this->request->query[$field] : -1;
            if (isset($this->request->query[$field]) && $this->request->query[$field] != -1) {
                $conditions["{$this->modelName}.{$field}"] = $this->request->query[$field];
            }
        }
        $this->set('filterFields', $this->filterFields);

        $searchingConditions = [];
        if ($this->searchingFields && !empty($this->request->query['keyword'])) {
            $keyword = trim($this->request->query['keyword']);
            $keyword = mb_strtolower($keyword);
            foreach ($this->searchingFields as $field) {
                if (!empty($this->multiLangFields[$field])) {
                    $languageList = Configure::read('LanguageList');
                    foreach ($languageList as $languageCode => $languageLabel) {
                        $className = ucfirst($field) . $languageLabel;
                        $contains[] = $className;
                        $searchingConditions["{$className}.content LIKE"] = "%{$keyword}%";
                        $this->listUnsetFields[] = strtolower($field . '_' . $languageLabel);
                    }
                } else {
                    $searchingConditions["{$this->modelName}.{$field} LIKE"] = "%{$keyword}%";
                }
            }
        }
        if (!empty($searchingConditions)) {
            if (empty($conditions['OR'])) {
                $conditions['OR'] = [];
            }
            $conditions['OR'] = array_merge($conditions['OR'], $searchingConditions);
        }
        $this->set('searchingFields', $this->searchingFields);

        if ($this->dateTimeFilterField && !empty($this->request->query['date_time'])) {
            $dateTime = $this->request->query['date_time'];
            list($fromDate, $toDate) = explode(' - ', $dateTime);
            $fromDate = new Date($fromDate);
            $fromDate = $fromDate->format('Y-m-d H:i:s');
            $toDate = new Date($toDate);
            $toDate = $toDate->format('Y-m-d H:i:s');
            $conditions["{$this->modelName}.{$this->dateTimeFilterField} >="] = $fromDate;
            $conditions["{$this->modelName}.{$this->dateTimeFilterField} <="] = $toDate;
        }
        $this->set('dateTimeFilterField', $this->dateTimeFilterField);

        $records = $this->getAllObjects($contains, $conditions);
        $unsetFields = array_merge($this->listUnsetFields, $this->unsetFields, ['display_order', 'modified']);
        $objectList = [];
        foreach ($records as $record) {
            $object = [];
            if (empty($record->display_field) && !empty($this->multiLangFields)) {
                $langKeys = array_keys($this->multiLangFields);
                $firstField = array_shift($langKeys);
                $object[$firstField] = $this->_getObjectDisplayField($record);
            }
            $object = array_merge($object, $record->toArray());
            foreach ($unsetFields as $field) {
                unset($object[$field]);
            }
            if (!empty($this->singlePhotos)) {
                foreach ($this->singlePhotos as $field => $fieldInfo) {
                    unset($object[$field . '_id']);
                }
            }
            $actionList = $this->_setActions($record);
            $object['actions'] = $this->_filterActionPermission($actionList);
            $objectList[] = $object;
        }
        $this->set('activationFields', $this->activationFields);

        $this->set('objectList', $objectList);
        $this->render('FsCore.Element/Crud/list_view');
    }

    public function add() {
        $this->createUpdate();
    }

    public function edit($id) {
        $this->createUpdate($id);
    }

    protected function createUpdate($id = false) {
        if ($id) {
            $object = $this->getObject($id);
            if (empty($object)) {
                $this->Flash->error(__('Data cannot found.'), ['plugin' => 'FsCore']);
                return $this->redirect(['action' => 'index']);
            }
        } else {
            $object = $this->model->newEntity();
        }
        $errors = [];
        if ($this->request->is('post') || $this->request->is('put')) {
            $submitData = $this->request->data;
            $object = $this->model->patchEntity($object, $submitData);
            $this->_handleUploadPhoto($object, $errors);
            if (!empty($this->toggleFields)) {
                foreach ($this->toggleFields as $field) {
                    $object->$field = !empty($submitData[$field]);
                }
            }
            $this->validateMultiLanguageFields($submitData, $errors);
            $this->validateMultiPhotos($submitData, $errors);
            $photoIds = [];
            if (!empty($this->multiPhotos)) {
                Utils::useTables($this, ['MultiPhotos']);
                foreach ($this->multiPhotos as $field => $photoInfo) {
                    if (!empty($submitData[$field . '_photo'])) {
                        $photoIds = array_merge($photoIds, $submitData[$field . '_photo']);
                    }
                }
                if (!empty($photoIds)) {
                    $uploadingPhotos = $this->MultiPhotos->find('all', [
                                'conditions' => [
                                    'id IN ' => $photoIds,
                                    'target_type' => $this->modelName,
                                ],
                            ])->toArray();
                    $this->set('uploadingPhotos', $uploadingPhotos);
                }
            }

            if (empty($errors) && $this->model->save($object)) {
                if (!empty($this->multiLangFields)) {
                    $languageList = Configure::read('LanguageList');
                    foreach ($languageList as $languageCode => $languageLabel) {
                        foreach ($this->multiLangFields as $field => $fieldInfo) {
                            $value = !empty($submitData['language_' . $languageCode . '_' . $field]) ? $submitData['language_' . $languageCode . '_' . $field] : false;
                            $languageContent = $this->LanguageContents->updateLanguageContent($object->id, $this->modelName, $languageCode, $field, $value);
                            if (!empty($this->slug) && $this->slug == $field && !empty($languageContent)) {
                                Utils::useTables($this, ['FsCore.Slugs']);
                                $slug = Slug::generate(":content", $languageContent);
                                $this->Slugs->updateSlug($object->id, $this->modelName, $languageCode, $slug);
                            }
                        }
                    }
                } else {
                    $languageCode = Configure::read('DefaultLanguage');
                    if (!empty($this->slug) && !empty($object->{$this->slug})) {
                        Utils::useTables($this, ['FsCore.Slugs']);
                        $slug = Slug::generate(":{$this->slug}", $object);
                        $this->Slugs->updateSlug($object->id, $this->modelName, $languageCode, $slug);
                    }
                }

                if (!empty($this->multiPhotos) && !empty($photoIds)) {
                    Utils::useTables($this, ['MultiPhotos']);
                    foreach ($photoIds as $photoId) {
                        $updateData = [
                            'target_id' => $object->id,
                            'target_type' => $this->modelName,
                        ];

                        if (!empty($submitData['thumbnail_photo_title'][$photoId])) {
                            $updateData['title'] = $submitData['thumbnail_photo_title'][$photoId];
                        }

                        if (!empty($submitData['thumbnail_photo_description'][$photoId])) {
                            $updateData['description'] = $submitData['thumbnail_photo_description'][$photoId];
                        }

                        $this->MultiPhotos->updateAll($updateData, [
                            'id' => $photoId
                        ]);
                    }
                }
                if (!empty($this->hasOrder) && empty($object->display_order)) {
                    $object->display_order = $object->id;
                    $this->model->save($object);
                }
                $this->Flash->success(__('The data has been saved.'), ['plugin' => 'FsCore']);
                return $this->redirect(['action' => 'index']);
            }
            $errors = array_merge($errors, $object->errors());

            if (!empty($this->singlePhotos)) {
                foreach ($this->singlePhotos as $field => $photoInfo) {
                    if (!empty($errors[$field])) {
                        if (empty($errors[$field . '_photo'])) {
                            $errors[$field . '_photo'] = $errors[$field];
                        }
                        unset($errors[$field]);
                    }
                }
            }
            $this->Flash->error(__('The data could not be saved. Please, try again.'), ['plugin' => 'FsCore']);
        }
        $ret = $this->_prepareObject($object);
        if (!$ret) {
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('object', 'errors'));
        $this->set('_serialize', ['object']);
        $this->render('FsCore.Element/Crud/create_update_view');
    }

    protected function validateMultiLanguageFields($submitData, &$errors) {
        if (!empty($this->multiLangFields)) {
            $languageList = Configure::read('LanguageList');
            foreach ($this->multiLangFields as $field => $fieldInfo) {
                if (!empty($fieldInfo['validation']) && is_array($fieldInfo['validation'])) {
                    foreach ($fieldInfo['validation'] as $validateAction => $validationInfo) {
                        if (is_array($validationInfo) && isset($validationInfo['errorMsg'])) {
                            $errorStr = $validationInfo['errorMsg'];
                        } elseif (is_string($validationInfo)) {
                            $errorStr = $validationInfo;
                        }
                        foreach ($languageList as $languageCode => $languageLabel) {
                            $value = !empty($submitData['language_' . $languageCode . '_' . $field]) ? $submitData['language_' . $languageCode . '_' . $field] : false;
                            $ret = true;
                            if (method_exists('Cake\\Validation\\Validation', $validateAction)) {
                                $validateValue = is_array($validationInfo) && isset($validationInfo['validationValue']) ? $validationInfo['validationValue'] : false;
                                $ret = Validation::$validateAction($value, $validateValue);
                            }
                            if (!$ret && !empty($errorStr)) {
                                $errors['language_' . $languageCode . '_' . $field] = [__($errorStr)];
                            }
                        }
                    }
                }
            }
        }
    }

    protected function validateMultiPhotos($submitData, &$errors) {
        if (!empty($this->multiPhotos)) {
            foreach ($this->multiPhotos as $field => $photoInfo) {
                if (!empty($photoInfo['isRequired']) && empty($submitData[$field . '_photo'])) {
                    $errors[$field] = [__('Please upload ') . ucwords(str_replace('_', ' ', $field))];
                }
            }
        }
    }

    public function view($id) {
        $contain = [];
        if (!empty($this->singlePhotos)) {
            foreach ($this->singlePhotos as $field => $photoInfo) {
                $contain[] = ucwords(Inflector::pluralize($field));
            }
        }
        $record = $this->getObject($id, $contain, true);
        if (empty($record)) {
            $this->Flash->error(__('Data cannot found.'), ['plugin' => 'FsCore']);
            return $this->redirect(['action' => 'index']);
        }
        $object = [];
        $unsetFields = array_merge($this->unsetFields, ['display_order']);
        foreach ($record->toArray() as $key => $val) {
            if (is_array($val)) {
                if (!empty($this->multiPhotos && !empty($this->multiPhotos[Inflector::singularize($key)]))) {
                    $object[$key] = [
                        'value' => $val,
                        'type' => 'multi-photo',
                    ];
                } else {
                    $object[$key] = $val;
                }
            } elseif (!in_array($key, $unsetFields)) {
                $object[$key] = [
                    'value' => $val,
                ];
                if (!empty($this->singlePhotos && !empty($this->singlePhotos[$key]))) {
                    $object[$key]['type'] = 'photo';
                }
            }
        }
        $this->set('multiLangFields', $this->multiLangFields);
        $this->set(compact('object'));
        $this->set('_serialize', ['object']);
        $this->render('FsCore.Element/Crud/detail_view');
    }

    public function delete($id) {
        $object = $this->getObject($id);
        if (empty($object)) {
            $this->Flash->error(__('Data cannot found.'), ['plugin' => 'FsCore']);
            return $this->redirect(['action' => 'index']);
        }
        $this->model->delete($object);
        $this->Flash->success(__('The data has been deleted.'), ['plugin' => 'FsCore']);
        return $this->redirect(['action' => 'index']);
    }

    public function activate($id, $field, $value) {
        if (!$this->request->is('ajax')) {
            return $this->redirect(['action' => 'index']);
        }
        if (empty($this->activationFields)) {
            $this->AsyncResponse->run("showAlert('" . __('You do not have permission for this feature.') . "');");
            return $this->sendAsyncResponse();
        }
        if ($this->stopAjax) {
            return $this->sendAsyncResponse();
        }

        $object = $this->getObject($id);
        if (empty($object) || !isset($object->$field) || empty($this->activationFields[$field]) || empty($this->activationFields[$field][$value])) {
            $this->AsyncResponse->run("showAlert('" . __('Data cannot found.') . "');");
        } else {
            $object->$field = $value;
            $this->model->save($object);
            $this->AsyncResponse->run("$('#{$id}-{$field}-btn .current-selection').html('{$this->activationFields[$field][$value]}');");
            $this->AsyncResponse->run("$('#{$id}-{$field}-btn li').show();");
            $this->AsyncResponse->run("$('#{$id}-{$field}-{$value}').hide();");
            $this->AsyncResponse->run("showAlert('" . __('The data has been saved.') . "');");
        }
        return $this->sendAsyncResponse();
    }

    public function move($id) {
        if (empty($this->hasOrder)) {
            $this->Flash->error(__('You do not have permission for this feature.'), ['plugin' => 'FsCore']);
            return $this->redirect(['action' => 'index']);
        }
        $object = $this->getObject($id);
        if (empty($object)) {
            $this->Flash->error(__('Data cannot found.'), ['plugin' => 'FsCore']);
            return $this->redirect(['action' => 'index']);
        }
        if (!empty($this->multiLangFields) && empty($object->display_field)) {
            $object->set('display_field', $this->_getObjectDisplayField($object));
        }

        $sameLevelObjects = $this->_getSameLevelObject($object);
        if (count($sameLevelObjects) <= 1) {
            $this->Flash->error(__('There is only 1 item in the list. Cannot move!'), ['plugin' => 'FsCore']);
            return $this->redirect(['action' => 'index']);
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            $submitData = $this->request->data;
            $targetObject = $this->getObject($submitData['target_id']);
            if (empty($targetObject)) {
                $this->Flash->error(__('Data cannot found.'), ['plugin' => 'FsCore']);
            } elseif ($submitData['position'] < 0) {
                for ($i = 0; $i < count($sameLevelObjects); $i++) {
                    if ($sameLevelObjects[$i]->display_order >= $targetObject->display_order) {
                        break;
                    }
                    $nextDisplayOrder = $sameLevelObjects[$i]->display_order;
                }
            } else {
                for ($i = count($sameLevelObjects) - 1; $i >= 0; $i++) {
                    if ($sameLevelObjects[$i]->display_order <= $targetObject->display_order) {
                        break;
                    }
                    $nextDisplayOrder = $sameLevelObjects[$i]->display_order;
                }
            }
            if (isset($nextDisplayOrder)) {
                $object->display_order = ($nextDisplayOrder + $targetObject->display_order) / 2;
            } elseif ($submitData['position'] < 0) {
                $object->display_order = $targetObject->display_order / 2;
            } else {
                $object->display_order = $targetObject->display_order + 0.5;
            }
            if ($this->model->save($object)) {
                $this->Flash->success(__('The data has been saved.'), ['plugin' => 'FsCore']);
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The data could not be saved. Please, try again.'), ['plugin' => 'FsCore']);
        }
        $this->set(compact('object', 'sameLevelObjects'));
        $this->set('_serialize', ['object']);
        $this->render('FsCore.Element/Crud/move_view');
    }

    protected function _handleUploadPhoto($object, &$errors) {
        Utils::useComponents($this, ['FsCore.Upload']);
        $destinationFolder = Configure::read('Upload.PhotoFolder');
        $this->Upload->setDestination($destinationFolder);
        if (!empty($this->singlePhotos)) {
            foreach ($this->singlePhotos as $field => $photoInfo) {
                if (empty($_FILES[$field . '_photo']['name']) && (empty($photoInfo['isRequired']) || !empty($object->{$field . '_id'}))) {
                    continue;
                }
                $ret = $this->Upload->handleUpload($field . '_photo');

                $photoError = [];
                if (!empty($ret['error'])) {
                    $photoError[] = $ret['error'];
                } else {
                    $photoPath = $destinationFolder . $ret['file'];
                    $photoSize = @getimagesize($photoPath);
                    $width = !empty($photoSize[0]) ? $photoSize[0] : 0;
                    $height = !empty($photoSize[1]) ? $photoSize[1] : 0;
                    if (!empty($photoInfo['width']) && $width < $photoInfo['width']) {
                        $photoError[] = __('Minimum width is ') . $photoInfo['width'] . ' px';
                    }
                    if (!empty($photoInfo['height']) && $height < $photoInfo['height']) {
                        $photoError[] = __('Minimum height is ') . $photoInfo['height'] . ' px';
                    }
                    if (!empty($photoInfo['width']) && !empty($photoInfo['height']) && !empty($photoInfo['fixRatio'])) {
                        if (($photoInfo['width'] / $photoInfo['height']) != ($width / $height)) {
                            $photoError[] = __('Photo size must be ') . $photoInfo['width'] . ' x ' . $photoInfo['height'];
                        }
                    }
                }
                if (!empty($photoError)) {
                    $errors[$field . '_photo'] = $photoError;
                } elseif (!empty($photoPath)) {
                    $photo = $this->Photos->newEntity([
                        'path' => str_replace(WWW_ROOT, '', $photoPath),
                    ]);
                    $this->Photos->save($photo);
                    $object->{$field . '_id'} = $photo->id;
                }
            }
        }
    }

    protected function _mainNav($page = 'index', $id = false) {
        $navList = [];
        if ($page == 'index') {
            if (!empty($this->hasListSeo)) {
                $navList['seo'] = [
                    'url' => Router::url(['action' => 'seo', 0]),
                    'label' => 'SEO Keyword',
                    'icon' => 'pencil'
                ];
            }
            $navList['add'] = [
                'url' => Router::url(['action' => 'add']),
                'label' => 'Add New',
                'icon' => 'plus'
            ];
        } else {
            $navList['index'] = [
                'url' => Router::url(['action' => 'index']),
                'label' => 'List',
                'icon' => 'list-alt'
            ];
        }
        if (!empty($id) && ($page == 'view' || $page == 'seo')) {
            $navList['edit'] = [
                'url' => Router::url(['action' => 'edit', $id]),
                'label' => 'Edit',
                'icon' => 'edit'
            ];
        }
        if (!empty($id) && ($page == 'edit' || $page == 'seo')) {
            $navList['view'] = [
                'url' => Router::url(['action' => 'view', $id]),
                'label' => 'View',
                'icon' => 'zoom-in'
            ];
        }
        if (!empty($id)) {
            if (!empty($this->hasSeo) && $page != 'seo') {
                $navList['seo'] = [
                    'url' => Router::url(['action' => 'seo', $id]),
                    'label' => 'SEO Keyword',
                    'icon' => 'pencil'
                ];
            }
            if (!empty($this->hasOrder) && $page != 'move') {
                $navList['move'] = [
                    'url' => Router::url(['action' => 'move', $id]),
                    'label' => 'Move',
                    'icon' => 'move'
                ];
            }
            $navList['delete'] = [
                'url' => Router::url(['action' => 'delete', $id]),
                'label' => 'Delete',
                'icon' => 'trash'
            ];
        }
        if (!empty($this->invalidActions)) {
            foreach ($this->invalidActions as $action) {
                unset($navList[$action]);
            }
        }
        return $navList;
    }

    protected function _setActions($record) {
        $id = $record->id;
        $actions = [
            'edit' => [
                'url' => Router::url(['action' => 'edit', $id]),
                'button' => 'success',
                'label' => 'Edit',
                'icon' => 'edit'
            ],
            'view' => [
                'url' => Router::url(['action' => 'view', $id]),
                'button' => 'success',
                'label' => 'View',
                'icon' => 'zoom-in'
            ],
            'delete' => [
                'url' => Router::url(['action' => 'delete', $id]),
                'button' => 'danger',
                'label' => 'Delete',
                'icon' => 'trash'
            ],
        ];
        if (!empty($this->hasSeo)) {
            $actions['seo'] = [
                'url' => Router::url(['action' => 'seo', $id]),
                'label' => 'SEO Keyword',
                'button' => 'info',
                'icon' => 'pencil'
            ];
        }
        if (!empty($this->hasOrder)) {
            $actions['move'] = [
                'url' => Router::url(['action' => 'move', $id]),
                'label' => 'Move',
                'button' => 'info',
                'icon' => 'move'
            ];
        }
        if (!empty($this->invalidActions)) {
            foreach ($this->invalidActions as $action) {
                unset($actions[$action]);
            }
        }
        return $actions;
    }

    protected function _prepareCommonObject(Entity $object) {
        $inputTypes = [];
        if (!empty($this->toggleFields)) {
            foreach ($this->toggleFields as $field) {
                $inputTypes[$field] = [
                    'input' => 'checkbox_toggle',
                    'label' => $field,
                    'currentValue' => !empty($object) && !empty($object->$field) ? $object->$field : false,
                ];
            }
        }
        if (!empty($this->activationFields)) {
            foreach ($this->activationFields as $field => $options) {
                $inputTypes[$field] = [
                    'input' => 'dropdown',
                    'label' => $field,
                    'options' => $options,
                    'currentValue' => !empty($object) && !empty($object->$field) ? $object->$field : false,
                ];
            }
        }

        if (!empty($this->singlePhotos)) {
            foreach ($this->singlePhotos as $field => $photoInfo) {
                if (!empty($object->{$field . '_id'})) {
                    $photo = $this->Photos->get($object->{$field . '_id'});
                    $inputTypes[$field] = [
                        'input' => 'photo',
                        'label' => 'Current ' . $field,
                        'currentValue' => !empty($photo) ? $photo : false,
                    ];
                }
                $inputTypes[$field . '_photo'] = array_merge($photoInfo, [
                    'input' => 'upload',
                    'label' => $field,
                    'currentValue' => false,
                ]);
            }
        }
        if (!empty($this->multiPhotos)) {
            Utils::useTables($this, ['FsCore.MultiPhotos']);
            foreach ($this->multiPhotos as $field => $photoInfo) {
                $pluralField = Inflector::pluralize($field);
                $inputTypes[$field] = [
                    'input' => 'multi-photo',
                    'label' => $field,
                    'currentValue' => !empty($object->$pluralField) ? $object->$pluralField : false,
                ];

                if (!empty($photoInfo['additionFields'])) {
                    $inputTypes[$field]['additionFields'] = $photoInfo['additionFields'];
                }
            }
        }
        if (!empty($this->multiLangFields)) {
            $languageList = Configure::read('LanguageList');
            $multiLangInputTypes = [];
            foreach ($languageList as $languageCode => $languageLabel) {
                foreach ($this->multiLangFields as $field => $fieldInfo) {
                    $fieldInfo['currentValue'] = false;
                    $fieldStr = 'language_' . $languageCode . '_' . $field;
                    $multiLangInputTypes[$languageCode][$fieldStr] = $fieldInfo;
                    if (!empty($object)) {
                        if (!empty($object->$fieldStr)) {
                            $multiLangInputTypes[$languageCode][$fieldStr]['currentValue'] = $object->$fieldStr;
                        } elseif (!empty($object->id)) {
                            $multiLangInputTypes[$languageCode][$fieldStr]['currentValue'] = $this->_parseMultiLangField($object->id, $languageCode, $field);
                        }
                    }
                }
            }
            $this->set('multiLangInputTypes', $multiLangInputTypes);
        }
        return $inputTypes;
    }

    protected abstract function _prepareObject(Entity $object);

    protected function getObject($id = null, $contain = [], $parsed = false) {
        $object = null;
        if (!empty($id)) {
            if (!empty($this->multiPhotos)) {
                foreach ($this->multiPhotos as $field => $photoInfo) {
                    $contain[] = ucwords(Inflector::pluralize($field));
                }
            }
            $object = $this->model->findById($id);
            if (!empty($contain)) {
                $object->contain($contain);
            }
            $object = $object->first();
            if (!empty($object) && $parsed) {
                if (!empty($this->toggleFields)) {
                    foreach ($this->toggleFields as $field) {
                        $object->$field = [
                            'type' => 'box',
                            'class' => 'label label-' . (!empty($record->$field) ? 'success' : 'danger'),
                            'value' => !empty($object->$field) ? __('On') : __('Off')
                        ];
                    }
                }

                if (!empty($this->multiLangFields)) {
                    $languageList = Configure::read('LanguageList');
                    foreach ($this->multiLangFields as $field => $fieldInfo) {
                        foreach ($languageList as $languageCode => $languageLabel) {
                            $fieldStr = 'language_' . $field . '_' . $languageLabel;
                            $object->$fieldStr = $this->_parseMultiLangField($id, $languageCode, $field);
                        }
                    }
                }
                if (!empty($this->hasOrder)) {
                    $sameLevelObjects = $this->_getSameLevelObject($object);
                    $object->previous_item = false;
                    $object->next_item = false;
                    foreach ($sameLevelObjects as $relatedObject) {
                        if ($relatedObject->display_order < $object->display_order) {
                            $object->previous_item = $relatedObject->display_field;
                        } elseif ($relatedObject->display_order > $object->display_order) {
                            $object->next_item = $relatedObject->display_field;
                            break;
                        }
                    }
                }
                if (!empty($this->singlePhotos)) {
                    foreach ($this->singlePhotos as $field => $photoInfo) {
                        unset($object->{$field . '_id'});
                        if (!empty($object->$field)) {
                            $object->$field = $object->$field->path;
                        }
                    }
                }
            }
        }
        return $object;
    }

    protected function _getObjectDisplayField(Entity $object) {
        if (!empty($object) && empty($object->display_field) && !empty($this->multiLangFields)) {
            $langKeys = array_keys($this->multiLangFields);
            $firstField = array_shift($langKeys);
            return $this->_parseMultiLangField($object->id, Configure::read('DefaultLanguage'), $firstField);
        }
        return false;
    }

    protected function getAllObjects($contain = [], $conditions = []) {
        $this->paginate = [
            'order' => [
                $this->modelName . '.id' => 'desc'
            ],
            'contain' => $contain,
        ];
        if (!empty($conditions)) {
            $this->paginate['conditions'] = $conditions;
        }

        $records = $this->paginate($this->model)->toArray();
        $totalRecords = $this->request->params['paging'][$this->modelName]['count'];
        $this->set('totalRecords', $totalRecords);
        foreach ($records as $record) {
            if (!empty($this->toggleFields)) {
                foreach ($this->toggleFields as $field) {
                    $record->$field = [
                        'type' => 'box',
                        'class' => 'label label-' . (!empty($record->$field) ? 'success' : 'danger'),
                        'value' => !empty($record->$field) ? __('On') : __('Off')
                    ];
                }
            }
        }
        return $records;
    }

    protected function _parseMultiLangField($targetId, $languageCode, $field) {
        $languageContent = $this->LanguageContents->find('all', [
                    'conditions' => [
                        'target_id' => $targetId,
                        'target_type' => $this->modelName,
                        'language' => $languageCode,
                        'field' => $field,
                    ],
                ])->first();
        if (!empty($languageContent)) {
            return $languageContent->content;
        }
        return false;
    }

    protected function _getSameLevelObject(Entity $object) {
        if (empty($this->hasOrder) || empty($object)) {
            return [];
        }
        $conditions = [];
        foreach ($this->hasOrder['filter'] as $field) {
            if (isset($object->$field)) {
                $conditions[$field] = $object->$field;
            }
        }
        $sameLevelObjects = $this->model->find('all', [
                    'conditions' => $conditions,
                    'order' => [
                        'display_order' => 'asc',
                    ],
                ])->toArray();

        foreach ($sameLevelObjects as $record) {
            if (empty($record->display_field) && !empty($this->multiLangFields)) {
                $record->set('display_field', $this->_getObjectDisplayField($record));
            }
        }
        return $sameLevelObjects;
    }

    public function seo($id = 0) {
        if (($id === 0 && empty($this->hasListSeo)) || $id > 0 && empty($this->hasSeo)) {
            $this->Flash->error(__('You do not have permission for this feature.'), ['plugin' => 'FsCore']);
            return $this->redirect(['action' => 'index']);
        }
        if ($id > 0) {
            $object = $this->getObject($id);
            if (empty($object)) {
                $this->Flash->error(__('Data cannot found.'), ['plugin' => 'FsCore']);
                return $this->redirect(['action' => 'index']);
            }
        }
        Utils::useTables($this, ['FsCore.Seos']);
        $errors = [];
        $languageList = Configure::read('LanguageList');
        $seoList = $this->Seos->find('all', [
                    'conditions' => [
                        'target_id' => $id,
                        'target_type' => $this->modelName,
                    ],
                ])->toArray();
        $seoObjects = [];
        foreach ($seoList as $seo) {
            $seoObjects[$seo->language] = $seo;
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            $submitData = $this->request->data;
            Utils::useComponents($this, ['FsCore.Upload']);
            $destinationFolder = Configure::read('Upload.PhotoFolder');
            $this->Upload->setDestination($destinationFolder);
            $photoInfo = [
                'width' => 600,
                'height' => 315,
            ];
            foreach ($languageList as $languageCode => $languageLabel) {
                $photoError = [];
                if (!empty($_FILES['seo_data_' . $languageCode . '_thumbnail_photo']['name'])) {
                    $ret = $this->Upload->handleUpload('seo_data_' . $languageCode . '_thumbnail_photo');
                    if (!empty($ret['error'])) {
                        $photoError[] = $ret['error'];
                    } else {
                        $photoPath = $destinationFolder . $ret['file'];
                        $photoSize = @getimagesize($photoPath);
                        $width = !empty($photoSize[0]) ? $photoSize[0] : 0;
                        $height = !empty($photoSize[1]) ? $photoSize[1] : 0;
                        if (!empty($photoInfo['width']) && $width < $photoInfo['width']) {
                            $photoError[] = __('Minimum width is ') . $photoInfo['width'] . ' px';
                        }
                        if (!empty($photoInfo['height']) && $height < $photoInfo['height']) {
                            $photoError[] = __('Minimum height is ') . $photoInfo['height'] . ' px';
                        }
                    }
                }
                if (!empty($photoError)) {
                    $errors['seo_data'][$languageCode]['thumbnail_photo'] = $photoError;
                } elseif (!empty($photoPath)) {
                    $submitData['seo_data'][$languageCode]['thumbnail'] = str_replace(WWW_ROOT, '', $photoPath);
                }
                unset($submitData['seo_data'][$languageCode]['thumbnail_photo']);
                if (empty($seoObjects[$languageCode])) {
                    $seoObjects[$languageCode] = $this->Seos->newEntity([
                        'target_id' => $id,
                        'target_type' => $this->modelName,
                        'language' => $languageCode,
                        'content' => json_encode($submitData['seo_data'][$languageCode]),
                    ]);
                } else {
                    $seoObjects[$languageCode]->content = json_encode($submitData['seo_data'][$languageCode]);
                }
            }
            if (empty($errors)) {
                foreach ($seoObjects as $seo) {
                    $this->Seos->save($seo);
                }
            }
            $this->Flash->success(__('The data has been saved.'), ['plugin' => 'FsCore']);
            return $this->redirect(['action' => 'index']);
        }
        foreach ($seoObjects as $seo) {
            $seo->content = json_decode($seo->content, true);
        }
        $this->set('seoList', $seoObjects);

        $this->set(compact('object', 'errors'));
        $this->set('_serialize', ['object']);
        $this->render('FsCore.Element/Crud/seo_view');
    }

    public function uploadMultiPhotos($field) {
        if (!$this->request->is('ajax')) {
            return $this->redirect(['action' => 'index']);
        }
        $this->viewBuilder()->layout(false);
        $this->autoRender = false;
        $this->response->type('json');
        if ($this->stopAjax) {
            return null;
        }
        $html = false;
        if (!empty($this->multiPhotos)) {
            Utils::useComponents($this, ['FsCore.Upload']);
            $destinationFolder = Configure::read('Upload.MultiPhotoFolder');
            $this->Upload->setDestination($destinationFolder);
            $ret = $this->Upload->handleUpload('myfile');
            if (!empty($ret['success'])) {
                $path = Configure::read('Upload.MultiPhotoFolder') . $ret['file'];
                Utils::useTables($this, ['MultiPhotos']);
                $photo = $this->MultiPhotos->newEntity([
                    'path' => str_replace(WWW_ROOT, '', $path),
                    'field' => $field,
                    'target_id' => 0,
                    'target_type' => $this->modelName,
                ]);
                $this->MultiPhotos->save($photo);
                $view = new \Cake\View\View();
                $view->layout(false);
                $view->set('photo', $photo);
                $view->set('field', $field);
                $view->set('fieldInfo', $this->multiPhotos[$field]);
                $controller = $this->request->params['controller'];
                $view->set('controller', $controller);
                $html = $view->render('FsCore.Element/Crud/multi_photo_item');
            }
        }
        $this->response->body(json_encode([
            'html' => $html
        ]));
        return null;
    }

    public function removeMultiPhotos($photoId) {
        if (!$this->request->is('ajax')) {
            return $this->redirect(['action' => 'index']);
        }
        if ($this->stopAjax) {
            return $this->sendAsyncResponse();
        }
        if (!empty($this->multiPhotos)) {
            Utils::useTables($this, ['MultiPhotos']);
            $photo = $this->MultiPhotos->findById($photoId)->first();
            @unlink(WWW_ROOT . $photo->path);
            $this->MultiPhotos->delete($photo);
            $this->AsyncResponse->run("$('#multi_photo_{$photoId}').remove();");
        }
        return $this->sendAsyncResponse();
    }

    protected function _filterActionPermission($actionList) {
        Utils::useComponents($this, ['FsCore.AdminCommon']);
        if ($this->AdminCommon->checkSuperPermission()) {
            return $actionList;
        }
        $controllerList = Configure::read('ControllerList');
        $controller = $this->request->params['controller'];
        if (empty($controllerList[$controller])) {
            return $actionList;
        }
        $customActions = Configure::read('CustomAction');
        foreach ($actionList as $itemAction => $actionInfo) {
            if (isset($customActions[$controller][$itemAction]) && $customActions[$controller][$itemAction] === 0) {
                continue;
            }
            if (!$this->AdminCommon->checkPermission($controller, $itemAction)) {
                unset($actionList[$itemAction]);
            }
        }
        return $actionList;
    }

}
