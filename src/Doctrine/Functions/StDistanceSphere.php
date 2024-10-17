<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class StDistanceSphere extends FunctionNode
{
    public PointFunction $firstPoint;
    public PathExpression $secondPoint;

    public function parse(Parser $parser)
    {
        $parser->match(TokenType::T_IDENTIFIER); // Matches ST_Distance_Sphere
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // Matches (

        /* @phpstan-ignore-next-line */
        $this->firstPoint = $parser->ArithmeticPrimary(); // First argument
        $parser->match(TokenType::T_COMMA); // Matches ,

        /* @phpstan-ignore-next-line */
        $this->secondPoint = $parser->ArithmeticPrimary(); // Second argument
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // Matches )
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'ST_Distance_Sphere(' .
            $this->firstPoint->dispatch($sqlWalker) . ', ' .
            $this->secondPoint->dispatch($sqlWalker) . ')';
    }
}
