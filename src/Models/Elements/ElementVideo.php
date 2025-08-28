<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\BaseElement;
use NSWDPC\Embed\Extensions\Embeddable;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

/**
 * Embed a video using the Embeddable extension and a URL from a supported provider
 * Note (YouTube) that this element uses the standard YouTube domain for embedding a video
 * @author James
 * @author Mark
 * @property ?string $Caption
 * @property ?string $AltVideoURL
 * @property ?string $Transcript
 * @mixin \NSWDPC\Embed\Extensions\Embeddable
 */
class ElementVideo extends BaseElement
{
    private static string $table_name = 'ElementVideo';

    private static string $icon = 'font-icon-block-media';

    private static string $singular_name = 'Video via embed URL';

    private static string $plural_name = 'Videos via embed URL';


    private static string $title = 'Video via embed URL';

    private static string $description = "Display a video using an embed URL";

    private static array $db = [
        'Caption' => 'Text',
        'AltVideoURL' => 'Varchar(1024)',
        'Transcript' => 'HTMLText'
    ];

    private static string $embed_folder = 'Uploads/images';

    /**
     * Element type
     */
    #[\Override]
    public function getType()
    {
        return _t(self::class . '.BlockType', 'Video (embed)');
    }

    /**
     * Embeddable
     */
    private static array $extensions = [
        Embeddable::class,
    ];

    /**
     * List the allowed included embed types.  If null all are allowed.
     */
    private static array $allowed_embed_types = [
        'video'
    ];

    /**
     * Defines tab to insert the embed fields into.
     */
    private static string $embed_tab = 'Main';

    /**
     * Override the return of EmbedType
     */
    public function getEmbedType(): string
    {
        return Embeddable::EMBED_TYPE_VIDEO;
    }

    /**
     * CMS fields for editing
     */
    #[\Override]
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['EmbedTitle', 'EmbedImage', 'EmbedDescription']);

        $fields->push(
            TextareaField::create(
                'Caption',
                _t(
                    self::class . '.CAPTION',
                    'Caption'
                )
            )
        );

        $fields->insertAfter(
            'Caption',
            HTMLEditorField::create(
                'Transcript',
                _t(
                    self::class . '.TRANSCRIPT',
                    'Transcript of video'
                )
            )
            ->setRows(12)
        );

        $fields->insertAfter(
            'Caption',
            TextField::create(
                'AltVideoURL',
                _t(
                    self::class . '.ALTVIDEO',
                    'Alternate video with audio captions enabled'
                )
            )->setDescription(
                _t(
                    self::class . '.SPECIFY_EXTERNAL_URL',
                    'Specify an external URL'
                )
            )
        );

        return $fields;
    }

}
