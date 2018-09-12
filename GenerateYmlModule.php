<?php
/**
 * Created by PhpStorm.
 * User: nightracer
 * Date: 03.06.2018
 * Time: 18:40
 */

namespace Modules\GenerateYml;


use Modules\GenerateYml\Models\YmlSettings;
use Phact\Main\Phact;
use Phact\Module\Module;

class GenerateYmlModule extends Module
{
    public static function getSettingsModel()
    {
        return new YmlSettings();
    }

    public static function onApplicationRun()
    {
        Phact::app()->event->on("GENERATE_YML", function ($sender) {
            Phact::app()->yml->generate();
        });
    }
}