<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Admin\ModelAdmin;

/**
 * Manage gallery videos
 * @author james.ellis@dpc.nsw.gov.au
 */
class VideoAdmin extends ModelAdmin
{
    /**
     * @var string
     */
    private static $url_segment = 'gallery-video';

    /**
     * @var string
     */
    private static $menu_title = 'Videos';

    /**
     * @var string
     */
    private static $menu_icon_class = 'font-icon-block-media';

    /**
     * @var array
     */
    private static $managed_models = [
        GalleryVideo::class
    ];

    /**
     * @return DataList
     */
    public function getList()
    {
        $list = parent::getList();
        $list = $list->sort(['Title' => 'ASC']);
        return $list;
    }

}
