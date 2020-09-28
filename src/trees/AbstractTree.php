<?php

namespace Kvelaro\trees;

class AbstractTree
{
    protected $tree;

    protected $initTreeData = [];

    public function __construct()
    {
        $this->tree = $this->initTreeData;
    }

    public function getTree() {
        return $this->tree;
    }
}