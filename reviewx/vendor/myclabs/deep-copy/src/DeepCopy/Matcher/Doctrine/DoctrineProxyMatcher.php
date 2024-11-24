<?php

namespace Rvx\DeepCopy\Matcher\Doctrine;

use Rvx\DeepCopy\Matcher\Matcher;
use Rvx\Doctrine\Persistence\Proxy;
/**
 * @final
 */
class DoctrineProxyMatcher implements Matcher
{
    /**
     * Matches a Doctrine Proxy class.
     *
     * {@inheritdoc}
     */
    public function matches($object, $property)
    {
        return $object instanceof Proxy;
    }
}
