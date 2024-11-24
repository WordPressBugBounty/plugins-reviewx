<?php

namespace Rvx\Builder;

abstract class BaseBuilder
{
    /**
     * Prepare classes for a given segment.
     *
     * @param string $segment
     * @return array
     */
    public function prepareClasses(string $segment) : array
    {
        $uniqueClasses = $this->getUniqueClasses($segment);
        $modifiedClasses = [];
        foreach ($uniqueClasses as $item) {
            $transformed = $this->transformClass($item);
            $modifiedClasses[] = $transformed;
        }
        return $modifiedClasses;
    }
    /**
     * Retrieve unique classes for a given segment.
     *
     * This method must be implemented by subclasses.
     *
     * @param string $segment
     * @return array
     */
    public abstract function getUniqueClasses(string $segment) : array;
    /**
     * Transform a class item into the required format.
     *
     * This method must be implemented by subclasses.
     *
     * @param array $item
     * @return array
     */
    public abstract function transformClass(array $item) : array;
}
