# TreeHelper
## Static class used to build tree of parent-child relations

#### install:
```
composer require kvelaro/tree-helper
```

#### ex:
```
TreeHelper::makeTree($rows, 'id', 'parent_id'); //build tree
$tree = TreeHelper::getTree(); //get tree
$root = null;
TreeHelper::iterateDS($root, null, function(&$node, $parentNode) {
    ...
} //iterate through items (Depth-Search)
``` 

