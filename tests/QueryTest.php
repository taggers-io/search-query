<?php

namespace Railken\SQ\Tests;

use PHPUnit\Framework\TestCase;
use Railken\SQ\Exceptions\QuerySyntaxException;
use Railken\SQ\Languages\BoomTree\Nodes as Nodes;
use Railken\SQ\Languages\BoomTree\Resolvers as Resolvers;
use Railken\SQ\QueryParser;

class QueryTest extends TestCase
{
    /**
     * @var QueryParser
     */
    protected $parser;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->parser = new QueryParser();
        $this->parser->addResolvers([
            new Resolvers\ValueResolver(),
            new Resolvers\KeyResolver(),
            new Resolvers\GroupingResolver(),
            new Resolvers\SumFunctionResolver(),
            new Resolvers\DateFormatFunctionResolver(),
            new Resolvers\ConcatFunctionResolver(),
            new Resolvers\NowFunctionResolver(),
            new Resolvers\SubdateFunctionResolver(),
            new Resolvers\NotEqResolver(),
            new Resolvers\EqResolver(),
            new Resolvers\LteResolver(),
            new Resolvers\LtResolver(),
            new Resolvers\GteResolver(),
            new Resolvers\GtResolver(),
            new Resolvers\CtResolver(),
            new Resolvers\SwResolver(),
            new Resolvers\NotInResolver(),
            new Resolvers\InResolver(),
            new Resolvers\EwResolver(),
            new Resolvers\NotNullResolver(),
            new Resolvers\NullResolver(),
            new Resolvers\AndResolver(),
            new Resolvers\OrResolver(),
        ]);
    }

    public function testFunction()
    {
        $query = $this->parser;

        $result = $query->parse('sum(1, 2) eq x');

        $this->assertEquals(Nodes\EqNode::class, get_class($result));
        $this->assertEquals(Nodes\SumFunctionNode::class, get_class($result->getChildByIndex(0)));
        $this->assertEquals('1', $result->getChildByIndex(0)->getChildByIndex(0)->getValue());
        $this->assertEquals('2', $result->getChildByIndex(0)->getChildByIndex(1)->getValue());
        $this->assertEquals('x', $result->getChildByIndex(1)->getValue());
    }

    public function testFunctionConcat()
    {
        $query = $this->parser;

        $result = $query->parse('y eq concat(x, 2)');

        $this->assertEquals(Nodes\EqNode::class, get_class($result));
        $this->assertEquals('y', $result->getChildByIndex(0)->getValue());
        $this->assertEquals(Nodes\ConcatFunctionNode::class, get_class($result->getChildByIndex(1)));
        $this->assertEquals('x', $result->getChildByIndex(1)->getChildByIndex(0)->getValue());
        $this->assertEquals('2', $result->getChildByIndex(1)->getChildByIndex(1)->getValue());
    }

    public function testFunctionNow()
    {
        $query = $this->parser;

        $result = $query->parse('y gt now()');

        $this->assertEquals(Nodes\GtNode::class, get_class($result));
        $this->assertEquals('y', $result->getChildByIndex(0)->getValue());
        $this->assertEquals(Nodes\NowFunctionNode::class, get_class($result->getChildByIndex(1)));
    }

    public function testFunctionSubdate()
    {
        $query = $this->parser;

        $result = $query->parse('x gt subdate(y,30)');

        $this->assertEquals(Nodes\GtNode::class, get_class($result));
        $this->assertEquals('x', $result->getChildByIndex(0)->getValue());
        $this->assertEquals(Nodes\SubdateFunctionNode::class, get_class($result->getChildByIndex(1)));
        $this->assertEquals('y', $result->getChildByIndex(1)->getChildByIndex(0)->getValue());
        $this->assertEquals('30', $result->getChildByIndex(1)->getChildByIndex(1)->getValue());
    }

    public function testExceptionFunction()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('sum');
    }

    public function testExceptionEq1()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('x eq');
    }

    public function testExceptionEq2()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('eq');
    }

    public function testExceptionEq3()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('eq 1');
    }

    public function testExceptionEq4()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('x eq (1)');
    }

    public function testKeyNode()
    {
        $query = $this->parser;

        $result = $query->parse('x2');
        $this->assertEquals(Nodes\KeyNode::class, get_class($result));
        $this->assertEquals('x2', $result->getValue());
        $this->assertEquals(['type', 'value'], array_keys($result->toArray()));
    }

    public function testValueNode()
    {
        $query = $this->parser;

        $result = $query->parse('1');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('1', $result->getValue());
        $this->assertEquals(['type', 'value'], array_keys($result->toArray()));
    }

    public function testValueTextNormalNode()
    {
        $query = $this->parser;

        $result = $query->parse('"1"');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('1', $result->getValue());
        $this->assertEquals(['type', 'value'], array_keys($result->toArray()));
    }

    public function testValueTextEsapedNode()
    {
        $query = $this->parser;

        $result = $query->parse('"1\\""');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('1"', $result->getValue());
        $this->assertEquals(['type', 'value'], array_keys($result->toArray()));
    }

    public function testValueIntegerNode()
    {
        $query = $this->parser;

        $result = $query->parse('832');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('832', $result->getValue());
    }

    public function testValueIntegerNegativeNode()
    {
        $query = $this->parser;

        $result = $query->parse('-832');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('-832', $result->getValue());
    }

    public function testValueDecimalNode()
    {
        $query = $this->parser;

        $result = $query->parse('0.832');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('0.832', $result->getValue());
    }

    public function testValueDecimalNegativeNode()
    {
        $query = $this->parser;

        $result = $query->parse('-0.832');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('-0.832', $result->getValue());
    }

    public function testValueENotationNode()
    {
        $query = $this->parser;

        $result = $query->parse('1e3');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('1e3', $result->getValue());
    }

    public function testValueENotationNegativeNode()
    {
        $query = $this->parser;

        $result = $query->parse('-1e3');
        $this->assertEquals(Nodes\ValueNode::class, get_class($result));
        $this->assertEquals('-1e3', $result->getValue());
    }

    public function testInvalidNumber1Node()
    {
        $this->expectException(QuerySyntaxException::class);

        $result = $this->parser->parse('3.4.3');
    }

    public function testInvalidNumber2Node()
    {
        $this->expectException(QuerySyntaxException::class);

        $result = $this->parser->parse('3,3.3');
    }

    public function testInvalidNumber3Node()
    {
        $this->expectException(QuerySyntaxException::class);

        $result = $this->parser->parse('+3');
    }

    public function testInvalidNumber4Node()
    {
        $this->expectException(QuerySyntaxException::class);

        $result = $this->parser->parse('3/9');
    }

    public function testEq()
    {
        $query = $this->parser;

        $result = $query->parse('x eq 1');
        $this->assertEquals(Nodes\EqNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x = 1');
        $this->assertEquals(Nodes\EqNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testNotEq()
    {
        $query = $this->parser;

        $result = $query->parse('x not eq 1');
        $this->assertEquals(Nodes\NotEqNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x != 1');
        $this->assertEquals(Nodes\NotEqNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x <> 1');
        $this->assertEquals(Nodes\NotEqNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testGt()
    {
        $query = $this->parser;

        $result = $query->parse('x gt 1');
        $this->assertEquals(Nodes\GtNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x > 1');
        $this->assertEquals(Nodes\GtNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testGte()
    {
        $query = $this->parser;

        $result = $query->parse('x gte 1');
        $this->assertEquals(Nodes\GteNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x >= 1');
        $this->assertEquals(Nodes\GteNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testLt()
    {
        $query = $this->parser;

        $result = $query->parse('x lt 1');
        $this->assertEquals(Nodes\LtNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x < 1');
        $this->assertEquals(Nodes\LtNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testLte()
    {
        $query = $this->parser;

        $result = $query->parse('x lte 1');
        $this->assertEquals(Nodes\LteNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x <= 1');
        $this->assertEquals(Nodes\LteNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testNull()
    {
        $query = $this->parser;

        $result = $query->parse('x is null');
        $this->assertEquals(Nodes\NullNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
    }

    public function testNotNull()
    {
        $query = $this->parser;

        $result = $query->parse('x is not null');
        $this->assertEquals(Nodes\NotNullNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
    }

    public function testCt()
    {
        $query = $this->parser;

        $result = $query->parse('x ct 1');
        $this->assertEquals(Nodes\CtNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x *= 1');
        $this->assertEquals(Nodes\CtNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testSw()
    {
        $query = $this->parser;

        $result = $query->parse('x sw 1');
        $this->assertEquals(Nodes\SwNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x ^= 1');
        $this->assertEquals(Nodes\SwNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testEw()
    {
        $query = $this->parser;

        $result = $query->parse('x ew 1');
        $this->assertEquals(Nodes\EwNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());

        $result = $query->parse('x $= 1');
        $this->assertEquals(Nodes\EwNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testNotIn()
    {
        $query = $this->parser;

        $result = $query->parse('x not in (1, 2)');

        $this->assertEquals(Nodes\NotInNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(0)->getValue());
        $this->assertEquals('2', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(1)->getValue());

        $result = $query->parse('x !=[] (1, 2)');
        $this->assertEquals(Nodes\NotInNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(0)->getValue());
        $this->assertEquals('2', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(1)->getValue());
    }

    public function testIn()
    {
        $query = $this->parser;

        $result = $query->parse('x in (1,2)');
        $this->assertEquals(Nodes\InNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(0)->getValue());
        $this->assertEquals('2', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(1)->getValue());

        $result = $query->parse('x =[] (1,2)');
        $this->assertEquals(Nodes\InNode::class, get_class($result));
        $this->assertEquals('x', $result->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(0)->getValue());
        $this->assertEquals('2', $result->getFirstChildByClass(Nodes\GroupNode::class)->getChildByIndex(1)->getValue());
    }

    public function testExceptionIn()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('x in');
    }

    public function testAnd1()
    {
        $query = $this->parser;
        $result = $query->parse('x and y');
        $this->assertEquals(Nodes\AndNode::class, get_class($result));
        $this->assertEquals('x', $result->getChildByIndex(0)->getValue());
        $this->assertEquals('y', $result->getChildByIndex(1)->getValue());

        $result = $query->parse('x && y');
        $this->assertEquals(Nodes\AndNode::class, get_class($result));
        $this->assertEquals('x', $result->getChildByIndex(0)->getValue());
        $this->assertEquals('y', $result->getChildByIndex(1)->getValue());
    }

    public function testExceptionAnd()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('x and');
    }

    public function testExceptionAnd1()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('and x');
    }

    public function testAndEq()
    {
        $query = $this->parser;
        $result = $query->parse('x = 1 and y = 1');
        $this->assertEquals(Nodes\AndNode::class, get_class($result));
        $this->assertEquals(Nodes\EqNode::class, get_class($result->getChildByIndex(0)));
        $this->assertEquals('x', $result->getChildByIndex(0)->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getChildByIndex(0)->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
        $this->assertEquals(Nodes\EqNode::class, get_class($result->getChildByIndex(1)));
        $this->assertEquals('y', $result->getChildByIndex(1)->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getChildByIndex(1)->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testAnd2()
    {
        $query = $this->parser;
        $result = $query->parse('x and (y and z)');

        $this->assertEquals(Nodes\AndNode::class, get_class($result));
        $this->assertEquals('x', $result->getChildByIndex(0)->getValue());
        $this->assertEquals('y', $result->getChildByIndex(1)->getValue());
        $this->assertEquals('z', $result->getChildByIndex(2)->getValue());
    }

    public function testOr1()
    {
        $query = $this->parser;
        $result = $query->parse('x or y');
        $this->assertEquals(Nodes\OrNode::class, get_class($result));
        $this->assertEquals('x', $result->getChildByIndex(0)->getValue());
        $this->assertEquals('y', $result->getChildByIndex(1)->getValue());

        $result = $query->parse('x || y');
        $this->assertEquals(Nodes\OrNode::class, get_class($result));
        $this->assertEquals('x', $result->getChildByIndex(0)->getValue());
        $this->assertEquals('y', $result->getChildByIndex(1)->getValue());
    }

    public function testAndOr1()
    {
        $query = $this->parser;
        $result = $query->parse('x || y && z');

        $this->assertEquals(Nodes\OrNode::class, get_class($result));
        $this->assertEquals('x', $result->getChildByIndex(0)->getValue());
        $this->assertEquals(Nodes\AndNode::class, get_class($result->getChildByIndex(1)));
        $this->assertEquals('y', $result->getChildByIndex(1)->getChildByIndex(0)->getValue());
        $this->assertEquals('z', $result->getChildByIndex(1)->getChildByIndex(1)->getValue());
    }

    public function testGrouping1()
    {
        $query = $this->parser;
        $result = $query->parse('((x eq 1))');

        $this->assertEquals(Nodes\GroupNode::class, get_class($result));
        $this->assertEquals(Nodes\GroupNode::class, get_class($result->getChildByIndex(0)));
        $this->assertEquals(Nodes\EqNode::class, get_class($result->getChildByIndex(0)->getChildByIndex(0)));
        $this->assertEquals('x', $result->getChildByIndex(0)->getChildByIndex(0)->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getChildByIndex(0)->getChildByIndex(0)->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testGrouping2()
    {
        $query = $this->parser;
        $result = $query->parse('( x = 1 ) and y = 2');
        $this->assertEquals(Nodes\AndNode::class, get_class($result));
        $this->assertEquals(Nodes\GroupNode::class, get_class($result->getChildByIndex(0)));
        $this->assertEquals(Nodes\EqNode::class, get_class($result->getChildByIndex(0)->getChildByIndex(0)));
        $this->assertEquals('x', $result->getChildByIndex(0)->getChildByIndex(0)->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('1', $result->getChildByIndex(0)->getChildByIndex(0)->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
        $this->assertEquals(Nodes\EqNode::class, get_class($result->getChildByIndex(1)));
        $this->assertEquals('y', $result->getChildByIndex(1)->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('2', $result->getChildByIndex(1)->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testExceptionGrouping1()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('(x');
    }

    public function testExceptionGrouping2()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('x)');
    }

    public function testExceptionGrouping3()
    {
        $this->expectException(QuerySyntaxException::class);
        $this->parser->parse('(x eq ")"');
    }

    public function testVeryLong()
    {
        $query = $this->parser;
        $result = $query->parse('x eq 1 or y eq 2 and x eq 4 or d eq 5 and y eq 3 or t eq 9 and h eq 4 or c eq 4');

        $this->assertEquals(Nodes\OrNode::class, get_class($result));
    }

    public function testComplex()
    {
        $query = $this->parser;
        $result = $query->parse('x = 27 and y = "83"');
        $this->assertEquals(Nodes\AndNode::class, get_class($result));
        $this->assertEquals(Nodes\EqNode::class, get_class($result->getChildByIndex(0)));
        $this->assertEquals('x', $result->getChildByIndex(0)->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('27', $result->getChildByIndex(0)->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
        $this->assertEquals(Nodes\EqNode::class, get_class($result->getChildByIndex(1)));
        $this->assertEquals('y', $result->getChildByIndex(1)->getFirstChildByClass(Nodes\KeyNode::class)->getValue());
        $this->assertEquals('83', $result->getChildByIndex(1)->getFirstChildByClass(Nodes\ValueNode::class)->getValue());
    }

    public function testComplex1()
    {
        $query = $this->parser;
        $result = $query->parse('(y or (z)) or x');

        $this->assertEquals(Nodes\OrNode::class, get_class($result));

        $this->assertEquals(Nodes\KeyNode::class, get_class($result->getChildByIndex(0)));
        $this->assertEquals('y', $result->getChildByIndex(0)->getValue());
        $this->assertEquals(Nodes\GroupNode::class, get_class($result->getChildByIndex(1)));
        $this->assertEquals('z', $result->getChildByIndex(1)->getChildByIndex(0)->getValue());
        $this->assertEquals('x', $result->getChildByIndex(2)->getValue());
    }
}
