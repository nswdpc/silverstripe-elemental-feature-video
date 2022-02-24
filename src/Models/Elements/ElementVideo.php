<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\BaseElement;
use gorriecoe\Embed\Extensions\Embeddable;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;


class ElementVideo extends BaseElement
{

    private static $table_name = 'ElementVideo';

    private static $icon = 'font-icon-block-media';

    private static $singular_name = 'video';

    private static $plural_name = 'videos';

    private static $db = [
        'Caption' => 'Text',
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
                'Caption'
            )
        );


        return $fields;
    }

}
