<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

/**
 * YouTube provider using www,youtube-nocookie.com as the host
 * @author James
 */
class YouTubeNoCookie extends YouTube
{
    /**
     * @inheritdoc
     */
    public static function getProviderCode(): string
    {
        return GalleryVideo::PROVIDER_YOUTUBE_NOCOOKIE;
    }

    /**
     * @inheritdoc
     */
    public static function getProviderDescription(): string
    {
        return _t(
            GalleryVideo::class . ".YOUTUBE_NOCOOKIE_PROVIDER_DESCRIPTION",
            "YouTube (privacy enhanced mode)"
        );
    }

    /**
     * @inheritdoc
     */
    public function getHost(): string
    {
        return "www.youtube-nocookie.com";
    }
}
