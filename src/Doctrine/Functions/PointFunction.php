<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class PointFunction extends FunctionNode
{
    public InputParameter $latitude;
    public InputParameter $longitude;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // Matches POINT
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // Matches (
        /* @phpstan-ignore-next-line */
        $this->longitude = $parser->ArithmeticPrimary(); // First argument (longitude)
        $parser->match(TokenType::T_COMMA); // Matches ,
        /* @phpstan-ignore-next-line */
        $this->latitude = $parser->ArithmeticPrimary(); // Second argument (latitude)
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // Matches )
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'POINT(' .
            $this->longitude->dispatch($sqlWalker) . ', ' .
            $this->latitude->dispatch($sqlWalker) . ')';
    }
}
