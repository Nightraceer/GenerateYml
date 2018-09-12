## Использование

Подключаем модуль:

```php
...
'GenerateYml'
...
```

Синхронизируем БД


Создаем Свой компонент, который нужно унаследовать от BaseYml, и реализуем абстрактные методы

```php
...
class Yml extends BaseYml
...
```

Подключаем созданный компонент

```php
...
'yml' => [
    'class' => '\Modules\GenerateYml\Components\Yml',
]
...
```

В админке обязательно заполняем в настройках модуля поля: "Домен сайта вместе с протоколом", "Полное наименование компании, владеющей магазином",
"Короткое название магазина, не более 20 символов"

В моделях, которые попадают в выгрузку, при необходимости, можно после сохранения обновлять файл выгрузки.
Генерацию yml-файла можно запустить, вызвав событие GENERATE_YML

```php
...
public function afterSave()
    {
        Phact::app()->event->trigger('GENERATE_YML');
        parent::afterSave();
    }
...
```