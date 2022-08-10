<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo\Tests;

use NSWDPC\Elemental\Models\FeaturedVideo\GalleryVideo;
use NSWDPC\Elemental\Models\FeaturedVideo\YouTube;
use NSWDPC\Elemental\Models\FeaturedVideo\Vimeo;
use NSWDPC\Elemental\Models\FeaturedVideo\YouTubeNoCookie;
use NSWDPC\Elemental\Models\FeaturedVideo\VideoProvider;
use SilverStripe\Control\Director;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationException;

/**
 * Provide tests for a single gallery video
 */
class GalleryVideoTest extends SapphireTest {

    protected $usesDatabase =  true;

    public function testValidationWrite() {
        try {
            $video = GalleryVideo::create();
            $video->Provider = GalleryVideo::PROVIDER_YOUTUBE;
            // provide a URL as a video embed code, fail on validation
            $video->Video = "https://www.youtube.com/embed/123456/";
            $video->Title = "YouTube Test";
            $video->Description = "YouTube Description";
            $video->Transcript = "<p>YouTube Transcript</p>";
            $video->write();
            $this->assertFalse($video->IsInDB());
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function testVideoHeight() {

        $video = GalleryVideo::create();
        $video->Provider = GalleryVideo::PROVIDER_YOUTUBE;
        $video->Video = "testyoutube";
        $video->Title = "YouTube Test";
        $video->Description = "YouTube Description";
        $video->Transcript = "<p>YouTube Transcript</p>";
        $video->write();

        $height = 412;

        $video->setVideoHeight($height);

        $this->assertEquals($height, $video->getVideoHeight());
    }

    public function testYouTube() {

        $videoId = "CyHMQ6iS3rY";

        $video = GalleryVideo::create();
        $video->Provider = GalleryVideo::PROVIDER_YOUTUBE;
        $video->Video = $videoId;
        $video->Title = "YouTube Test";
        $video->Description = "YouTube Description";
        $video->Transcript = "<p>YouTube Transcript</p>";
        $video->UseVideoThumbnail = 1;
        $video->write();

        $provider = VideoProvider::getProvider( $video->Provider );

        $this->assertInstanceOf( YouTube::class, $provider );

        $url = $video->EmbedURL();

        $this->assertNotEmpty($url, "URL empty");

        $parts = parse_url($url);

        $this->assertEquals("www.youtube.com", $parts['host']);
        $this->assertEquals("https", $parts['scheme']);
        $this->assertEquals("/embed/{$videoId}/", $parts['path']);

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

        $this->assertEquals( $expected, $query );

        // validate the video has a thumbnail as a URL
        $this->assertNotFalse( filter_var( $video->VideoThumbnail, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) );

    }

    public function testYouTubeNoCookie() {

        $videoId = "vRl_jDVF-eo";

        $video = GalleryVideo::create();
        $video->Provider = GalleryVideo::PROVIDER_YOUTUBE_NOCOOKIE;
        $video->Video = $videoId;
        $video->Title = "YouTube NoCookie Test";
        $video->Description = "YouTube NoCookie Description";
        $video->Transcript = "<p>YouTube NoCookie Transcript</p>";
        $video->write();

        $provider = VideoProvider::getProvider( $video->Provider );

        $this->assertInstanceOf( YouTubeNoCookie::class, $provider );

        $url = $video->EmbedURL();

        $this->assertNotEmpty($url, "URL empty");

        $parts = parse_url($url);

        $this->assertEquals("www.youtube-nocookie.com", $parts['host']);
        $this->assertEquals("https", $parts['scheme']);
        $this->assertEquals("/embed/{$videoId}/", $parts['path']);

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

        $this->assertEquals( $expected, $query );

        // validate the video has a thumbnail as a URL
        $this->assertNotFalse( filter_var( $video->VideoThumbnail, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) );

    }

    public function testVimeo() {

        $videoId = "700166879";

        $video = GalleryVideo::create();
        $video->Provider = GalleryVideo::PROVIDER_VIMEO;
        $video->Video = $videoId;
        $video->Title = "Vimeo Test";
        $video->Description = "Vimeo Description";
        $video->Transcript = "<p>Vimeo Transcript</p>";
        $video->write();

        $provider = VideoProvider::getProvider( $video->Provider );

        $this->assertInstanceOf( Vimeo::class, $provider );

        $url = $video->EmbedURL();

        $this->assertNotEmpty($url, "URL empty");

        $parts = parse_url($url);

        $this->assertEquals("player.vimeo.com", $parts['host']);
        $this->assertEquals("https", $parts['scheme']);
        $this->assertEquals("/video/{$videoId}/", $parts['path']);

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

        $this->assertEquals( $expected, $query );

        // validate the video has a thumbnail as a URL
        $this->assertNotFalse( filter_var( $video->VideoThumbnail, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) );

    }
}
