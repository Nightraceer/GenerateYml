<?php
/**
 * Created by PhpStorm.
 * User: nightracer
 * Date: 03.06.2018
 * Time: 19:00
 */

namespace Modules\GenerateYml\Commands;


use Modules\GenerateYml\Components\BaseYml;
use Modules\GenerateYml\GenerateYmlModule;
use Phact\Commands\Command;
use Phact\Main\Phact;

class GenerateCommand extends Command
{
    public function handle($arguments = [])
    {
        Phact::app()->yml->generate();
    }
}