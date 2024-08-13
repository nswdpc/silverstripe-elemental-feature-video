<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo\Tests;

use NSWDPC\Elemental\Models\FeaturedVideo\YouTube;
use NSWDPC\Elemental\Models\FeaturedVideo\Vimeo;
use NSWDPC\Elemental\Models\FeaturedVideo\YouTubeNoCookie;
use NSWDPC\Elemental\Models\FeaturedVideo\VideoProvider;
use NSWDPC\Elemental\Models\FeaturedVideo\ElementFeaturedVideo;
use SilverStripe\Control\Director;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;

/**
 * Provide tests for element featured video
 */
class ElementFeaturedVideoTest extends SapphireTest
{
    protected $usesDatabase =  true;

    public function testThumbWidthHeight(): void
    {
        $video = ElementFeaturedVideo::create();
        $video->Provider = YouTube::getProviderCode();
        $video->Video = "testyoutube";
        $video->Title = "YouTube Test";
        $video->Description = "YouTube Description";
        $video->Transcript = "<p>YouTube Transcript</p>";
        $video->Width = 1201;
        $video->Height = 603;
        $video->write();

        $this->assertEquals(1201, $video->getThumbWidth());
        $this->assertEquals(603, $video->getThumbHeight());
    }

    public function testDefaultThumbWidthHeight(): void
    {
        $video = ElementFeaturedVideo::create();
        $video->Provider = YouTube::getProviderCode();
        $video->Video = "testyoutube";
        $video->Title = "YouTube Test";
        $video->Description = "YouTube Description";
        $video->Transcript = "<p>YouTube Transcript</p>";
        $video->Width = 0;
        $video->Height = 0;
        $video->write();

        $this->assertEquals($video->config()->get('default_thumb_width'), $video->getThumbWidth());
        $this->assertEquals($video->config()->get('default_thumb_height'), $video->getThumbHeight());
    }

    public function testValidationWrite(): void
    {
        try {
            $video = ElementFeaturedVideo::create();
            $video->Provider = YouTube::getProviderCode();
            // provide a URL as a video embed code, fail on validation
            $video->Video = "https://www.youtube.com/embed/123456/";
            $video->Title = "YouTube Test";
            $video->Description = "YouTube Description";
            $video->Transcript = "<p>YouTube Transcript</p>";
            $video->write();
            $this->assertFalse($video->IsInDB());
        } catch (ValidationException $validationException) {
            $this->assertNotEmpty($validationException->getMessage());
        }
    }

    public function testVideoHeight(): void
    {

        $video = ElementFeaturedVideo::create();
        $video->Provider = YouTube::getProviderCode();
        $video->Video = "testyoutube";
        $video->Title = "YouTube Test";
        $video->Description = "YouTube Description";
        $video->Transcript = "<p>YouTube Transcript</p>";
        $video->write();

        $height = 412;

        $video->setVideoHeight($height);

        $this->assertEquals($height, $video->getVideoHeight());
    }

    public function testYouTube(): void
    {

        $video = ElementFeaturedVideo::create();
        $video->Provider = YouTube::getProviderCode();
        $video->Video = "testyoutube";
        $video->Title = "YouTube Test";
        $video->Description = "YouTube Description";
        $video->Transcript = "<p>YouTube Transcript</p>";
        $video->write();

        $this->assertEquals(YouTube::getProviderCode(), $video->getVideoProviderCode());

        $provider = VideoProvider::getProvider($video->Provider);

        $this->assertInstanceOf(YouTube::class, $provider);

        $url = $video->EmbedURL();

        $this->assertNotEmpty($url, "URL empty");

        $parts = parse_url($url);

        $this->assertEquals("www.youtube.com", $parts['host']);
        $this->assertEquals("https", $parts['scheme']);
        $this->assertEquals("/embed/testyoutube/", $parts['path']);

        $this->assertNotEmpty($parts['query']);

        parse_str($parts['query'], $query);

        $expected = [
            "autoplay" => 0,
            "modestbranding" => 0,
            "fs" => 1,
            "rel" => 0,
            "enablejsapi" => 1,
            "origin" => Director::protocolAndHost()
        ];

        asort($query);
        asort($expected);

        $this->assertEquals($expected, $query);

    }

    public function testYouTubeNoCookie(): void
    {

        $video = ElementFeaturedVideo::create();
        $video->Provider = YouTubeNoCookie::getProviderCode();
        $video->Video = "testyoutube-nocookie";
        $video->Title = "YouTube NoCookie Test";
        $video->Description = "YouTube NoCookie Description";
        $video->Transcript = "<p>YouTube NoCookie Transcript</p>";
        $video->write();

        $this->assertEquals(YouTubeNoCookie::getProviderCode(), $video->getVideoProviderCode());

        $provider = VideoProvider::getProvider($video->Provider);

        $this->assertInstanceOf(YouTubeNoCookie::class, $provider);

        $url = $video->EmbedURL();

        $this->assertNotEmpty($url, "URL empty");

        $parts = parse_url($url);

        $this->assertEquals("www.youtube-nocookie.com", $parts['host']);
        $this->assertEquals("https", $parts['scheme']);
        $this->assertEquals("/embed/testyoutube-nocookie/", $parts['path']);

        $this->assertNotEmpty($parts['query']);

        parse_str($parts['query'], $query);

        $expected = [
            "autoplay" => 0,
            "modestbranding" => 0,
            "fs" => 1,
            "rel" => 0,
            "enablejsapi" => 1,
            "origin" => Director::protocolAndHost()
        ];

        asort($query);
        asort($expected);

        $this->assertEquals($expected, $query);

    }

    public function testVimeo(): void
    {

        $video = ElementFeaturedVideo::create();
        $video->Provider = Vimeo::getProviderCode();
        $video->Video = "testvimeo";
        $video->Title = "Vimeo Test";
        $video->Description = "Vimeo Description";
        $video->Transcript = "<p>Vimeo Transcript</p>";
        $video->write();

        $this->assertEquals(Vimeo::getProviderCode(), $video->getVideoProviderCode());

        $provider = VideoProvider::getProvider($video->Provider);

        $this->assertInstanceOf(Vimeo::class, $provider);

        $url = $video->EmbedURL();

        $this->assertNotEmpty($url, "URL empty");

        $parts = parse_url($url);

        $this->assertEquals("player.vimeo.com", $parts['host']);
        $this->assertEquals("https", $parts['scheme']);
        $this->assertEquals("/video/testvimeo/", $parts['path']);

        $this->assertNotEmpty($parts['query']);

        parse_str($parts['query'], $query);

        $expected = [
            "autoplay" => 0,
            "color" => "ffffff",
            "title" => 0,
            "byline" => 0,
            "portrait" => 1,
            "dnt" => 1,
            "transparent" => 0,
            "height" => $video->getVideoHeight()
        ];

        asort($query);
        asort($expected);

        $this->assertEquals($expected, $query);

    }

}
