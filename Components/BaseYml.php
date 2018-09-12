<?php
/**
 * Created by PhpStorm.
 * User: nightracer
 * Date: 03.06.2018
 * Time: 18:47
 */

namespace Modules\GenerateYml\Components;


use Phact\Helpers\Paths;
use Phact\Main\Phact;

/**
 * Class BaseYml
 * @package Modules\GenerateYml\Components
 *
 * Формирование xml-файла с товарами для Яндекс Маркета
 * @see https://yandex.ru/support/partnermarket/export/yml.html
 */
abstract class BaseYml
{
    public $fileName = 'products_yml.xml';

    public $hostInfo = '';

    public $vendor = '';

    private $shopName = '';

    private $companyName = '';

    /**
     * @var array
     * @see https://yandex.ru/support/partnermarket/currencies.html
     */
    public $currencies = [
        'ruble' => [
            'id' => 'RUR',
            'rate' => '1'
        ]
    ];

    public function __construct()
    {
        $settings = Phact::app()->settings;
        $names = [
            'host_info' => 'hostInfo',
            'vendor' => 'vendor',
            'company_name' => 'companyName',
            'shop_name' => 'shopName',
            'file_name' => 'fileName'
        ];


        foreach ($names as $field => $prop) {
            $value = $settings->get("GenerateYml.{$field}");

            if (!$value && in_array($field, ['host_info', 'company_name', 'shop_name'])) {
                throw new \Exception("Field {$field} in the YmlSettings model must be filled");
            }

            if ($value) {
                $this->{$prop} = $value;
            }
        }
    }

    public function generate()
    {
        $this->message("Формирование yml-файла началось\n", 'green', 'black');
        if ($this->checkExec()) {
            $this->message("Файл уже формируется\n", 'light_blue', 'black');
            return false;
        }

        $result = $this->process();

        $this->clearExecFile();

        if ($result) {
            $this->message("Формирование успешно завершено\n", 'green', 'black');
        } else {
            $this->message("При создании файла произошла ошибка\n", 'red', 'black');
        }

        return $result;
    }

    public function process()
    {
        $base = '<?xml version="1.0" encoding="utf-8"?>
                <yml_catalog date="' . date("Y-m-d H:i") . '">
                    <shop>
                    </shop>
                </yml_catalog>';

        $document = new \SimpleXMLElement($base);

        ini_set('memory_limit', '256M');

        $this->addTo($document->shop, $this->getBase());
        $this->addTo($document->shop, $this->getAdditionalInfo());
        $this->addTo($document->shop, $this->categories());
        $this->addTo($document->shop, $this->offers());

        $filePath = Paths::get('www') . DIRECTORY_SEPARATOR . $this->fileName;

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        return $document->asXML(Paths::get('www') . DIRECTORY_SEPARATOR . $this->fileName);
    }

    /**
     * @return array
     * @see https://yandex.ru/support/partnermarket/categories.html
     */
    abstract protected function getCategories();

    /**
     * @return array
     * @see https://yandex.ru/support/partnermarket/offers.html
     */
    abstract protected function getOffers();

    protected function getBase()
    {
        $data = [
            'name' => $this->shopName,
            'company' => $this->companyName,
            'url' => $this->hostInfo
        ];

        $currencies = [];

        foreach ($this->currencies as $currency) {
            $currencies[] = [
                'node' => 'currency',
                'attributes' => $currency
            ];
        }

        $data['currencies'] = $currencies;

        return $data;
    }

    protected function getAdditionalInfo()
    {
        return [];
    }

    protected function categories()
    {
        $data = [];
        $categories = $this->getCategories();

        if ($categories) {
            $data['categories'] = $categories;
        }

        return $data;
    }

    protected function offers()
    {
        $data = [];
        $offers = $this->getOffers();

        if ($offers) {
            $data['offers'] = $offers;
        }

        return $data;
    }

    protected function addTo(\SimpleXMLElement $node, array $data)
    {
        foreach ($data as $key => $value) {

            if (is_array($value)) {

                $nodeName = isset($value['node']) ? $value['node'] : $key;

                $xmlValue = isset($value['value']) ? $value['value'] : null;

                $newNode = $node->addChild($nodeName, $this->clear($xmlValue));

                if (isset($value['attributes'])) {

                    foreach ($value['attributes'] as $name => $valueAttr) {
                        $newNode->addAttribute($name, $this->clear($valueAttr));
                    }
                    unset($value['attributes']);
                }

                if (isset($value['node'])) {
                    unset($value['node']);
                }

                if (isset($value['value'])) {
                    unset($value['value']);
                }

                $this->addTo($newNode, $value);
            } else {
                if (is_string($value) || is_int($value) || is_double($value)) {

                    $node->addChild($key, $this->clear($value));
                }
            }
        }

        return $node;
    }

    protected function clear($value)
    {
        $cleared = strip_tags($value);
        $replaced = [
            '"' => '&quot;',
            '&' => '&amp;',
            '>' => '&gt;',
            '<' => '&lt;',
            "'" => '&apos;',
            '`' => '&apos;',
        ];
        return strtr($cleared, $replaced);
    }

    protected function clearExecFile()
    {
        $path = Paths::get('base.runtime');
        $file = $path . DIRECTORY_SEPARATOR . 'yml_generate_started';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    protected function checkExec()
    {
        $path = Paths::get('base.runtime');
        $file = $path . DIRECTORY_SEPARATOR . 'yml_generate_started';
        if (file_exists($file)) {
            return true;
        }
        if (!file_exists($file)) {
            file_put_contents($file, '');
        }
    }

    protected function message($string, $foreground_color, $background_color)
    {
        if (Phact::app()::getIsCliMode()) {
            $colored_string = "";
            $foreground = $this->getColor('foreground', $foreground_color);
            $background = $this->getColor('background', $background_color);

            if ($foreground) {
                $colored_string .= "\033[" . $foreground . "m";
            }
            if ($background) {
                $colored_string .= "\033[" . $background . "m";
            }

            $colored_string .= $string . "\033[0m";

            echo $colored_string;
        }

        return null;
    }

    protected function getColor($position, $color)
    {
        $colors = [
            'foreground' => [
                'black' => '0;30',
                'dark_gray' => '1;30',
                'blue' => '0;34',
                'light_blue' => '1;34',
                'green' => '0;32',
                'light_green' => '1;32',
                'cyan' => '0;36',
                'light_cyan' => '1;36',
                'red' => '0;31',
                'light_red' => '1;31',
                'purple' => '0;35',
                'light_purple' => '1;35',
                'brown' => '0;33',
                'yellow' => '1;33',
                'light_gray' => '0;37',
                'white' => '1;37'
            ],
            'background' => [
                'black' => '40',
                'red' => '41',
                'green' => '42',
                'yellow' => '43',
                'blue' => '44',
                'magenta' => '45',
                'cyan' => '46',
                'light_gray' => '47'
            ]
        ];

        return isset($colors[$position][$color]) ? $colors[$position][$color] : null;
    }
}