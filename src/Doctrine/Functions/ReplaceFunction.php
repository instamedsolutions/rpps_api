<?php

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * ReplaceFunction ::= "REPLACE" "(" StringPrimary "," StringPrimary "," StringPrimary ")".
 */
class ReplaceFunction extends FunctionNode
{
    public mixed $subject;
    public mixed $search;
    public mixed $replace;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->subject = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->search = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->replace = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'REPLACE(' .
            $this->subject->dispatch($sqlWalker) . ', ' .
            $this->search->dispatch($sqlWalker) . ', ' .
            $this->replace->dispatch($sqlWalker) .
            ')';
    }
}
