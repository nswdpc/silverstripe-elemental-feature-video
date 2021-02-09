<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Admin\ModelAdmin;

/**
 * Manage gallery videos
 * @author james.ellis@dpc.nsw.gov.au
 */
class VideoAdmin extends ModelAdmin
{
    private static $url_segment = 'gallery-video';
    private static $menu_title = 'Video';
    private static $menu_icon_class = 'font-icon-block-media';
    private static $managed_models = [
        GalleryVideo::class
    ];

    public function getList()
    {
        $list = parent::getList();
        $list = $list->sort(['Title' => 'ASC']);
        return $list;
    }

}
