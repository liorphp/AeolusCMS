<?php
namespace AeolusCMS\Wrappers;

use Envms\FluentPDO\Query;
use Envms\FluentPDO\Structure;

class AeolusFluentPDO extends Query {
    public $convertWrite = true;

    function __construct(\PDO $pdo, Structure $structure = null) {
        parent::__construct($pdo, $structure);
    }
}
