<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Control\Controller;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Symbiote\MultiValueField\Fields\KeyValueField;
use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;
use SilverStripe\LinkField\Models\Link as CoreLink;
use SilverStripe\LinkField\Form\LinkField as CoreLinkField;

/**
 * Images in an ElementVideo
 * @property string $Title
 * @property ?string $Video
 * @property ?string $Provider
 * @property ?string $Description
 * @property int $Sort
 * @property ?string $Transcript
 * @property ?string $VideoThumbnail
 * @property bool $UseVideoThumbnail
 * @property int $ImageID
 * @property int $ParentID
 * @property int $LinkTargetID
 * @method \SilverStripe\Assets\Image Image()
 * @method \NSWDPC\Elemental\Models\FeaturedVideo\ElementVideoGallery Parent()
 * @method \gorriecoe\Link\Models\Link LinkTarget()
 * @method \SilverStripe\LinkField\Models\Link CoreLinkTarget()
 * @property int $CoreLinkTargetID
 * @mixin \SilverStripe\Versioned\Versioned
 * @property mixed $CustomQueryArgs
 */
class GalleryVideo extends DataObject implements VideoDefaults
{
    use VideoFromProvider;

    private static string $table_name = 'GalleryVideo';

    private static bool $versioned_gridfield_extensions = true;

    private static string $singular_name = 'Video';

    private static string $plural_name = 'Videos';

    private static string $default_sort = 'Sort';

    private static array $allowed_file_types = ["jpg","jpeg","gif","png","webp"];

    private static string $folder_name = 'videos';

    private static string $allow_attribute = 'autoplay; fullscreen; picture-in-picture';

    /**
     * @var string
     */
    public const PROVIDER_VIMEO = 'vimeo';

    /**
     * @var string
     */
    public const PROVIDER_YOUTUBE = 'youtube';

