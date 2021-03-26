<?php

namespace Pixelgrade\Customify\Vendor\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(\Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface $logger);
}
