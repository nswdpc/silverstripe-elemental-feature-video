<?php
namespace NSWDPC\Elemental\Models\FeaturedVideo;

use NSWDPC\Elemental\Models\FeaturedVideo\GalleryVideo;
use DNADesign\Elemental\Models\ElementContent;
use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * ElementVideoGallery adds a content slider via a sortable upload field
 */
class ElementVideoGallery extends ElementContent {

    private static $icon = 'font-icon-block-banner';

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

    private static $db = [
    ];

    private static $has_one = [
    ];

    private static $has_many = [
        'Videos' => GalleryVideo::class,
    ];

    private static $owns = [
        'Videos'
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function($fields)
        {
                $fields->removeByName(['Videos']);

                if ($this->isInDB()) {
                    $fields->addFieldToTab(
                        'Root.Main',
                        GridField::create(
                            'Videos',
                            _t(
                                __CLASS__ . 'SLIDES', 'Videos'
                            ),
                            $this->Videos(), $config = GridFieldConfig_RecordEditor::create()
                        )
                    );
                    $config->addComponent(GridFieldOrderableRows::create());
                }

            });
        return parent::getCMSFields();
    }


    public function SortedVideos() {
        return $this->Videos()->Sort('Sort');
    }

}
