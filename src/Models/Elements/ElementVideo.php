<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\BaseElement;
use gorriecoe\Embed\Extensions\Embeddable;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

/**
 * Embed a video using the Embeddable extension and a URL from a supported provider
 * Note (YouTube) that this element uses the standard YouTube domain for embedding a video
 * @author James
 * @author Mark
 */
class ElementVideo extends BaseElement
{

    /**
     * @var string
     */
    private static $table_name = 'ElementVideo';

    /**
     * @var string
     */
    private static $icon = 'font-icon-block-media';

    /**
     * @var string
     */
    private static $singular_name = 'Video via embed URL';

    /**
     * @var string
     */
    private static $plural_name = 'Videos via embed URL';


    /**
     * @var string
     */
    private static $title = 'Video via embed URL';

    /**
     * @var string
     */
    private static $description = "Display a video using an embed URL";

    /**
     * @var array
     */
    private static $db = [
        'Caption' => 'Text',
        'AltVideoURL' => 'Varchar(1024)',
        'Transcript' => 'HTMLText'
    ];

    /**
     * @var string
     */
    private static $embed_folder = 'Uploads/images';

    /**
     * Element type
     */
    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Video (embed)');
    }

    /**
     * Embeddable
     */
    private static $extensions = [
        Embeddable::class,
    ];

    /**
     * List the allowed included embed types.  If null all are allowed.
     * @var array
     */
    private static $allowed_embed_types = [
        'video'
    ];

    /**
     * Defines tab to insert the embed fields into.
     * @var string
     */
    private static $embed_tab = 'Main';


    /**
     * CMS fields for editing
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['EmbedTitle', 'EmbedImage', 'EmbedDescription']);

        $fields->insertAfter(
            'EmbedSourceURL',
            HTMLEditorField::create(
                'Transcript',
                _t(
                    __CLASS__ . '.TRANSCRIPT',
                    'Transcript of video'
                )
            )
            ->setRows(12)
        );

        $fields->insertAfter(
            'EmbedSourceURL',
            TextField::create(
                'AltVideoURL',
                _t(
                    __CLASS__ . '.ALTVIDEO',
                    'Alternate video with audio captions enabled'
                )
            )
            ->setDescription('Specify a external URL')
        );

        $fields->insertAfter(
            'EmbedSourceURL',
            TextareaField::create(
                'Caption',
                _t(
                    __CLASS__ . '.CAPTION',
                    'Caption'
                )
            )
        );

        return $fields;
    }

}
