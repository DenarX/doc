# Doc - Class inspector

Get all info about class from doc block comments using Reflector classes in PHP
Can be used for incpect any libraries, for generation documentation and incentive to write informational comments.

#### Usage

### static

```
doc::init();
include 'someclass.php';
echo '<pre>' . print_r(doc::toArray(), true) . '</pre>';
```

### object

```
$doc = new doc(['doc','pdo']); //$doc = new doc('doc');
echo $doc->toHtml();
```

## OR with options (all data and without formatting)

### static

```
doc::init([
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
]);
include 'someclass.php';
echo '<pre>' . print_r(doc::toJson(), true) . '</pre>';
```

### object

```
$doc = new doc(['doc','pdo'],[
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
]);
echo $doc->toHtml();
```

#### Result

```
<?php
include 'doc.php';
$doc = new doc(['doc','pdo']); //$doc = new doc('doc');
echo $doc->toHtml();
```

-   doc:
    -   description: Class inspector
    -   const:
        -   description: All supported options and defaults
        -   value: ['classHide'=>[], 'methodHide'=>['__construct', '__destruct', '__get'], 'commentFormat'=>true, 'valueFormat'=>true, 'onlyPublic'=>true, 'methodOnly'=>true, 'varHide'=>true, 'typeHide'=>true, 'paramFormat'=>true, 'htmlUseH'=>false]
    -   init($classes=[]): Get declared classes
    -   toString(): used for export inbuilt method of ReflectionClass toString()
    -   toArray(): Exporting all classes information in assoc array
    -   toHtml(): Generate html unordered list
    -   toJson(): Generate JSON
-   pdo:
    -   const:
        -   description:
        -   value: 1002
    -   prepare($options):
    -   beginTransaction():
    -   commit():
    -   rollBack():
    -   inTransaction():
    -   setAttribute($value):
    -   exec($query):
    -   query():
    -   lastInsertId($seqname):
    -   errorCode():
    -   errorInfo():
    -   getAttribute($attribute):
    -   quote($paramtype):
    -   getAvailableDrivers():
