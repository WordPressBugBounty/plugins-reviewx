<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rvx\Twig\Profiler\Node;

use Rvx\Twig\Attribute\YieldReady;
use Rvx\Twig\Compiler;
use Rvx\Twig\Node\Node;
/**
 * Represents a profile leave node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[YieldReady]
class LeaveProfileNode extends Node
{
    public function __construct(string $varName)
    {
        parent::__construct([], ['var_name' => $varName]);
    }
    public function compile(Compiler $compiler) : void
    {
        $compiler->write("\n")->write(\sprintf("\$%s->leave(\$%s);\n\n", $this->getAttribute('var_name'), $this->getAttribute('var_name') . '_prof'));
    }
}
