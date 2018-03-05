<?php

namespace Railken\SQ\Nodes;

class LogicNode extends Node
{
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'childs' => array_map(function($node) {
            	return $node->jsonSerialize();
            }, $this->getChilds()),
        ]);
    }
}
