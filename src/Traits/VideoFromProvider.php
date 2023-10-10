<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use Embed\Embed;
use Silverstripe\Core\Convert;
use SilverStripe\ORM\ValidationException;
use SilverStripe\View\Requirements;

/**
 * Common methods for sourcing a video from a provider
 * @author James
 */
trait VideoFromProvider {

    /**
     * @var int
     */
    protected $videoHeight = 0;


    /**
     * @var Extractor|null
     */
    protected $oEmbedData = null;

    /**
     * Get available video providers
     */
    public function getVideoProviders() : array {
        $providers = VideoProvider::getProviderSelections();
        // @deprecated updateVideoProviders - this will be removed in v1
        $this->extend('updateVideoProviders', $providers);
        return $providers;
    }

    /**
     * Allow some control of the video height eg. from a gallery parent
     */
    public function setVideoHeight(int $height) : self {
        $this->videoHeight = $height;
        return $this;
    }

    /**
     * Get specified height. Some providers allow a height to be set via URL arg
     */
    public function getVideoHeight() :int {
        $parent = $this->Parent();
        if($parent && $parent->VideoHeight > 0) {
            return $parent->VideoHeight;
        } elseif($this->videoHeight > 0) {
            return $this->videoHeight;
        } else {
            return $this->getDefaultVideoHeight();
        }
    }

    /**
     * Validate the video code provided
     */
    public function validateVideoCode($videoCode) {
        if(preg_match("/^http(s)?:\/\//", $videoCode)) {
            throw new ValidationException(
                _t(
                    __CLASS__ . ".VIDEO_ID_NOT_URL",
                    "Please use the video id from the embed URL, not the URL itself"
                )
            );
        }
    }

    /**
     * Method to wrap retrieval of the video id code
     */
    public function getVideoid() {
        return $this->Video;
    }

    /**
     * Add an requirements used by the video provider to support video embedding
     */
    protected function addEmbedRequirements() : void {
        if($provider = VideoProvider::getProvider( $this->Provider )) {
            // Add any provider requirements
            $provider->addEmbedRequirements();
        }
    }

    /**
     * Return the URL to embed the video in an <iframe>
     */
    public function EmbedURL() : string {
        $provider = VideoProvider::getProvider( $this->Provider );
        if($provider) {
            return $provider->getEmbedURL( $this->getVideoid(), [], $this->getVideoHeight() );
        } else {
            return "";
        }
    }

    /**
     * Return the watch URL, to link to the video offsite, eg. at the provider
     */
    public function WatchURL() : string {
        $provider = VideoProvider::getProvider( $this->Provider );
        if($provider) {
            return $provider->getWatchURL( $this->getVideoid(), [] );
        } else {
            return "";
        }
    }

    /**
     * Return allow="" value for <iframe>
     */
    public function AllowAttribute() : string {
        return '';
    }

    /**
     * Return OEmbed data from embed/embed
     * @param bool $force true = force request to be made
     * @return mixed
     */
    public function getOEmbedData($force = false) {
        try {
            if(is_null($this->oEmbedData) || $force) {
                $watchURL = $this->WatchURL();
                $reflector = new \ReflectionClass(Embed::class);
                if($reflector->isAbstract()) {
                    // embed/embed v3
                    $this->oEmbedData = Embed::create( $watchURL );
                } else {
                    // embed/embed v4
                    $embed = new Embed();
                    $this->oEmbedData = $embed->get( $watchURL );
                }
            }
        } catch (\Exception $e) {
            // some error occurred
            $this->oEmbedData = null;
        }
        return $this->oEmbedData;
    }

    /**
     * Return OEmbed image value
     */
    public function getOEmbedImage() : ?string {
        $value = null;
        try {
            $info = $this->getOEmbedData();
            if($image = $info->image) {
                $value = $info->image->__toString();
            }
        } catch (\Exception $e) {
            // oembed failure to retrieve property
        }
        return $value;
    }

    /**
     * Return current video's provider code, determined by the Provider value
     * Allows templates to use $VideoProviderCode in a data attribute
     */
    public function getVideoProviderCode() : ?string {
        $inst = VideoProvider::getProvider( $this->Provider );
        if($inst) {
            /**
             * @var VideoProvider
             */
            return $inst->getVideoProviderCode();
        } else {
            return null;
        }
    }

}
