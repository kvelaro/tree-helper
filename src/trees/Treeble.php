<?php

namespace Kvelaro\trees;

interface Treeble {

    public function makeTree(array $arrayOfObjects, $id = 'id', $parent_id = 'parent_id');

    public function addLeaf($leaf, $id  = 'id', $parent_id = 'parent_id', &$incomingNode = null);

    public function getTree();

    public function isEmpty();

    public function isNodeExist($fieldToSearch = 'id', $valueToSearch, $level = -1, $incomingNode = null);

    public function getNode($fieldToSearch = 'id', $valueToSearch, $level = -1, $incomingNode = null, $remove = false);

    public function appendNodes($arrayOfObjects, $id = 'id', $parent_id = 'parent_id');


//    public static function iterateDS(&$root = null, $parentNode = null, $callback, $depth = 0)

}