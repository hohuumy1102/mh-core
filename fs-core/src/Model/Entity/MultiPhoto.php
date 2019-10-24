<?php

namespace FsCore\Model\Entity;

use Cake\ORM\Entity;
use FsCore\Utility\Utils;

class MultiPhoto extends Entity {

    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

}
