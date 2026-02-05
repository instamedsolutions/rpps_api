<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class MBRContains extends FunctionNode
{
    public STMakeEnvelope $point1;
    public PathExpression $point2;

    // Parse the SQL arguments
    public function parse(Parser $parser) : void
    {
        $parser->match(TokenType::T_IDENTIFIER); // MBRContains
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // (
        /* @phpstan-ignore-next-line */
        $this->point1 = $parser->ArithmeticPrimary(); // first point
        $parser->match(TokenType::T_COMMA); // ,
        /* @phpstan-ignore-next-line */
        $this->point2 = $parser->ArithmeticPrimary(); // second point
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // )
    }

    // Generate SQL output for this function
    public function getSql(SqlWalker $sqlWalker) : string
    {
        return 'MBRContains(' .
            $this->point1->dispatch($sqlWalker) . ', ' .
            $this->point2->dispatch($sqlWalker) . ')';
    }
}
