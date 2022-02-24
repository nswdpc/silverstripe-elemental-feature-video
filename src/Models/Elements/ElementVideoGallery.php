<?php
namespace NSWDPC\Elemental\Models\FeaturedVideo;

use NSWDPC\Elemental\Models\FeaturedVideo\GalleryVideo;
use DNADesign\Elemental\Models\ElementContent;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * ElementVideoGallery adds a content slider via a sortable upload field
 */
class ElementVideoGallery extends ElementContent {

    private static $icon = 'font-icon-thumbnails';

    private static $inline_editable = false;

    private static $table_name = 'ElementVideoGallery';

    private static $title = 'Video gallery';
    private static $description = "Display one or more videos";

    private static $singular_name = 'Video gallery';
    private static $plural_name = 'Video galleries';

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Video gallery');
    }

    /**
     * @var array
     */
    private static $db = [
        'GalleryStyle' => 'Varchar',
        'VideoHeight' => 'Int'
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Videos' => GalleryVideo::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Videos'
    ];

    /**
     * Save a default height based on the Gallery Video constant
     */
    public static function getDefaultHeight() : int {
        return GalleryVideo::DEFAULT_HEIGHT;
    }

    /**
     * Return a title for a dropdown to assist in identifying this gallery
     * @return string
     */
    public function DropdownTitle() : string {
        $title = $this->Title;
        if(!$title) {
            $title = $this->getType();
        }
        $page = $this->getPage();
        $suffix = "";
        if($page && $page->exists()) {
            $suffix = " - ";
            $suffix .= _t(
                 __CLASS__ . ".ON_PAGE_TITLE",
                 "on {pageType} '{pageTitle}'",
                 [
                     'pageType' => strtolower($page->i18n_singular_name()),
                     'pageTitle' => $page->Title
                 ]
            );
        }
        return "{$title} (#{$this->ID}){$suffix}";
    }

    public function getCMSFields()
    {

        $fields = parent::getCmsFields();

        $fields->addFieldToTab(
            'Root.Videos',
            GridField::create(
                'Videos',
                _t(
                    __CLASS__ . '.VIDEOS', 'Videos'
                ),
                $this->Videos(),
                $config = GridFieldConfig_RelationEditor::create()
            )
        );
        $config->addComponent(GridFieldOrderableRows::create());

        $heightField = NumericField::create(
            'VideoHeight',
            _t(__CLASS__ . ".VIDEO_HEIGHT", "Apply a height to all videos in this gallery (pixels)")
        );
        if(!$this->exists()) {
            $heightField = $heightField->setValue( self::getDefaultHeight() );
        }
        $fields->addFieldsToTab(
            'Root.Settings', [
                $heightField,
                DropdownField::create(
                    "GalleryStyle",
                    _t(__CLASS__ . ".STYLE", "Gallery style"),
                    [
                        "default" => "Default style",
                        "card" => "Cards with links",
                    ]
                )
            ]
        );
        return $fields;
    }


    public function SortedVideos() {
        return $this->Videos()->Sort('Sort');
    }

}
