<?php
class TreeHelper {
    protected static $tree = [];
    protected static $flatNodes = [];

    public static function getTree() {
        return self::$tree;
    }

    public static function getArrayTree($root = null, $depth = 0) {
        $tree = [];
        if($root == null) {
            $root = [];
            if(empty(self::$tree) == false && isset(self::$tree['children']) == true) {
                $root = self::$tree['children'];
            }
        }

        foreach ($root as $node) {
            $currentLeaf = [
                'id' => $node['item']->id,
                'parent_id' => $node['item']->parent_id,
                'text' => $node['item']->title,
                'children' => []
            ];
            if(empty($node['children']) == false) {
                $currentLeaf['children'] = self::getArrayTree($node['children'], $depth + 1);
                $tree[] = $currentLeaf;
            }
            else {
                $tree[] = $currentLeaf;
            }
        }
        return $tree;
    }

    public static function isNodeExist($fieldToSearch = 'id', $valueToSearch, $level = -1, $incomingNode = null) {
        if($level == 0) {
            return false;
        }
        if($incomingNode == null) {
            $incomingNode = self::$tree['children'];
        }
        foreach ($incomingNode as $node) {
            if(is_object($node['item'])) {
                $nodeValue = $node['item']->$fieldToSearch;
            }
            else {
                $nodeValue = $node['item'];
            }
            if($nodeValue == $valueToSearch) {
                return true;
            }
            if(empty($node['children']) == false && ($level > 0 || $level == -1)) {
                if($level == -1) {
                    $result = self::isNodeExist($fieldToSearch, $valueToSearch, $level, $node['children']);
                }
                else {
                    $result = self::isNodeExist($fieldToSearch, $valueToSearch, $level - 1, $node['children']);
                }
                if($result == true) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function makeTree($arrayOfObjects, $id = 'id', $parent_id = 'parent_id', $trivial = false) {
        self::$tree = [];
        self::$flatNodes = [];
        foreach ($arrayOfObjects as $object) {
            $item = $object;
            if($trivial == true) {
                $item= $object->$id;
            }
            $leaf = [
                'item' => $object->$parent_id,
                'children' => [
                    [
                        'item' => $item,
                        'children' => []
                    ]
                ]
            ];
            TreeHelper::addLeaf($leaf, $id, $parent_id);
        }
    }

    public static function appendNodes($arrayOfObjects, $id = 'id', $parent_id = 'parent_id') {
        foreach ($arrayOfObjects as $object) {
            $leaf = [
                'item' => $object->$parent_id,
                'children' => [
                    [
                        'item' => $object,
                        'children' => []
                    ]
                ]
            ];
            TreeHelper::addLeaf($leaf, $id, $parent_id);
        }
    }

    public static function getNode($fieldToSearch = 'id', $valueToSearch, $level = -1, $incomingNode = null, $remove = false) {
        if($level == 0) {
            return false;
        }
        if($incomingNode == null) {
            $incomingNode = &self::$tree['children'];
        }
        foreach ($incomingNode as $nodeKey => &$node) {
            if(is_object($node['item'])) {
                $nodeValue = $node['item']->$fieldToSearch;
            }
            else {
                $nodeValue = $node['item'];
            }
            if($nodeValue == $valueToSearch) {
                if($remove == true) {
                    $tmpNode = $node;
                    unset($incomingNode[$nodeKey]);
                    return $tmpNode;
                }
                return $node;
            }
            if(empty($node['children']) == false && ($level > 0 || $level == -1)) {
                if($level == -1) {
                    $result = self::getNode($fieldToSearch, $valueToSearch, $level, $node['children'], $remove);
                }
                else {
                    $result = self::getNode($fieldToSearch, $valueToSearch, $level - 1, $node['children'], $remove);
                }
                if($result != false) {
                    return $result;
                }
            }
        }
        return false;
    }

    public static function addLeaf($leaf, $id  = 'id', $parent_id = 'parent_id', &$incomingNode = null) {
        $root = false;
        if($incomingNode == null) {
            $root = true;
        }
        //initialization of tree
        if($root == true && empty(self::$tree)) {
            self::$tree = [
                'item' => null,
                'children' => []
            ];
        }
        $leafItemId = null;
        if (is_object($leaf['item']) == false) {
            $leafItemId = $leaf['item'];
        } else {
            $leafItemId = $leaf['item']->$id;
        }
        //plain id with object replacement in parent node
        if (is_object($leaf['children'][0]['item']) == false) {
            $leafChildId = $leaf['children'][0]['item'];
        } else {
            $leafChildId = $leaf['children'][0]['item']->$id;
        }
        //@todo does this code needed?
        if (is_object($leaf['children'][0]['item']) == false) {
            $leafParentId = $leaf['item'];
        } else {
            $leafParentId = $leaf['children'][0]['item']->$parent_id;
        }
        if($root == true && empty($leaf['item']) == false) {
            //if node is with parent, create parent node first
            $nodeExist = TreeHelper::isNodeExist($id, $leafItemId);
            //if node does not exist then create it
            if($nodeExist == false) {
                $parentLeaf = [
                    'item' => null,
                    'children' => [
                        [
                            'item' => $leaf['item'],
                            'children' => []
                        ]
                    ]
                ];
                self::addLeaf($parentLeaf, $id, $parent_id);
            }
            //try to find child node if it exists
            $nodeExist = TreeHelper::isNodeExist($id, $leafChildId);
            if($nodeExist == true) {
                $existentChildNode = TreeHelper::getNode($id, $leafChildId, -1, null, true);
                //@todo what if there is more than one children?
                $leaf['children'][0]['children'] = array_merge($leaf['children'][0]['children'], $existentChildNode['children']);
            }
        }
        if($root == true) {
            $incomingNode = &self::$tree['children'];
        }
        foreach ($incomingNode as $nodeKey => &$node) {
            $nodeItemId = null;
            if (is_object($node['item']) == false) {
                $nodeItemId = $node['item'];
            } else {
                $nodeItemId = $node['item']->$id;
            }
            if($nodeItemId == $leafItemId) {
                $node['children'] = array_merge($node['children'], $leaf['children']);
                return true;
            }
            if($nodeItemId == $leafChildId) {
                $newNode = [
                    'item' => $leafParentId,
                    'children' => [
                        [
                            'item' => $leaf['children'][0]['item'],
                            'children' => $node['children']
                        ]
                    ]
                ];
                unset($incomingNode[$nodeKey]);
                self::addLeaf($newNode, $id, $parent_id);
                return true;
            }
            if(empty($node['children']) == false) {
                $result = self::addLeaf($leaf, $id, $parent_id, $node['children']);
                if($result == true) {
                    return true;
                }
            }
        }
        if($root == true) {
            $incomingNode = array_merge($incomingNode, $leaf['children']);
            return true;
        }
        else {
            return false;
        }
    }

    public static function getBTStems($root = null, $depth = 0) {
        $rootNode = $root;
        $weAreInroot = false;
        if(empty($rootNode) == true) {
            //Tree may not be initialized
            if(isset(self::$tree['children']) == true) {
                $rootNode = self::$tree['children'];
            }
            else {
                $rootNode = [];
            }
            $weAreInroot = true;
        }
        foreach ($rootNode as $node) {
            if(empty($node['children']) == false) {
                self::getBTStems($node['children'], $depth + 1);
                self::$flatNodes[$depth + 1][] = $node;
            }
        }
        if($weAreInroot) {
            ksort(self::$flatNodes);
            return array_reverse(self::$flatNodes);
        }
    }

    //Bottom-Top path
    public static function getBTNodesPath($parentNode) {
        throw new Exception('Not implemented', 500);
    }

    public static function getTBNodesPath() {
        throw new Exception('Not implemented', 500);
    }

    //DFS - Depth first search
    public static function plainItemExistDFS($root = null, $depth = -1) {
        if($depth == 0) {
            return false;
        }
        $rootNode = $root;
        if(empty($rootNode) == true) {
            //Tree may not be initialized
            if(isset(self::$tree['children']) == true) {
                $rootNode = self::$tree['children'];
            }
            else {
                $rootNode = [];
            }
        }
        foreach ($rootNode as $node) {
            if(is_object($node['item']) == false) {
                return true;
            }
            if(empty($node['children']) == false) {
                if($depth == -1) {
                    $result = self::plainItemExistDFS($node['children'], $depth);
                }
                else {
                    $result = self::plainItemExistDFS($node['children'], $depth - 1);
                }
                if($result == true) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function iterateDS(&$root = null, $parentNode = null, $callback, $depth = 0) {
        $rootNode = &$root;
        $parentNode = $parentNode;
        if(empty($rootNode) == true) {
            //Tree may not be initialized
            if(isset(self::$tree['children']) == true) {
                $parentNode = self::$tree['item'];
                $rootNode = &self::$tree['children'];
            }
            else {
                $parentNode = null;
                $rootNode = [];
            }
        }
        if(empty($rootNode) == true) {
            return;
        }
        if(empty($callback) == true) {
            return;
        }
        foreach ($rootNode as $key => &$node) {
            $callback($node, $parentNode, $depth);
            if(empty($node) == true) {
                unset($rootNode[$key]);
                continue;
            }
            if(empty($node['children']) == false) {
                self::iterateDS($node['children'], $node['item'], $callback, $depth + 1);
            }
        }
        return;
    }

    public static function loadTree($treeObject) {
        self::$tree = $treeObject;
    }
}