    /**
     * @var string
     */
    public const PROVIDER_YOUTUBE_NOCOOKIE = 'youtube-nocookie';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'Video' => 'Varchar(255)',
        'Provider' => 'Varchar',
        'Description' => 'Text',
        'Sort' => 'Int',
        'Transcript' => 'HTMLText',
        'VideoThumbnail' => 'Varchar(255)',
        'UseVideoThumbnail' => 'Boolean',
        'CustomQueryArgs' => 'MultiValueField',
    ];

    private static array $has_one = [
        'Image' => Image::class,
        'Parent' => ElementVideoGallery::class,
        'LinkTarget' => Link::class,
        'CoreLinkTarget' => CoreLink::class
    ];

    private static array $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Title' => 'Title',
        'Video' => 'Video Id',
        'Provider' => 'Provider',
        'Parent.DropdownTitle' => 'Gallery'
    ];

    private static array $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'Video' => 'PartialMatchFilter',
        'Provider' => 'PartialMatchFilter',
        'Description' => 'PartialMatchFilter'
    ];

    private static array $owns = [
        'Image',
        'CoreLinkTarget'
    ];

    private static array $cascade_deletes = [
        'CoreLinkTarget'
    ];

    private static array $cascade_duplicates = [
        'CoreLinkTarget'
    ];

    private static array $extensions = [
        Versioned::class
    ];

    /**
     * Default height of video, if none specified
     * @var int
     */
    public const DEFAULT_HEIGHT = 360;

    /**
     * Allowed file types for the video image
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
     * Folder name for uploaded thumbnails
     */
    public function getFolderName(): string
    {
        $folder_name = $this->config()->get('folder_name');
        if (!$folder_name) {
            $folder_name = "videos";
        }

        return $folder_name;
    }

    /**
     * Apply changes on write
     */
    #[\Override]
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->validateVideoCode($this->Video);
        // update the OEmbed image value
        $this->VideoThumbnail = $this->getOEmbedImage();
    }

    /**
     * CMS fields for video management
     *
     */
    #[\Override]
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['ParentID', 'LinkTargetID', 'CoreLinkTarget', 'Sort']);

        if (Controller::curr() instanceof VideoAdmin) {
            $fields->addFieldToTab(
                'Root.Main',
                DropdownField::create(
                    'ParentID',
                    _t(self::class . '.CHOOSE_A_GALLERY', 'Choose a video gallery'),
                    ElementVideoGallery::get()
                        ->sort('Title ASC')
                        ->map("ID", "DropdownTitle")
                )->setDescription(
                    _t(
                        self::class . '.CHOOSE_A_GALLERY_DESCRIPTION',
                        'Changing the gallery will move the video to that gallery'
                    ),
                )->setEmptyString(''),
                'Video'
            );
        }


        $description = '';
        if ($this->Video && $this->Provider) {
            $embedURL = $this->EmbedURL();
            if ($embedURL !== '') {
                $description = _t(
                    self::class . ".VIDEO_EMBED_URL",
                    "The following URL will be used: <code>{embedURL}</code>",
                    [
                        'embedURL' => $embedURL
                    ]
                );
            }
        }

        $fields->addFieldsToTab(
            'Root.Main',
            [
                OptionsetField::create(
                    'Provider',
                    _t(self::class . '.PROVIDER', 'Choose a video source'),
                    $this->getVideoProviders()
                ),
                TextField::create(
                    'Video',
                    _t(
                        self::class . 'VIDEO_ID_VALUE',
                        'Enter the video identifier/code. The embed URL for the video will be automatically created based on this value.'
                    )
                )->setRightTitle(
                    _t(
                        self::class . ".VIDEO_EMBED_DESCRIPTION",
                        "Example: oJL-lCzEXgI from  https://www.youtube.com/embed/oJL-lCzEXgI"
                    )
                )->setDescription(
                    $description
                ),
                TextareaField::create(
                    'Description',
                    _t(
                        self::class . 'DESCRIPTION',
                        'Description'
                    )
                ),
                UploadField::create(
                    'Image',
                    _t(
                        self::class . '.SLIDE_IMAGE',
                        'Image'
                    )
                )->setFolderName($this->getFolderName() . '/' . $this->ID)
                ->setAllowedExtensions($this->getAllowedFileTypes())
                ->setDescription(
                    _t(
                        self::class . 'ALLOWED_FILE_TYPES',
                        'Allowed file types: {types}',
                        [
                            'types' => implode(",", $this->getAllowedFileTypes())
                        ]
                    )
                ),
                HTMLEditorField::create(
                    'Transcript',
                    _t(
                        self::class . '.TRANSCRIPT',
                        'Transcript of video'
                    )
                ),
                $this->getLinkField()
            ]
        );

        $fields->insertAfter(
            'Image',
            ReadonlyField::create(
                'VideoThumbnail',
                _t(
                    self::class . 'VIDEO_THUMBNAIL',
                    'Video thumbnail'
                ),
            )->setDescription(
                _t(
                    self::class . 'VIDEO_THUMBNAIL_DESCRIPTION',
                    'The automatically discovered video thumbnail, if found. Copy and paste the URL into a browser to view it.'
                ),
            )
        );

        if ($imageField = $fields->dataFieldByName('Image')) {
            $imageField->setTitle(
                _t(
                    self::class . 'IMAGE_SPECIFIC_THUMBNAIL',
                    'Upload an image to use as the thumbnail'
                )
            );
        }

        $fields->insertAfter(
            'VideoThumbnail',
            DropdownField::create(
                'UseVideoThumbnail',
                _t(
                    self::class . 'VIDEO_THUMBNAIL_TO_USE',
                    'Select which thumbnail to use, if it exists'
                ),
                [
                    0 => _t(self::class . 'IMAGE_UPLOADED', 'Image uploaded'),
                    1 => _t(self::class . 'VIDEO_THUMBNAIL_FOUND', 'Video thumbnail found')
                ]
            )
        );

        $fields->insertAfter(
            'VideoID',
            KeyValueField::create(
                'CustomQueryArgs',
                _t(
                    self::class . '.CUSTOM_QUERY_ARGS',
                    'Custom URL Parameters'
                )
            )
        );

        return $fields;
    }

    /**
     * Return default video height
     */
    public function getDefaultVideoHeight(): int
    {
        return self::DEFAULT_HEIGHT;
    }

    /**
     * Title for symbiote/silverstripe-multirecordfield
     * @return string
     */
    public function getMultiRecordEditingTitle()
    {
        return $this->singular_name();
    }

    /**
     * Render this record into a template
     */
    #[\Override]
    public function forTemplate(): string
    {
        $this->addEmbedRequirements();
        return $this->renderWith([$this->class, self::class]);
    }

    /**
     * Return allow="" value for <iframe>
     */
    public function AllowAttribute(): string
    {
        $value = $this->config()->get('allow_attribute');
        return is_string($value) ? $value : '';
    }

    /**
     * Return the field used to render the link
     * based on migration state
     */
    protected function getLinkField(): CoreLinkField|LinkField
    {
        $link = $this->getDerivedLinkTarget();
        if ($link instanceof CoreLink) {
            return CoreLinkField::create('CoreLinkTarget', _t(self::class . '.LINKTARGET', 'Link'));
        }

        return LinkField::create('LinkTarget', _t(self::class . '.LINKTARGET', 'Link'), $this);
    }

    /**
     * Template method
     */
    public function DerivedLinkTarget(): CoreLink|Link
    {
        return $this->getDerivedLinkTarget();
    }

    /**
     * Returns the relevant link based on migration status
     */
    public function getDerivedLinkTarget(): CoreLink|Link
    {
        $coreLink = $this->CoreLinkTarget();
        /** @var Link $legacyLink */
        $legacyLink = $this->getComponent('LinkTarget');
        if ($coreLink->isInDB() || !$legacyLink->isInDB()) {
            return $coreLink;
        }

        return $legacyLink;
    }
}
