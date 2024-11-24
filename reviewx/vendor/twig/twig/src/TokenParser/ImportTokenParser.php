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

use Rvx\Twig\Node\Expression\Variable\AssignTemplateVariable;
use Rvx\Twig\Node\Expression\Variable\TemplateVariable;
use Rvx\Twig\Node\ImportNode;
use Rvx\Twig\Node\Node;
use Rvx\Twig\Token;
/**
 * Imports macros.
 *
 *   {% import 'forms.html' as forms %}
 *
 * @internal
 */
final class ImportTokenParser extends AbstractTokenParser
{
    public function parse(Token $token) : Node
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(Token::NAME_TYPE, 'as');
        $name = $this->parser->getStream()->expect(Token::NAME_TYPE)->getValue();
        $var = new AssignTemplateVariable(new TemplateVariable($name, $token->getLine()), $this->parser->isMainScope());
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $this->parser->addImportedSymbol('template', $name);
        return new ImportNode($macro, $var, $token->getLine());
    }
    public function getTag() : string
    {
        return 'import';
    }
}
