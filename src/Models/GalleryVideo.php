<?php
namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Control\Controller;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use gorriecoe\Link\Models\Link;
use NSWDPC\InlineLinker\InlineLinkCompositeField;
use NSWDPC\Elemental\Models\FeaturedVideo\ElementVideoGallery;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\ValidationException;

/**
 * Images in an ElementVideo
 */
class GalleryVideo extends DataObject {

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
    const PROVIDER_VIMEO = 'vimeo';

    /**
     * @var string
     */
    const PROVIDER_YOUTUBE = 'youtube';

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
    const DEFAULT_HEIGHT = 360;

    /**
     * @var int
     */
    protected $videoHeight = 0;

    /**
     * Allowed file types for the video image
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
     * Get available video providers
     */
    public function getVideoProviders() : array {
        $providers = VideoProvider::getProviderSelections();
        // @deprecated updateVideoProviders - this will be removed in v1
        $this->extend('updateVideoProviders', $providers);
        return $providers;
    }

    /**
     * Folder name for uploaded thumbnails
     */
    public function getFolderName() : string {
        $folder_name = $this->config()->get('folder_name');
        if(!$folder_name) {
            $folder_name = "videos";
        }
        return $folder_name;
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if(preg_match("/^http(s)?:\/\//", $this->Video)) {
            throw new ValidationException(
                _t(
                    __CLASS__ . ".VIDEO_ID_NOT_URL",
                    "Please use the video id from the embed URL, not the URL itself"
                )
            );
        }
    }

    /**
     * CMS fields for video management
     *
     */
    public function getCMSFields() {
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
                        ->map("ID","DropdownTitle")
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
            'Root.Main', [
                OptionsetField::create(
                    'Provider',
                    _t(__CLASS__ . '.PROVIDER', 'Choose a video source'),
                    $this->getVideoProviders()
                ),
                TextField::create(
                    'Video',
                    _t(
                        __CLASS__ . 'VIDEO_ID_VALUE', 'Enter the video identifier/code. The embed URL for the video will be automatically created based on this value.'
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
                        __CLASS__ . 'DESCRIPTION', 'Description'
                    )
                ),
                UploadField::create(
                    'Image',
                    _t(
                        __CLASS__ . '.SLIDE_IMAGE',
                        'Image'
                    )
                )->setFolderName( $this->getFolderName() . '/' . $this->ID)
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
                        __CLASS__ . 'LINKTARGET', 'Link'
                    ),
                    $this
                )
            ]
        );

        return $fields;
    }

    /**
     * Title for symbiote/silverstripe-multirecordfield
     * @return string
     */
    public function getMultiRecordEditingTitle() {
        return $this->singular_name();
    }

    /**
     * Allow some control of the video height eg. from a gallery parent
     */
    public function setVideoHeight(int $height) : self {
        $this->videoHeight = $height;
        return $this;
    }

    /**
     * Get specified height. Some providers allow a height to be set via URL arg
     */
    public function getVideoHeight() :int {
        $parent = $this->Parent();
        if($parent && $parent->VideoHeight > 0) {
            return $parent->VideoHeight;
        } elseif($this->videoHeight > 0) {
            return $this->videoHeight;
        } else {
            return self::DEFAULT_HEIGHT;
        }
    }

    /**
     *
     */
    protected function applyRequirements() {
        $height = $this->getVideoHeight();
        Requirements::customCSS(
<<<CSS
.embed.video {
    position: relative;
    overflow: hidden;
    padding-top: 56.25%;/* 16:9 */
    height : {$height}px;
}

.embed.video > iframe {
    border: 0;
    height: 100% !important;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}
CSS,
            'galleryVideoEmbedCSS'
        );
    }

    /**
     * Return the URL to embed the video in an <iframe>
     */
    public function EmbedURL() : string {
        $this->applyRequirements();
        $provider = VideoProvider::getProvider( $this->Provider );
        if($provider) {
            return $provider->getEmbedURL( $this->Video, [], $this->getVideoHeight() );
        } else {
            return "";
        }
    }

    /**
     * Return allow="" value for <iframe>
     */
    public function AllowAttribute() : string {
        return $this->config()->get('allow_attribute');
    }

    /**
     * Render this record into a template
     */
    public function forTemplate() {
        return $this->renderWith([$this->class, __CLASS__]);
    }
}
