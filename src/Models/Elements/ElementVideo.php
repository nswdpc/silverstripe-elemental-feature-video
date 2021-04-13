<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\BaseElement;
use gorriecoe\Embed\Extensions\Embeddable;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;


class ElementVideo extends BaseElement
{

    private static $table_name = 'ElementVideo';

    private static $icon = 'font-icon-block-banner';

    private static $singular_name = 'video';

    private static $plural_name = 'videos';

    private static $db = [
        'AltVideoURL' => 'Varchar(1024)',
        'Transcript' => 'HTMLText'
    ];

    private static $embed_folder = 'Uploads/images';

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Video');
    }

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


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['EmbedImage', 'EmbedDescription']);

        $fields->insertAfter(
            'EmbedSourceURL',
            HTMLEditorField::create(
                'Transcript',
                _t(
                    __CLASS__ . '.TRANSCRIPT',
                    'Transcript of video'
                )
            )
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
        );

        return $fields;
    }

}
