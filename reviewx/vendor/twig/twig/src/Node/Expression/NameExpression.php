<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rvx\Twig\Node\Expression;

use Rvx\Twig\Compiler;
class NameExpression extends AbstractExpression
{
    private $specialVars = ['_self' => '$this->getTemplateName()', '_context' => '$context', '_charset' => '$this->env->getCharset()'];
    public function __construct(string $name, int $lineno)
    {
        parent::__construct([], ['name' => $name, 'is_defined_test' => \false, 'ignore_strict_check' => \false, 'always_defined' => \false], $lineno);
    }
    public function compile(Compiler $compiler) : void
    {
        $name = $this->getAttribute('name');
        $compiler->addDebugInfo($this);
        if ($this->getAttribute('is_defined_test')) {
            if (isset($this->specialVars[$name])) {
                $compiler->repr(\true);
            } elseif (\PHP_VERSION_ID >= 70400) {
                $compiler->raw('array_key_exists(')->string($name)->raw(', $context)');
            } else {
                $compiler->raw('(isset($context[')->string($name)->raw(']) || array_key_exists(')->string($name)->raw(', $context))');
            }
        } elseif (isset($this->specialVars[$name])) {
            $compiler->raw($this->specialVars[$name]);
        } elseif ($this->getAttribute('always_defined')) {
            $compiler->raw('$context[')->string($name)->raw(']');
        } else {
            if ($this->getAttribute('ignore_strict_check') || !$compiler->getEnvironment()->isStrictVariables()) {
                $compiler->raw('($context[')->string($name)->raw('] ?? null)');
            } else {
                $compiler->raw('(isset($context[')->string($name)->raw(']) || array_key_exists(')->string($name)->raw(', $context) ? $context[')->string($name)->raw('] : (function () { throw new RuntimeError(\'Variable ')->string($name)->raw(' does not exist.\', ')->repr($this->lineno)->raw(', $this->source); })()')->raw(')');
            }
        }
    }
    /**
     * @deprecated since Twig 3.11 (to be removed in 4.0)
     */
    public function isSpecial()
    {
        trigger_deprecation('twig/twig', '3.11', 'The "%s()" method is deprecated and will be removed in Twig 4.0.', __METHOD__);
        return isset($this->specialVars[$this->getAttribute('name')]);
    }
    /**
     * @deprecated since Twig 3.11 (to be removed in 4.0)
     */
    public function isSimple()
    {
        trigger_deprecation('twig/twig', '3.11', 'The "%s()" method is deprecated and will be removed in Twig 4.0.', __METHOD__);
        return !$this->isSpecial() && !$this->getAttribute('is_defined_test');
    }
}
