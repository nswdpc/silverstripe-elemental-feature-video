<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;

/**
 * Abstract class for all video providers
 * @author James
 */
abstract class VideoProvider
{
    use Configurable;

    use Injectable;

    /**
     * Default to https://
     */
    public function getProtocol(): string
    {
        return "https://";
    }

    abstract public static function getProviderCode(): string;

    abstract public static function getProviderDescription(): string;

    abstract public function getHost(): string;

    abstract public function getPath(string $videoId = ''): string;

    abstract public function getQueryArguments(): array;

    abstract public function getEmbedURL(string $videoId, array $customQueryArgs, int $videoHeight): string;

    abstract public function getWatchURL(string $videoID, array $customQueryArgs): string;


    /**
     * Optional JS/CSS requirements for provider
     */
    public function addEmbedRequirements(): bool
    {
        return false;
    }

    /**
     * Return the available providers, subclasses of this class
     */
    public static function getProviders(): array
    {
        return ClassInfo::subclassesFor(self::class, false);
    }

    /**
     * Return the VideoProvider class for the given code
     */
    public static function getProvider($code): ?VideoProvider
    {
        $providers = self::getProviders();
        foreach($providers as $providerClass) {
            if($providerClass::getProviderCode() == $code) {
                return Injector::inst()->create($providerClass);
            }
        }

        return null;
    }

    /**
     * Return an array allowing selection of a provider from a field
     */
    public static function getProviderSelections(): array
    {
        $selection = [];
        $providers = self::getProviders();
        foreach($providers as $providerClass) {
            $inst = Injector::inst()->create($providerClass);
            $selection[ $inst->getProviderCode() ] = $inst->getProviderDescription();
        }

        return $selection;
    }

    /**
     * Return the provider code for this video from an instance of this provider
     */
    public function getVideoProviderCode(): string
    {
        return static::getProviderCode();
    }

}
