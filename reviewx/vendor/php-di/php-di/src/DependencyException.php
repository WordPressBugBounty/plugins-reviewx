<?php

declare (strict_types=1);
namespace ReviewX\DI;

use ReviewX\Psr\Container\ContainerExceptionInterface;
/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
