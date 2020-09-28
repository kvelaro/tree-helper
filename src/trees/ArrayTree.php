<?php

namespace Kvelaro\trees;

class ArrayTree extends AbstractTree implements Treeble
{

    protected $initTreeData = [
        'item' => null,
        'children' => []
    ];

    public function makeTree(array $arrayOfObjects, $id = 'id', $parent_id = 'parent_id')
    {
        foreach ($arrayOfObjects as $object) {
            $leaf = [
                'item' => $object[$parent_id],
                'children' => [
                    [
                        'item' => $object,
                        'children' => []
                    ]
                ]
            ];
            $this->addLeaf($leaf, $id, $parent_id);
        }
    }

    public function appendNodes($arrayOfObjects, $id = 'id', $parent_id = 'parent_id')
    {
        $this->makeTree($arrayOfObjects, $id, $parent_id);
    }

    public function getTree()
    {
        return $this->tree;
    }

    public function isNodeExist($fieldToSearch = 'id', $valueToSearch, $level = -1, $incomingNode = null)
    {
        if ($level == 0) {
            return false;
        }
        if ($incomingNode == null) {
            $incomingNode = $this->getTree()['children'];
        }
        foreach ($incomingNode as $node) {
            if ($node['item'][$fieldToSearch] == $valueToSearch) {
                return true;
            }
            if (!empty($node['children']) && ($level > 0 || $level == -1)) {
                if ($level == -1) {
                    $result = $this->isNodeExist($fieldToSearch, $valueToSearch, $level, $node['children']);
                } else {
                    $result = $this->isNodeExist($fieldToSearch, $valueToSearch, $level - 1, $node['children']);
                }
                if ($result == true) {
                    return true;
                }
            }
        }
        return false;
    }

    public function addLeaf($leaf, $id = 'id', $parent_id = 'parent_id', &$incomingNode = null)
    {
        $root = false;
        if ($incomingNode == null) {
            $root = true;
        }
        $leafItemId = $leaf['item'][$id];
        //@todo index[0] should be replaced
        $leafChildId = $leaf['children'][0]['item'][$id];
        $leafParentId = $leaf['children'][0]['item'][$parent_id];
        if ($root == true && empty($leaf['item']) == false) {
            //if node is with parent, create parent node first
            $nodeExist = $this->isNodeExist($id, $leafItemId);
            //if node does not exist then create it
            if (!$nodeExist) {
                $parentLeaf = [
                    'item' => null,
                    'children' => [
                        [
                            'item' => $leaf['item'],
                            'children' => []
                        ]
                    ]
                ];
                $this->addLeaf($parentLeaf, $id, $parent_id);
            }
            //try to find child node if it exists
            $nodeExist = $this->isNodeExist($id, $leafChildId);
            if ($nodeExist) {
                $existentChildNode = $this->getNode($id, $leafChildId, -1, null, true);
                //@todo what if there is more than one children?
                $leaf['children'][0]['children'] = array_merge(
                    $leaf['children'][0]['children'],
                    $existentChildNode['children']
                );
            }
        }
        if ($root == true) {
            $incomingNode = &$this->getTree()['children'];
        }
        foreach ($incomingNode as $nodeKey => &$node) {
            $nodeItemId = null;
            $nodeItemId = $node['item'][$id];
        }
        if ($nodeItemId == $leafItemId) {
            $node['children'] = array_merge($node['children'], $leaf['children']);
            return true;
        }
        if ($nodeItemId == $leafChildId) {
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
            $this->addLeaf($newNode, $id, $parent_id);
            return true;
        }
        if (empty($node['children']) == false) {
            $result = $this->addLeaf($leaf, $id, $parent_id, $node['children']);
            if ($result == true) {
                return true;
            }
        }
        if ($root == true) {
            $incomingNode = array_merge($incomingNode, $leaf['children']);
            return true;
        } else {
            return false;
        }
    }

    public function getNode($fieldToSearch = 'id', $valueToSearch, $level = -1, $incomingNode = null, $remove = false)
    {
        if ($level == 0) {
            return false;
        }
        if ($incomingNode == null) {
            $incomingNode = &$this->getTree()['children'];
        }
        foreach ($incomingNode as $nodeKey => &$node) {
            $nodeValue = $node['item'][$fieldToSearch];
            if ($nodeValue == $valueToSearch) {
                if ($remove == true) {
                    $tmpNode = $node;
                    unset($incomingNode[$nodeKey]);
                    return $tmpNode;
                }
                return $node;
            }
            if (empty($node['children']) == false && ($level > 0 || $level == -1)) {
                if ($level == -1) {
                    $result = $this->getNode($fieldToSearch, $valueToSearch, $level, $node['children'], $remove);
                } else {
                    $result = $this->getNode($fieldToSearch, $valueToSearch, $level - 1, $node['children'], $remove);
                }
                if ($result != false) {
                    return $result;
                }
            }
        }
        return false;
    }

    public function isEmpty()
    {
        return count($this->getTree()) > 0;
    }
}