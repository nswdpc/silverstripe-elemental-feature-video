<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo\Tests;

use NSWDPC\Elemental\Models\FeaturedVideo\ElementVideo;
use SilverStripe\Dev\SapphireTest;

/**
 * Provide tests for element video
 */
class ElementVideoTest extends SapphireTest {

    protected $usesDatabase =  true;

    public function testEmbedHTML() {

        $caption = "Caption of the video";
        $altURL = "https://alt.example.com/video";
        $sourceURL = "https://youtu.be/oJL-lCzEXgI";
        $embedSrc = "https://www.youtube.com/embed/oJL-lCzEXgI?feature=oembed";

        $element =  ElementVideo::create();
        $element->EmbedSourceURL = $sourceURL;
        $element->Caption = $caption;
        $element->AltVideoURL = $altURL;
        $element->write();

        $html = $element->EmbedHTML;

        // load HTML
        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        $iframes = $doc->getElementsByTagName('iframe');
        $this->assertEquals(1, $iframes->count());
        $iframe = $iframes->item(0);
        $src = $iframe->getAttribute('src');

        $this->assertEquals($embedSrc, $src);

    }

}
