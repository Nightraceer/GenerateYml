<?php
/**
 * Created by PhpStorm.
 * User: nightracer
 * Date: 04.06.2018
 * Time: 21:07
 */

namespace Modules\GenerateYml\Models;


use Phact\Orm\Fields\CharField;
use Phact\Orm\Model;

class YmlSettings extends Model
{
    public static function getFields()
    {
        return [
            'host_info' => [
                'class' => CharField::class,
                'label' => 'Домен сайта вместе с протоколом',
            ],
            'vendor' => [
                'class' => CharField::class,
                'label' => 'Производитель (vendor)',
                'null' => true
            ],
            'company_name' => [
                'class' => CharField::class,
                'label' => 'Полное наименование компании, владеющей магазином',
            ],
            'shop_name' => [
                'class' => CharField::class,
                'label' => 'Короткое название магазина, не более 20 символов',
                'hint' => 'В названии нельзя использовать слова, не имеющие отношения к наименованию магазина, например «лучший», «дешевый», указывать номер телефона и т. п.'
            ],
            'file_name' => [
                'class' => CharField::class,
                'label' => 'Название файла',
                'null' => true,
                'hint' => 'Можно указать путь, например: export/products_yml.xml . Если папки export не существует, она  будет создана'
            ]
        ];
    }
}