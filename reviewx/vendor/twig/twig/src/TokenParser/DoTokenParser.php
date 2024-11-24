<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rvx\Twig\TokenParser;

use Rvx\Twig\Node\DoNode;
use Rvx\Twig\Node\Node;
use Rvx\Twig\Token;
/**
 * Evaluates an expression, discarding the returned value.
 *
 * @internal
 */
final class DoTokenParser extends AbstractTokenParser
{
    public function parse(Token $token) : Node
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        return new DoNode($expr, $token->getLine());
    }
    public function getTag() : string
    {
        return 'do';
    }
}
