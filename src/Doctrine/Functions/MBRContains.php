<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\TokenType;

class MBRContains extends FunctionNode
{
    public $point1 = null;
    public $point2 = null;

    // Parse the SQL arguments
    public function parse(Parser $parser)
    {
        $parser->match(TokenType::T_IDENTIFIER); // MBRContains
        $parser->match(TokenType::T_OPEN_PARENTHESIS); // (
        $this->point1 = $parser->ArithmeticPrimary(); // first point
        $parser->match(TokenType::T_COMMA); // ,
        $this->point2 = $parser->ArithmeticPrimary(); // second point
        $parser->match(TokenType::T_CLOSE_PARENTHESIS); // )
    }

    // Generate SQL output for this function
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'MBRContains(' .
            $this->point1->dispatch($sqlWalker) . ', ' .
            $this->point2->dispatch($sqlWalker) . ')';
    }
}
