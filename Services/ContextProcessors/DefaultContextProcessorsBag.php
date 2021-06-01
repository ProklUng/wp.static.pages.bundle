<?php

namespace Prokl\StaticPageMakerBundle\Services\ContextProcessors;

use Prokl\StaticPageMakerBundle\Services\ContextProcessorInterface;
use Traversable;

/**
 * Class DefaultContextProcessorsBag
 * @package Prokl\StaticPageMakerBundle\Services\ContextProcessors
 *
 * @since 23.01.2021
 */
class DefaultContextProcessorsBag
{
    /**
     * @var ContextProcessorInterface[] $processors
     */
    private $processors = [];

    /**
     * @param Traversable $processors
     *
     * @return void
     */
    public function setProcessors(Traversable $processors): void
    {
        $handlers = iterator_to_array($processors);

        $this->processors = $handlers;
    }

    /**
     * @return ContextProcessorInterface[]
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }
}
