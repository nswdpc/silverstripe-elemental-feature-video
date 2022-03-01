<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

/**
 * Vimeo provider, based on https://vimeo.zendesk.com/hc/en-us/articles/360001494447-Player-parameters-overview
 * @author James
 */
class Vimeo extends VideoProvider {

    /**
     * Return ident code for this video, used to load an instance of this class
     */
    public static function getProviderCode() : string {
        return GalleryVideo::PROVIDER_VIMEO;
    }

    /**
     * Description for assistance in identifying the provider
     */
    public static function getProviderDescription() : string {
        return _t(
            GalleryVideo::class . ".VIMEO_PROVIDER_DESCRIPTION",
            "Vimeo"
        );
    }

    /**
     * Return Vimeo host for URL
     */
    public function getHost() : string {
        return "player.vimeo.com";
    }

    /**
     * Return Vimeo path for URL
     */
     public function getPath($videoId = '') : string {
         if($videoId) {
             return "/video/{$videoId}/";
         } else {
             return "";
         }
     }

    /**
     * Return Vimeo default query arguments for URL
     * See: https://vimeo.zendesk.com/hc/en-us/articles/360001494447-Player-parameters-overview
     */
    public function getQueryArguments() : array {
        return [
            "autoplay" => 0,
            "color" => "ffffff",
            "title" => 0,
            "byline" => 0,
            "portrait" => 1,
            "dnt" => 1,
            "transparent" => 0
        ];
    }

    /**
     * Return Embed URL for the video
     */
    public function getEmbedURL(string $videoID, array $customQueryArgs, int $videoHeight) : string {
        if($videoHeight > 0) {
            $customQueryArgs['height'] = $videoHeight;
        }
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
    public function getWatchURL(string $videoID, array $customQueryArgs) : string {
        $query = [];
        $query = array_merge($query, $customQueryArgs);
        $queryString = http_build_query($query);
        $videoID = htmlspecialchars($videoID);
        return "https://www.vimeo.com/{$videoID}"
            . ($queryString ? "?{$queryString}" : "");
    }
}
