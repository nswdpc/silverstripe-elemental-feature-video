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
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use gorriecoe\Link\Models\Link;
use NSWDPC\InlineLinker\InlineLinkCompositeField;
use NSWDPC\Elemental\Models\FeaturedVideo\ElementVideoGallery;

/**
 * Images in an ElementVideo
 */
class GalleryVideo extends DataObject {

    private static $table_name = 'GalleryVideo';

    private static $versioned_gridfield_extensions = true;

    private static $singular_name = 'Video';
    private static $plural_name = 'Videos';

    private static $default_sort = 'Sort';

    private static $allowed_file_types = ["jpg","jpeg","gif","png","webp"];

    private static $folder_name = 'videos';

    const PROVIDER_VIMEO = 'vimeo';
    const PROVIDER_YOUTUBE = 'youtube';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Video' => 'Varchar(255)',
        'Provider' => 'Varchar',
        'Description' => 'Text',
        'Sort' => 'Int',
        'Transcript' => 'HTMLText',
    ];

    private static $has_one = [
        'Image' => Image::class,
        'Parent' => ElementVideoGallery::class,
        'LinkTarget' => Link::class
    ];

    private static $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Parent.DropdownTitle' => 'Gallery',
        'Title' => 'Title',
        'Video' => 'Video Id',
        'Provider' => 'Provider'
    ];

    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'Video' => 'PartialMatchFilter',
        'Provider' => 'PartialMatchFilter',
        'Description' => 'PartialMatchFilter'
    ];

    private static $owns = [
        'Image'
    ];

    private static $extensions = [
        Versioned::class
    ];

    public function getAllowedFileTypes() {
        $types = $this->config()->get('allowed_file_types');
        if(empty($types)) {
            $types = ["jpg","jpeg","gif","png","webp"];
        }
        $types = array_unique($types);
        return $types;
    }

    public function getVideoProviders() {
        $list = [
            self::PROVIDER_VIMEO => _t(__CLASS__ . '.PROVIDER_VIMEO', 'Vimeo'),
            self::PROVIDER_YOUTUBE => _t(__CLASS__ . '.PROVIDER_YOUTUBE', 'YouTube'),
        ];
        $this->extend('updateVideoProviders', $list);
        return $list;
    }

    public function getFolderName() {
        $folder_name = $this->config()->get('folder_name');
        if(!$folder_name) {
            $folder_name = "videos";
        }
        return $folder_name;
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName(['LinkTargetID', 'Sort']);

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
                        __CLASS__ . 'VIDEO', 'Video ID'
                    )
                )->setDescription("Use the video ID only, e.g.; https://www.youtube.com/watch?v=<strong>oJL-lCzEXgI</strong>"),
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

    public function getMultiRecordEditingTitle() {
        return $this->singular_name();
    }

    public function forTemplate() {
        return $this->renderWith([$this->class, __CLASS__]);
    }
}
