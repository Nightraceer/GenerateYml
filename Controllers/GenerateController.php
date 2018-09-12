<?php
/**
 * Created by PhpStorm.
 * User: nightracer
 * Date: 03.06.2018
 * Time: 19:02
 */

namespace Modules\GenerateYml\Controllers;


use Phact\Controller\Controller;
use Phact\Main\Phact;

class GenerateController extends Controller
{
    public function index()
    {
        $generate = Phact::app()->yml->generate();

        if ($generate) {
            echo 'Yml успешно обновлен';
        } else {
            echo 'Yml не удалось обновить';
        }
    }
}