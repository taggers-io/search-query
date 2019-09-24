<?php

namespace Railken\SQ\Languages\BoomTree\Resolvers;

use Railken\SQ\Languages\BoomTree\Nodes as Nodes;

class SubdateFunctionResolver extends FunctionResolver
{
    /**
     * Node resolved.
     *
     * @var string
     */
    public $node = Nodes\SubdateFunctionNode::class;

    /**
     * Regex.
     *
     * @var array
     */
    public $regex = [
        '/(?<![^\s])subdate(?![^\s])/i',
    ];
}
