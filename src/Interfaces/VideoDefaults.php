<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

/**
 * Specify requirements for handling video options
 * @author James
 */
interface VideoDefaults
{
    public function getDefaultVideoHeight(): int;

}
