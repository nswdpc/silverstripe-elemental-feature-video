<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

/**
 * YouTube provider, based on https://developers.google.com/youtube/player_parameters
 * @author James
 */
class YouTube extends VideoProvider {

    /**
     * @var bool
     */
    private static $use_nocookie = false;

    /**
     * Return ident code for this video, used to load an instance of this class
     */
    public static function getProviderCode() : string {
        return GalleryVideo::PROVIDER_YOUTUBE;
    }

    /**
     * Description for assistance in identifying the provider
     */
    public static function getProviderDescription() : string {
        return _t(
            GalleryVideo::class . ".YOUTUBE_PROVIDER_DESCRIPTION",
            "YouTube"
        );
    }

    /**
     * Return YouTube host for URL
     */
    public function getHost() : string {
        if($this->config()->get('use_nocookie')) {
            return "youtube-nocookie.com";
        } else {
            return "www.youtube.com";
        }
    }

    /**
     * Return YouTube path for URL
     */
     public function getPath($videoId = '') : string {
         if($videoId) {
             return "/embed/{$videoId}/";
         } else {
             return "";
         }
     }

    /**
     * Return YouTube default query arguments for URL
     * Provide some sensible defaults. You can override these in getEmbedURL
     */
    public function getQueryArguments() : array {
        return [
            "autoplay" => 0,
            "modestbranding" => 0,
            "fs" => 1,
            "rel" => 0 // show related videos from the same channel
        ];
    }

    /**
     * Return Embed URL for the video
     */
    public function getEmbedURL(string $videoID, array $customQueryArgs, int $videoHeight) : string {
        $query = array_merge($this->getQueryArguments(), $customQueryArgs);
        $queryString = http_build_query($query);
        return $this->getProtocol()
            . $this->getHost()
            . $this->getPath($videoID)
            . ($queryString ? "?{$queryString}" : "");
    }
}
