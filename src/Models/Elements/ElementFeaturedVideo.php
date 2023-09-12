<?php
namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\ElementContent;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use gorriecoe\Link\Models\Link;
use NSWDPC\InlineLinker\InlineLinkCompositeField;

/**
 * ElementFeaturedVideo adds a featured video
 */
class ElementFeaturedVideo extends ElementContent implements VideoDefaults {

    use VideoFromProvider;

    /**
     * @var string
     */
    private static $icon = 'font-icon-block-media';

    /**
     * @var string
     */
    private static $table_name = 'ElementFeaturedVideo';

    /**
     * @var string
     */
    private static $title = 'Feature video';

    /**
     * @var string
     */
    private static $description = "Display a feature video";

    /**
     * @var string
     */
    private static $singular_name = 'FeaturedVideo';

    /**
     * @var string
     */
    private static $plural_name = 'FeaturedVideos';

    /**
     * Element type
     */
    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Video (feature)');
    }

    /**
     * @var array
     */
    private static $db = [
        'Video' => 'Varchar(255)',
        'Provider' => 'Varchar',
        'Width' => 'Int',
        'Height' => 'Int',
        'Transcript' => 'HTMLText'
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Image' => Image::class,
        'FeatureLink' => Link::class // an optional link for more information
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Title' => 'Title',
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Image'
    ];

    /**
     * @var int
     */
    private static $default_thumb_width = 1200;

    /**
     * @var int
     */
    private static $default_thumb_height = 0;

    /**
     * Default height of video, if none specified
     * @var int
     */
    const DEFAULT_HEIGHT = 600;

    /**
     * Return thumbnail width
     */
    public function getThumbWidth() : int {
        $width = $this->Width;
        if($width <= 0) {
            $width = $this->config()->get('default_thumb_width');
        }
        return $width;
    }

    /**
     * Return thumbnail height
     */
    public function getThumbHeight() : int {
        $height = $this->Height;
        if($height <= 0) {
            $height = $this->config()->get('default_thumb_height');
        }
        return $height;
    }

    /**
     * Return default video height
     * @return int
     */
    public function getDefaultVideoHeight() : int {
        return self::DEFAULT_HEIGHT;
    }

    /**
     * Cover image for link
     */
    public function getCoverImage() : ?DBFile {
        $image = $this->Image();
        if($image && $image->exists()) {
            $width = $this->getThumbWidth();
            $height = $this->getThumbHeight();
            if($width > 0 && $height > 0) {
                $coverImage = $image->FillMax( $width, $height );
                return $coverImage;
            } else if($width > 0) {
                $coverImage = $image->ScaleWidth( $width );
                return $coverImage;
            } else {
                return $image;
            }
        }
        return null;
    }

    /**
     * Return thumbnail allowed types
     */
    public function getAllowedFileTypes() : array {
        $types = $this->config()->get('allowed_file_types');
        if(empty($types)) {
            $types = ["jpg","jpeg","gif","png","webp"];
        }
        $types = array_unique($types);
        return $types;
    }

    /**
     * Handle before write
     */
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
    public function getVideoProviders() : array {
        return GalleryVideo::create()->getVideoProviders();
    }

    /**
     * Return fields for the CMS
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['FeatureLink','FeatureLinkID']);
        $fields->addFieldsToTab(
            'Root.Main', [
                OptionsetField::create(
                    'Provider',
                    _t(__CLASS__ . '.PROVIDER', 'Video provider'),
                    $this->getVideoProviders()
                ),
                TextField::create(
                    'Video',
                    _t(
                        __CLASS__ . 'VideoID', 'Video ID'
                    )
                ),
                InlineLinkCompositeField::create(
                    'FeatureLink',
                    _t(
                        __CLASS__ . 'LINK', 'Link'
                    ),
                    $this->owner
                ),
                UploadField::create(
                    'Image',
                    _t(
                        __CLASS__ . '.SLIDE_IMAGE',
                        'Cover image'
                    )
                )->setFolderName('videos/' . $this->ID)
                ->setAllowedExtensions($this->getAllowedFileTypes())
                ->setDescription(
                    _t(
                        __CLASS__ . 'ALLOWED_FILE_TYPES',
                        'Upload an image to use as a cover image. Allowed file types: {allowedFileTypes}',
                        [
                            'allowedFileTypes' => implode(",", $this->getAllowedFileTypes())
                        ]
                    )
                ),
                NumericField::create(
                    'Width',
                    _t(
                        __CLASS__ . '.IMAGE_WIDTH', 'Image width'
                    )
                )->setHTML5(true)->setDescription(
                    _t(
                        __CLASS__ . '.IMAGE_WIDTH_DESCRIPTION', 'Enter a width to restrict the image. Leave the width and height as zero to return the original image as the cover image'
                    )
                ),
                NumericField::create(
                    'Height',
                    _t(
                        __CLASS__ . '.IMAGE_HEIGHT', 'Image height'
                    )
                )->setHTML5(true)->setDescription(
                    _t(
                        __CLASS__ . '.IMAGE_HEIGHT_DESCRIPTION', 'Enter a height to restrict the image, or leave as zero to scale by width only'
                    )
                )
            ]
        );


        $fields->insertAfter(
            'Image',
            HTMLEditorField::create(
                'Transcript',
                _t(
                    __CLASS__ . '.TRANSCRIPT',
                    'Transcript of video'
                )
            )
        );

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function forTemplate($holder = true)
    {
        $this->addEmbedRequirements();
        return parent::forTemplate($holder);
    }

}
