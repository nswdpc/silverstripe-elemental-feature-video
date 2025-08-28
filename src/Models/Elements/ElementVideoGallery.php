<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use DNADesign\Elemental\Models\ElementContent;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\NumericField;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * ElementVideoGallery adds a content slider via a sortable upload field
 * @property ?string $GalleryStyle
 * @property int $VideoHeight
 * @method \SilverStripe\ORM\HasManyList<\NSWDPC\Elemental\Models\FeaturedVideo\GalleryVideo> Videos()
 */
class ElementVideoGallery extends ElementContent
{
    private static string $icon = 'font-icon-thumbnails';

    private static bool $inline_editable = false;

    private static string $table_name = 'ElementVideoGallery';

    private static string $title = 'Video gallery';

    private static string $description = "Display one or more videos";

    private static string $singular_name = 'Video gallery';

    private static string $plural_name = 'Video galleries';

    #[\Override]
    public function getType()
    {
        return _t(self::class . '.BlockType', 'Video gallery');
    }

    private static array $db = [
        'GalleryStyle' => 'Varchar',
        'VideoHeight' => 'Int'
    ];

    private static array $has_many = [
        'Videos' => GalleryVideo::class,
    ];

    private static array $owns = [
        'Videos'
    ];

    /**
     * Save a default height based on the Gallery Video constant
     */
    public static function getDefaultHeight(): int
    {
        return GalleryVideo::DEFAULT_HEIGHT;
    }

    /**
     * Return a title for a dropdown to assist in identifying this gallery
     */
    public function DropdownTitle(): string
    {
        $title = $this->Title;
        if (!$title) {
            $title = $this->getType();
        }

        $page = $this->getPage();
        $suffix = "";
        if ($page && $page->exists()) {
            $suffix = " - ";
            $suffix .= _t(
                self::class . ".ON_PAGE_TITLE",
                "on {pageType} '{pageTitle}'",
                [
                    'pageType' => strtolower($page->i18n_singular_name()),
                    'pageTitle' => $page->Title
                ]
            );
        }

        return "{$title} (#{$this->ID}){$suffix}";
    }

    #[\Override]
    public function getCMSFields()
    {

        $fields = parent::getCmsFields();

        $fields->addFieldToTab(
            'Root.Videos',
            GridField::create(
                'Videos',
                _t(
                    self::class . '.VIDEOS',
                    'Videos'
                ),
                $this->Videos(),
                $config = GridFieldConfig_RelationEditor::create()
            )
        );
        $config->addComponent(GridFieldOrderableRows::create());

        $heightField = NumericField::create(
            'VideoHeight',
            _t(self::class . ".VIDEO_HEIGHT", "Apply a height to all videos in this gallery (pixels)")
        );
        if (!$this->exists()) {
            $heightField = $heightField->setValue(self::getDefaultHeight());
        }

        $fields->addFieldsToTab(
            'Root.Settings',
            [
                $heightField,
                DropdownField::create(
                    "GalleryStyle",
                    _t(self::class . ".STYLE", "Gallery style"),
                    [
                        "default" => "Default style",
                        "card" => "Cards with links",
                    ]
                )
            ]
        );
        return $fields;
    }


    public function SortedVideos()
    {
        return $this->Videos()->Sort('Sort');
    }

}
