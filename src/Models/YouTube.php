<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;

/**
 * YouTube provider, based on https://developers.google.com/youtube/player_parameters
 * @author James
 */
class YouTube extends VideoProvider
{
    /**
     * @config
     * Whether to enable (and require) the YT iframe API
     * See: https://developers.google.com/youtube/iframe_api_reference
     */
    private static bool $enable_iframe_api = true;

    /**
     * Return ident code for this video, used to load an instance of this class
     */
    public static function getProviderCode(): string
    {
        return GalleryVideo::PROVIDER_YOUTUBE;
    }

    /**
     * Description for assistance in identifying the provider
     */
    public static function getProviderDescription(): string
    {
        return _t(
            GalleryVideo::class . ".YOUTUBE_PROVIDER_DESCRIPTION",
            "YouTube"
        );
    }

    /**
     * Return YouTube host for URL
     */
    public function getHost(): string
    {
        return "www.youtube.com";
    }

    /**
     * Return YouTube path for URL
     */
    public function getPath($videoId = ''): string
    {
        if ($videoId) {
            return "/embed/{$videoId}/";
        } else {
            return "";
        }
    }

    /**
     * Return YouTube default query arguments for URL
     * Provide some sensible defaults. You can override these in getEmbedURL
     */
    public function getQueryArguments(): array
    {
        return [
            "enablejsapi" => 1,
            "origin" => Director::protocolAndHost(),
            "autoplay" => 0,
            "modestbranding" => 0,
            "fs" => 1,
            "rel" => 0 // show related videos from the same channel
        ];
    }

    /**
     * Include the YT iframe API (if enabled)
     */
    #[\Override]
    public function addEmbedRequirements(): bool
    {
        if (!self::config()->get('enable_iframe_api')) {
            return false;
        } else {
            Requirements::javascript(
                "https://www.youtube.com/iframe_api",
                [
                    "async" => true,
                ]
            );
            return true;
        }
    }

    /**
     * Return Embed URL for the video
     */
    public function getEmbedURL(string $videoID, array $customQueryArgs, int $videoHeight): string
    {
        $query = array_merge($this->getQueryArguments(), $customQueryArgs);
        $queryString = http_build_query($query);
        return $this->getProtocol()
            . $this->getHost()
            . $this->getPath($videoID)
            . ($queryString ? "?{$queryString}" : "");
    }

    /**
     * Return off-site watch URL for the video
     */
    public function getWatchURL(string $videoID, array $customQueryArgs): string
    {
        $query = [
            'rel' => 0,
        ];
        $query = array_merge($query, $customQueryArgs);
        $query['v'] = $videoID;
        $queryString = http_build_query($query);
        return "https://www.youtube.com/watch?{$queryString}";
    }
}
