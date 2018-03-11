<?php

namespace Railken\SQ\Languages\BoomTree\Resolvers;

use Railken\SQ\Contracts\NodeContract;
use Railken\SQ\Languages\BoomTree\Nodes as Nodes;

class DateFormatFunctionResolver extends FunctionResolver
{
    /**
     * Node resolved
     *
     * @var string
     */
    public $node = Nodes\DateFormatFunctionNode::class;

    /**
     * Regex token
     *
     * @var string
     */
    public $regex = [
        '/(?<![^\s])date_format(?![^\s])/i',
        '/(?<![^\s])dateformat(?![^\s])/i'
    ];
}
