<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\ElementContent;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Symbiote\MultiValueField\Fields\KeyValueField;
use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;
use SilverStripe\LinkField\Models\Link as CoreLink;
use SilverStripe\LinkField\Form\LinkField as CoreLinkField;

/**
 * ElementFeaturedVideo adds a featured video
 * @property ?string $Video
 * @property ?string $Provider
 * @property int $Width
 * @property int $Height
 * @property ?string $Transcript
 * @property int $ImageID
 * @property int $FeatureLinkID
 * @method \SilverStripe\Assets\Image Image()
 * @method \gorriecoe\Link\Models\Link FeatureLink()
 * @method \SilverStripe\LinkField\Models\Link CoreFeatureLink()
 * @property int $CoreFeatureLinkID
 * @property mixed $CustomQueryArgs
 */
class ElementFeaturedVideo extends ElementContent implements VideoDefaults
{
    use VideoFromProvider;

    private static string $icon = 'font-icon-block-media';

    private static string $table_name = 'ElementFeaturedVideo';

    private static string $title = 'Feature video';

    private static string $class_description = "Display a feature video";

    private static string $singular_name = 'FeaturedVideo';

    private static string $plural_name = 'FeaturedVideos';

    /**
     * Element type
     */
    #[\Override]
    public function getType()
    {
        return _t(self::class . '.BlockType', 'Video (feature)');
    }

    private static array $db = [
        'Video' => 'Varchar(255)',
        'Provider' => 'Varchar',
        'Width' => 'Int',
        'Height' => 'Int',
        'Transcript' => 'HTMLText',
        'CustomQueryArgs' => 'MultiValueField',
    ];

    private static array $has_one = [
        'Image' => Image::class,
        'FeatureLink' => Link::class,
        'CoreFeatureLink' => CoreLink::class
    ];

    private static array $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Title' => 'Title',
    ];

    private static array $owns = [
        'Image',
        'CoreFeatureLink'
    ];

    private static array $cascade_deletes = [
        'CoreFeatureLink'
    ];

    private static array $cascade_duplicates = [
        'CoreFeatureLink'
    ];

    private static int $default_thumb_width = 1200;

    private static int $default_thumb_height = 0;

    private static bool $inline_editable = false;

    /**
     * Default height of video, if none specified
     * @var int
     */
    public const DEFAULT_HEIGHT = 600;

    /**
     * Return thumbnail width
     */
    public function getThumbWidth(): int
    {
        $width = $this->Width;
        if ($width <= 0) {
            $width = $this->config()->get('default_thumb_width');
        }

        return $width;
    }

    /**
     * Return thumbnail height
     */
    public function getThumbHeight(): int
    {
        $height = $this->Height;
        if ($height <= 0) {
            $height = $this->config()->get('default_thumb_height');
        }

        return $height;
    }

    /**
     * Return default video height
     */
    public function getDefaultVideoHeight(): int
    {
        return self::DEFAULT_HEIGHT;
    }

    /**
     * Cover image for link
     */
    public function getCoverImage(): ?AssetContainer
    {
        $image = $this->Image();
        if ($image && $image->exists()) {
            $width = $this->getThumbWidth();
            $height = $this->getThumbHeight();
            if ($width > 0 && $height > 0) {
                return $image->FillMax($width, $height);
            } elseif ($width > 0) {
                return $image->ScaleWidth($width);
            } else {
                return $image;
            }
        }

        return null;
    }

    /**
     * Return thumbnail allowed types
     */
    public function getAllowedFileTypes(): array
    {
        $types = $this->config()->get('allowed_file_types');
        if (empty($types)) {
            $types = ["jpg","jpeg","gif","png","webp"];
        }

        return array_unique($types);
    }

    /**
     * Handle before write
     */
    #[\Override]
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->validateVideoCode($this->Video);
        $this->Width = $this->getThumbWidth();
        $this->Height = $this->getThumbHeight();
    }

    /**
     * Get available video providers
     */
    public function getVideoProviders(): array
    {
        return GalleryVideo::create()->getVideoProviders();
    }

    /**
     * Return fields for the CMS
     */
    #[\Override]
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['FeatureLink','FeatureLinkID','CoreFeatureLink']);
        $fields->addFieldsToTab(
            'Root.Main',
            [
                OptionsetField::create(
                    'Provider',
                    _t(self::class . '.PROVIDER', 'Video provider'),
                    $this->getVideoProviders()
                ),
                TextField::create(
                    'Video',
                    _t(
                        self::class . 'VideoID',
                        'Video ID'
                    )
                ),
                $this->getLinkField(),
                UploadField::create(
                    'Image',
                    _t(
                        self::class . '.SLIDE_IMAGE',
                        'Cover image'
                    )
                )->setFolderName('videos/' . $this->ID)
                ->setAllowedExtensions($this->getAllowedFileTypes())
                ->setDescription(
                    _t(
                        self::class . 'ALLOWED_FILE_TYPES',
                        'Upload an image to use as a cover image. Allowed file types: {allowedFileTypes}',
                        [
                            'allowedFileTypes' => implode(",", $this->getAllowedFileTypes())
                        ]
                    )
                ),
                NumericField::create(
                    'Width',
                    _t(
                        self::class . '.IMAGE_WIDTH',
                        'Image width'
                    )
                )->setHTML5(true)->setDescription(
                    _t(
                        self::class . '.IMAGE_WIDTH_DESCRIPTION',
                        'Enter a width to restrict the image. Leave the width and height as zero to return the original image as the cover image'
                    )
                ),
                NumericField::create(
                    'Height',
                    _t(
                        self::class . '.IMAGE_HEIGHT',
                        'Image height'
                    )
                )->setHTML5(true)->setDescription(
                    _t(
                        self::class . '.IMAGE_HEIGHT_DESCRIPTION',
                        'Enter a height to restrict the image, or leave as zero to scale by width only'
                    )
                )
            ]
        );


        $fields->insertAfter(
            'Image',
            HTMLEditorField::create(
                'Transcript',
                _t(
                    self::class . '.TRANSCRIPT',
                    'Transcript of video'
                )
            )
        );


        $fields->insertAfter(
            'Video',
            KeyValueField::create(
                'CustomQueryArgs',
                _t(
                    self::class . '.CUSTOM_QUERY_ARGS',
                    'Custom parameters for the video URL'
                )
            )
        );

        return $fields;
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function forTemplate($holder = true): string
    {
        $this->addEmbedRequirements();
        return parent::forTemplate($holder);
    }

    /**
     * Return the field used to render the link
     * based on migration state
     */
    protected function getLinkField(): CoreLinkField|LinkField
    {
        $link = $this->getDerivedFeatureLink();
        if ($link instanceof CoreLink) {
            return CoreLinkField::create('CoreFeatureLink', _t(self::class . '.LINK', 'Link'));
        }

        return LinkField::create('FeatureLink', _t(self::class . '.LINK', 'Link'), $this);
    }

    /**
     * Template method
     */
    public function DerivedFeatureLink(): CoreLink|Link
    {
        return $this->getDerivedFeatureLink();
    }

    /**
     * Returns the relevant link based on migration status
     */
    public function getDerivedFeatureLink(): CoreLink|Link
    {
        $coreLink = $this->CoreFeatureLink();
        /** @var Link $legacyLink */
        $legacyLink = $this->getComponent('FeatureLink');
        if ($coreLink->isInDB() || !$legacyLink->isInDB()) {
            return $coreLink;
        }

        return $legacyLink;
    }

}
