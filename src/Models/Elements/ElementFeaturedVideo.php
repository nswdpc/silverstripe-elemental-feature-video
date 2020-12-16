<?php
namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\ElementContent;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;

/**
 * ElementFeaturedVideo adds a featured video
 */
class ElementFeaturedVideo extends ElementContent {

    private static $inline_editable = false;

    private static $icon = 'font-icon-block-banner';

    private static $table_name = 'ElementFeaturedVideo';

    private static $title = 'Featured video';
    private static $description = "Display a featured video";

    private static $singular_name = 'FeaturedVideo';
    private static $plural_name = 'FeaturedVideos';

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Featured video');
    }

    private static $db = [
        'Video' => 'Varchar(255)',
        'Provider' => 'Varchar',
        'Width' => 'Int',
        'Height' => 'Int',
        'Transcript' => 'HTMLText',

    ];

    private static $has_one = [
        'Image' => Image::class,
        'FeatureLink' => Link::class
    ];

    private static $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Title' => 'Title',
    ];

    private static $owns = [
        'Image'
    ];

    public function getThumbWidth() {
        $width = $this->Width;
        if($width <= 0) {
            $width = $this->config()->get('default_thumb_width');
        }
        return $width;
    }

    public function getThumbHeight() {
        $height = $this->Height;
        if($height <= 0) {
            $height = $this->config()->get('default_thumb_height');
        }
        return $height;
    }

    public function getAllowedFileTypes() {
        $types = $this->config()->get('allowed_file_types');
        if(empty($types)) {
            $types = ["jpg","jpeg","gif","png","webp"];
        }
        $types = array_unique($types);
        return $types;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->Width = $this->getThumbWidth();
        $this->Height = $this->getThumbHeight();
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function($fields)
        {
                $fields->removeByName(['FeatureLinkID']);

                $fields->addFieldsToTab(
                    'Root.Main', [
                        OptionsetField::create(
                            'Provider',
                            _t(__CLASS__ . '.PROVIDER', 'Video provider'),
                            [
                                'youtube' => 'YouTube',
                                'vimeo' => 'Vimeo'
                            ]
                        ),
                        TextField::create(
                            'Video',
                            _t(
                                __CLASS__ . 'VideoID', 'Video ID'
                            )
                        ),
                        LinkField::create(
                            'FeatureLink',
                            _t(
                                __CLASS__ . 'LINK', 'Link'
                            ),
                            $this->owner
                        ),
                        NumericField::create(
                            'Width',
                            _t(
                                __CLASS__ . 'WIDTH', 'Thumbnail width'
                            )
                        ),
                        NumericField::create(
                            'Height',
                            _t(
                                __CLASS__ . 'WIDTH', 'Thumbnail height'
                            )
                        ),
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
                        ),
                        HTMLEditorField::create(
                            'Transcript',
                            _t(
                                __CLASS__ . '.TRANSCRIPT',
                                'Transcript of video'
                            )
                        )
                    ]
                );

            });
        return parent::getCMSFields();
    }

}
