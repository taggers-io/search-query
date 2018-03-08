<?php

namespace Railken\SQ\Resolvers;

use Railken\SQ\Nodes as Nodes;

class NotEqResolver extends ComparisonOperatorResolver
{
    /**
     * Node resolved
     *
     * @var string
     */
    public $node = Nodes\NotEqNode::class;

    /**
     * Regex token
     *
     * @var string
     */
    public $regex = [
        '/(?<![^\s])not eq(?![^\s])/i',
        '/(?<![^\s])<>(?![^\s])/i',
        '/(?<![^\s])!=(?![^\s])/i',
    ];
}
