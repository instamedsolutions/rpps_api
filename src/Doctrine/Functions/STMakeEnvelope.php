<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class STMakeEnvelope extends FunctionNode
{
    public PointFunction $point1;
    public PointFunction $point2;

    public function parse(Parser $parser) : void
    {
        $parser->match(TokenType::T_IDENTIFIER); // ST_MakeEnvelope
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // (
        /* @phpstan-ignore-next-line */
        $this->point1 = $parser->ArithmeticPrimary(); // first point
        $parser->match(TokenType::T_COMMA); // ,

        /* @phpstan-ignore-next-line */
        $this->point2 = $parser->ArithmeticPrimary(); // second point
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // )
    }

    public function getSql(SqlWalker $sqlWalker) : string
    {
        return 'ST_MakeEnvelope(' .
            $this->point1->dispatch($sqlWalker) . ', ' .
            $this->point2->dispatch($sqlWalker) . ')';
    }
}
