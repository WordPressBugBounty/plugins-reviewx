<?php

namespace ReviewX\DeepCopy\Matcher\Doctrine;

use ReviewX\DeepCopy\Matcher\Matcher;
use ReviewX\Doctrine\Persistence\Proxy;
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
