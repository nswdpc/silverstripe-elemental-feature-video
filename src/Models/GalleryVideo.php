<?php
namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextField;

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

    private static $db = [
        'Title' => 'Varchar(255)',
        'Video' => 'Varchar',
        'Sort' => 'Int'
    ];

    private static $has_one = [
        'Image' => Image::class,
        'Parent' => ElementVideoGallery::class,
    ];

    private static $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Title' => 'Title',
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

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName(['LinkID', 'ParentID', 'Sort']);

        $fields->addFieldsToTab(
            'Root.Main', [
                TextField::create(
                    'Video',
                    _t(
                        __CLASS__ . 'VIDEO', 'Video'
                    )
                )->setDescription("Use the video ID only, e.g.; https://www.youtube.com/watch?v=<strong>oJL-lCzEXgI</strong>"),
                UploadField::create(
                    'Image',
                    _t(
                        __CLASS__ . '.SLIDE_IMAGE',
                        'Image'
                    )
                )->setFolderName('videos/' . $this->ID)
                ->setAllowedExtensions($this->getAllowedFileTypes())
                ->setDescription(
                    sprintf(_t(
                        __CLASS__ . 'ALLOWED_FILE_TYPES',
                        'Allowed file types: %s'
                    ), implode(",", $this->getAllowedFileTypes()))
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
