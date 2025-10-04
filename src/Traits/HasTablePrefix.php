<?php

namespace PeterMarkley\Tollerus\Traits;

trait HasTablePrefix
{
    /**
     * Get configured table prefix
     */
    public function getConnectionName()
    {
        return \Config::get('tollerus.connection', 'tollerus');
    }
}

