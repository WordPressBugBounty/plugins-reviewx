<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rvx\Twig\Node\Expression\Unary;

use Rvx\Twig\Compiler;
final class StringCastUnary extends AbstractUnary
{
    public function operator(Compiler $compiler) : Compiler
    {
        return $compiler->raw('(string)');
    }
}
