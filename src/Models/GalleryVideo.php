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
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use gorriecoe\Link\Models\Link;
use NSWDPC\InlineLinker\InlineLinkCompositeField;
use NSWDPC\Elemental\Models\FeaturedVideo\ElementVideoGallery;

/**
 * Images in an ElementVideo
 */
class GalleryVideo extends DataObject implements VideoDefaults
{
    use VideoFromProvider;

    /**
     * @var string
     */
    private static $table_name = 'GalleryVideo';

    /**
     * @var string
     */
    private static $versioned_gridfield_extensions = true;

    /**
     * @var string
     */
    private static $singular_name = 'Video';

    /**
     * @var string
     */
    private static $plural_name = 'Videos';

    /**
     * @var string
     */
    private static $default_sort = 'Sort';

    /**
     * @var array
     */
    private static $allowed_file_types = ["jpg","jpeg","gif","png","webp"];

    /**
     * @var string
     */
    private static $folder_name = 'videos';

    /**
     * @var string
     */
    private static $allow_attribute = 'autoplay; fullscreen; picture-in-picture';

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

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'Video' => 'Varchar(255)',
        'Provider' => 'Varchar',
        'Description' => 'Text',
        'Sort' => 'Int',
        'Transcript' => 'HTMLText',
        'VideoThumbnail' => 'Varchar(255)',
        'UseVideoThumbnail' => 'Boolean'
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Image' => Image::class,
        'Parent' => ElementVideoGallery::class,
        'LinkTarget' => Link::class
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Title' => 'Title',
        'Video' => 'Video Id',
        'Provider' => 'Provider',
        'Parent.DropdownTitle' => 'Gallery'
    ];

    /**
     * @var array
     */
    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'Video' => 'PartialMatchFilter',
        'Provider' => 'PartialMatchFilter',
        'Description' => 'PartialMatchFilter'
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Image'
    ];

    /**
     * @var array
     */
    private static $extensions = [
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
        if(empty($types)) {
            $types = ["jpg","jpeg","gif","png","webp"];
        }
        $types = array_unique($types);
        return $types;
    }

    /**
     * Folder name for uploaded thumbnails
     */
    public function getFolderName(): string
    {
        $folder_name = $this->config()->get('folder_name');
        if(!$folder_name) {
            $folder_name = "videos";
        }
        return $folder_name;
    }

    /**
     * Apply changes on write
     */
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
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['ParentID', 'LinkTargetID', 'Sort']);

        if(Controller::curr() instanceof VideoAdmin) {
            $fields->addFieldToTab(
                'Root.Main',
                DropdownField::create(
                    'ParentID',
                    _t(__CLASS__ . '.CHOOSE_A_GALLERY', 'Choose a video gallery'),
                    ElementVideoGallery::get()
                        ->sort('Title ASC')
                        ->map("ID", "DropdownTitle")
                )->setDescription(
                    _t(
                        __CLASS__ . '.CHOOSE_A_GALLERY_DESCRIPTION',
                        'Changing the gallery will move the video to that gallery'
                    ),
                )->setEmptyString(''),
                'Video'
            );
        }


        $description = '';
        if($this->Video && $this->Provider) {
            $embedURL = $this->EmbedURL();
            if($embedURL) {
                $description = _t(
                    __CLASS__ . ".VIDEO_EMBED_URL",
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
                    _t(__CLASS__ . '.PROVIDER', 'Choose a video source'),
                    $this->getVideoProviders()
                ),
                TextField::create(
                    'Video',
                    _t(
                        __CLASS__ . 'VIDEO_ID_VALUE',
                        'Enter the video identifier/code. The embed URL for the video will be automatically created based on this value.'
                    )
                )->setRightTitle(
                    _t(
                        __CLASS__ . ".VIDEO_EMBED_DESCRIPTION",
                        "Example: oJL-lCzEXgI from  https://www.youtube.com/embed/oJL-lCzEXgI"
                    )
                )->setDescription(
                    $description
                ),
                TextareaField::create(
                    'Description',
                    _t(
                        __CLASS__ . 'DESCRIPTION',
                        'Description'
                    )
                ),
                UploadField::create(
                    'Image',
                    _t(
                        __CLASS__ . '.SLIDE_IMAGE',
                        'Image'
                    )
                )->setFolderName($this->getFolderName() . '/' . $this->ID)
                ->setAllowedExtensions($this->getAllowedFileTypes())
                ->setDescription(
                    _t(
                        __CLASS__ . 'ALLOWED_FILE_TYPES',
                        'Allowed file types: {types}',
                        [
                            'types' => implode(",", $this->getAllowedFileTypes())
                        ]
                    )
                ),
                HTMLEditorField::create(
                    'Transcript',
                    _t(
                        __CLASS__ . '.TRANSCRIPT',
                        'Transcript of video'
                    )
                ),
                InlineLinkCompositeField::create(
                    'LinkTarget',
                    _t(
                        __CLASS__ . 'LINKTARGET',
                        'Link'
                    ),
                    $this
                )
            ]
        );

        $fields->insertAfter(
            'Image',
            ReadonlyField::create(
                'VideoThumbnail',
                _t(
                    __CLASS__ . 'VIDEO_THUMBNAIL',
                    'Video thumbnail'
                ),
            )->setDescription(
                _t(
                    __CLASS__ . 'VIDEO_THUMBNAIL_DESCRIPTION',
                    'The automatically discovered video thumbnail, if found. Copy and paste the URL into a browser to view it.'
                ),
            )
        );

        if($imageField = $fields->dataFieldByName('Image')) {
            $imageField->setTitle(
                _t(
                    __CLASS__ . 'IMAGE_SPECIFIC_THUMBNAIL',
                    'Upload an image to use as the thumbnail'
                )
            );
        }

        $fields->insertAfter(
            'VideoThumbnail',
            DropdownField::create(
                'UseVideoThumbnail',
                _t(
                    __CLASS__ . 'VIDEO_THUMBNAIL_TO_USE',
                    'Select which thumbnail to use, if it exists'
                ),
                [
                    0 => _t(__CLASS__ . 'IMAGE_UPLOADED', 'Image uploaded'),
                    1 => _t(__CLASS__ . 'VIDEO_THUMBNAIL_FOUND', 'Video thumbnail found')
                ]
            )
        );

        return $fields;
    }

    /**
     * Return default video height
     * @return int
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
    public function forTemplate()
    {
        $this->addEmbedRequirements();
        return $this->renderWith([$this->class, __CLASS__]);
    }

    /**
     * Return allow="" value for <iframe>
     */
    public function AllowAttribute(): string
    {
        $value = $this->config()->get('allow_attribute');
        return is_string($value) ? $value : '';
    }
}
