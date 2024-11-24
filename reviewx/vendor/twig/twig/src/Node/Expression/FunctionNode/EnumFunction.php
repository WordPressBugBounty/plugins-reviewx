<?php

namespace Rvx\Twig\Node\Expression\FunctionNode;

use Rvx\Twig\Compiler;
use Rvx\Twig\Error\SyntaxError;
use Rvx\Twig\Node\Expression\ConstantExpression;
use Rvx\Twig\Node\Expression\FunctionExpression;
class EnumFunction extends FunctionExpression
{
    public function compile(Compiler $compiler) : void
    {
        $arguments = $this->getNode('arguments');
        if ($arguments->hasNode('enum')) {
            $firstArgument = $arguments->getNode('enum');
        } elseif ($arguments->hasNode('0')) {
            $firstArgument = $arguments->getNode('0');
        } else {
            $firstArgument = null;
        }
        if (!$firstArgument instanceof ConstantExpression || 1 !== \count($arguments)) {
            parent::compile($compiler);
            return;
        }
        $value = $firstArgument->getAttribute('value');
        if (!\is_string($value)) {
            throw new SyntaxError('The first argument of the "enum" function must be a string.', $this->getTemplateLine(), $this->getSourceContext());
        }
        if (!\enum_exists($value)) {
            throw new SyntaxError(\sprintf('The first argument of the "enum" function must be the name of an enum, "%s" given.', $value), $this->getTemplateLine(), $this->getSourceContext());
        }
        if (!($cases = $value::cases())) {
            throw new SyntaxError(\sprintf('The first argument of the "enum" function must be a non-empty enum, "%s" given.', $value), $this->getTemplateLine(), $this->getSourceContext());
        }
        $compiler->raw(\sprintf('%s::%s', $value, $cases[0]->name));
    }
}
