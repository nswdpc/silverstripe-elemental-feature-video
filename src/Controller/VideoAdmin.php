<?php

namespace NSWDPC\Elemental\Models\FeaturedVideo;

use SilverStripe\Admin\ModelAdmin;

/**
 * Manage gallery videos
 * @author james.ellis@dpc.nsw.gov.au
 */
class VideoAdmin extends ModelAdmin
{
    private static string $url_segment = 'gallery-video';

    private static string $menu_title = 'Videos';

    private static string $menu_icon_class = 'font-icon-block-media';

    private static array $managed_models = [
        GalleryVideo::class
    ];

    /**
     * @inheritdoc
     */
    public function getList()
    {
        $list = parent::getList();
        return $list->sort(['Title' => 'ASC']);
    }

}
