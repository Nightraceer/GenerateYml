<?php
/**
 * Created by PhpStorm.
 * User: nightracer
 * Date: 03.06.2018
 * Time: 20:17
 */

namespace Modules\GenerateYml\Components;


use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

class Yml extends BaseYml
{
    public function getCategories()
    {
        $data = [];

        $categories = Category::objects()->all();

        /** @var Category $category */
        foreach ($categories as $category) {
            $data[$category->id] = [
                'node' => 'category',
                'value' => $category->name,
                'attributes' => [
                    'id' => $category->id,
                ]
            ];

            if ($category->parent_id) {
                $data[$category->id]['attributes']['parentId'] = $category->parent_id;
            }

        }
        return $data;
    }

    public function getOffers()
    {
        $data = [];

        $products = Product::objects()->exclude(['avail' => Product::AVAIL_UNAVAILABLE])->all();

        /** @var Product $product */
        foreach ($products as $product) {
            $meta = $product->getMeta();
            $category = $meta->category;

            $data[$product->id] = [
                'node' => 'offer',
                'attributes' => [
                    'id' => $product->id,
                    'available' => $product->avail == Product::AVAIL_AVAILABLE ? 'true' : 'false'
                ],
                'url' => $this->hostInfo . $product->getAbsoluteUrl(),
                'price' => $product->price,
                'oldprice' => $product->old_price,
                'currencyId' => $this->currencies['ruble']['id'],
                'picture' => $this->hostInfo . $product->image->url_view,
                'description' => $meta->description,
                'sales_notes' => 'Наличные, банковский перевод'
            ];

            $vendor = $product->vendor ? $product->vendor : $this->vendor;

            $data[$product->id]['vendor'] = $vendor;

            if ($product->model) {
                $data[$product->id]['model'] = $product->model;

                if (mb_substr_count($product->name, $product->model)) {
                    $data[$product->id]['name'] = $product->name . ' ' . $vendor;
                } else {
                    $data[$product->id]['name'] = $product->name . ' ' . $vendor . ' ' . $product->model;
                }
            }

            if ($category) {
                $data[$product->id]['categoryId'] = $category->id;
            }

            foreach ($meta->characters->all() as $character) {
                $data[$product->id][] = [
                    'node' => 'param',
                    'attributes' => [
                        'name' => $character->name,
                    ],
                    'value' => $character->value
                ];
            }

        }

        return $data;
    }
}