Yii2 Tilda Api
==============

Читать на другом языке: [English](https://github.com/daccess1/yii2-tilda-api/blob/master/README.md)

Расширение для подключения API Tilda в Yii2

Это форк проекта [nariman924/yii2-tilda-api](https://github.com/nariman924/yii2-tilda-api), который, похоже, был брошен несколько лет назад.

Установка
=========

Предпочтительный способ установки данного расширения - с использованием [composer](http://getcomposer.org/download/).

Выполните команду

```
composer require daccess1/yii2-tilda-api
```

или добавьте

```
"daccess1/yii2-tilda-api": "^1.0"
```
в секцию "require" вашего файла `composer.json`.


После установки расширения, добавьте следующий код в ваш файл `common/config/main-local.php`:

```php
    'components' => [
         ...
         'tilda' => [
             'class' => 'daccess1\tilda\TildaApi',
             'publicKey' => '**********',
             'secretKey' => '**********',
             // В случае необходимости замените URL. Не забудьте
             // сменить протокол на https:// при переносе в продакшн.
             'assetsUrl' => 'http://'.$_SERVER['HTTP_HOST'].'/tilda',
             // В случае необходимости Измените 'public_html' на
             // директорию web вашего frontend приложения
             // (по умолчанию в Yii2 это 'frontend/web')
            'assetsPath' => dirname(dirname(__DIR__)).'/public_html/tilda',
             //Опциональный ID проекта по умлочанию (integer)
             //'defaultProjectID' => *****
         ],
     ],
```

Примените необходимые миграции

```
php yii migrate --migrationPath=@vendor/daccess1/yii2-tilda-api/migrations
```

Использование
=============
Две наиболее частые задачи, которые призвано решить данное расширение - сохранение локальных копий страниц Tilda и показ их конечному пользователю. Данная инструкция показывает примеры использования данного расширения совместно с CRUD, сгенерированным с помшью Gii.

Сохранение страниц
------------------
Для того, чтобы показать список страниц из проекта Tilda, вы можете использовать виджет `TildaPageSelect`. Он был создан специально для использования совместно с ActiveForm, и по умолчанию не требует дполнительных настроек. Просто замените поле, хранящее в себе ID страницы Tilda на этот код:
```php
<?= Yii::$app->tilda->renderPageSelect($model,'your_field_id'); ?>
```
Данный виджет принмает те же параметры, что и стандартное поле ActiveForm. Вы также можете задать ID проекта (целое число) третьим параметром, страницы которого будут выведены в список. Если вы этого не сделаете, будет использован ID, заданный в настройке `defaultProjectID`. В результате в вашу форму будет выведен HTML-тег select, а выбранное в нем значение в дальнейшем можно обрабатывать как любое другое в форме.

После выбора страницы, необходимо поизвести сохранение данных на локальный сервер. Для этого отредактируйте методы `actionCreate` и `actionUpdate` вашего контроллера, как показано ниже:
```php
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    // Добавьте эту строку
    Yii::$app->tilda->getPage($model->your_field_id);
    
    return $this->redirect(['view', 'id' => $model->id]);
}
```
Вызов данного метода сохранит выбранную страницу и ее ресурсы на локальный сервер.

Вывод страниц
-------------

После того, как ваша страница была сохранена на локальном сервере, вы можете показать ее пользователю с помощью метода `loadPage`. Для этого потребуется выполнить несколько шагов:

В нужном методе экшена в вашем контроллере загрузите данные страницы, и передайте их в представление
```php
$page = Yii::$app->tilda->loadPage(/*ID страницы в Tilda*/);
...
return $this->render(/*ваше_представление*/, [
    ...
    'page' => $page
]);
```

После этого зарегистрируте ресурсы страницы в вашем представлении:
```php
foreach ($page['styles'] as $style) {
    $this->registerCssFile($style);
}
foreach ($page['scripts'] as $script) {
    $this->registerJsFile($script,['depends' => [yii\web\JqueryAsset::className()]]);
}
```

Теперь вы можете вывести HTML-код страницы:
```php
<?= $page['html'] ?>
```

Webhook
=======
Tilda имеет возможность уведомлять ваше приложение об обновлениях с помошью Webhook. Следует принять во внимание, что сервис Webhook имеет определенные ограничения по использованию (в частности, ограничение времени ответа. Подробности в [документации Tilda API](http://help-ru.tilda.ws/api)). С учетом данных ограничений, прием Webhook можно организовать таким способом:

```php
public function actionWebhook()
    {
        $get = \Yii::$app->request->get();

        // Проврека publicKey
        if (!\Yii::$app->tilda->verifyPublicKey($get['publickey']))
            throw new \yii\web\ForbiddenHttpException("PublicKey dosen't match");
        
        if (!isset($get['save'])) {
            // Если обрабатывается запрос от Tilda
            // Замените 'baseUrl' на реальный адрес вашего Webhook
            $client = new Client([
                'transport' => 'yii\httpclient\CurlTransport',
                'baseUrl' => 'http://domain.com/index.php?r=tilda/webhook'
            ]);
            try {
                $client->createRequest()
                    ->setMethod('get')
                    ->setData(['save' => 1, 'page' => $get['pageid'], 'publickey' => $get['publickey']])
                    ->setOptions([
                        // Установка тайм-аутов и опций запроса
                        // для отмены ожидания скачивания старницы
                        CURLOPT_CONNECTTIMEOUT => 1,
                        CURLOPT_TIMEOUT => 1,
                        CURLOPT_RETURNTRANSFER => false,
                        CURLOPT_FORBID_REUSE => true,
                        CURLOPT_DNS_CACHE_TIMEOUT => 10,
                        CURLOPT_FRESH_CONNECT => true,
                    ])
                    ->send();
            } catch (\yii\httpclient\Exception $e) {
                // Возвращение успешного результата Tilda webhook
                \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
                return "ok";
            }
        } else {
            // Если это рекурсивный запрос от 
            // cURL, выполняем сохранение страницы
            \Yii::$app->tilda->getPage($get['page']);
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return "ok";
    }
```

Методы
=====
После установки расширения, вы можете использовать его методы в любом месте вашего приложения.

getPage
-------
Сохраняет страницу и ее ресурсы на локальном сервере. Примечение: не сохраняет на локаольный сервер библотеку jQuery из Tilda (1.10.2), для того чтобы избежать конфликтов с версией включенной в Yii2.
```php
Yii::$app->tilda->getPage($pageID)
```

listPages
---------
Возвращает массив массивов вида `['id' => 'title']`. Если `$projectID` не задан, используется параметр `defaultProjectID`.
```php
Yii::$app->tilda->listPages($projectID)
```

loadPage
---
Возвращает массив с данными страницы (HTML-код и ресурсы).
```php
$page = Yii::$app->tilda->loadPage($pageID);
```


Примечания
==========
Вы должны принять во внимание, что в настоящий момент CSS Tilda имеет глобальный конфликт с CSS Bootstrap (который поставляется с Yii2 по умолчанию). Для того, чтобы избежать ошибок, вы можете либо не использовать `bootstrap.css` и Tilda на одной странице, либо вручную исправить свойство `box-sizing` некорректно отображаемых элементов в вашей таблице стилей.


Лицензия 
========
Данное расширение распространяется под лицензией MIT. Подробнее здесь: [LICENSE](https://github.com/daccess1/yii2-tilda-api/blob/master/LICENSE).
