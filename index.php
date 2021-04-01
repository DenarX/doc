<?php
include 'doc.php';

// disable all options
/* $doc = new doc(['doc', 'pdo'], [
    'classHide' => [],
    'methodHide' => [],
    'commentFormat' => false,
    'valueFormat' => false,
    'onlyPublic' => false,
    'methodOnly' => false,
    'varHide' => false,
    'typeHide' => false,
    'paramFormat' => false,
    'htmlUseH' => false,
]); */

$doc = new doc(['doc', 'pdo']);
echo $doc->toHtml();
