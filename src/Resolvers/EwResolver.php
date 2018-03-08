<?php

namespace Railken\SQ\Resolvers;

use Railken\SQ\Nodes as Nodes;

class EwResolver extends ComparisonOperatorResolver
{
    /**
     * Node resolved
     *
     * @var string
     */
    public $node = Nodes\EwNode::class;

    /**
     * Regex token
     *
     * @var string
     */
    public $regex = [
        '/(?<![^\s])ew(?![^\s])/i',
        '/(?<![^\s])\$=(?![^\s])/i',
    ];
}
