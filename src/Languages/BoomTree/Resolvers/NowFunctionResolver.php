<?php

namespace Railken\SQ\Languages\BoomTree\Resolvers;

use Railken\SQ\Languages\BoomTree\Nodes as Nodes;

class NowFunctionResolver extends FunctionResolver
{
    /**
     * Node resolved.
     *
     * @var string
     */
    public $node = Nodes\NowFunctionNode::class;

    /**
     * Regex.
     *
     * @var array
     */
    public $regex = [
        '/(?<![^\s])now(?![^\s])/i',
    ];
}
