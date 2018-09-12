<?php
/**
 * Created by PhpStorm.
 * User: nightracer
 * Date: 03.06.2018
 * Time: 18:41
 */
return [

    [
        'route' => '/generate-yml-file',
        'target' => [\Modules\GenerateYml\Controllers\GenerateController::class, 'index'],
        'name' => 'generate_yml'
    ],
];