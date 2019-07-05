Yii2 Tilda Api
==============
Tilda platform api extension for Yii2

This is a fork of [nariman924/yii2-tilda-api](https://github.com/nariman924/yii2-tilda-api) project, which seems to be abandoned for a couple of years.

Installation
============

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require daccess1/yii2-tilda-api
```

or add

```
"daccess1/yii2-tilda-api": "^1.0"
```
to the require section of your `composer.json` file.


Once the extension is installed, include this in components section of your `common/config/main-local.php` file:

```php
    'components' => [
         ...
         'tilda' => [
             'class' => 'daccess1\tilda\TildaApi',
             'publicKey' => '**********',
             'secretKey' => '**********',
             // Change URL if needed. Don't forget to set protocol
             // to https:// when moving to production.
             'assetsUrl' => 'http://'.$_SERVER['HTTP_HOST'].'/tilda',
             // Change 'public_html' to the frontend web directory if needed
             // (default Yii2 directory is 'frontend/web')
            'assetsPath' => dirname(dirname(__DIR__)).'/public_html/tilda',
             //Optional default project ID (integer)
             //'defaultProjectID' => *****
         ],
     ],
```

Apply required migrations

```
php yii migrate --migrationPath=@vendor/daccess1/yii2-tilda-api/migrations
```

Usage
=====
The two most common goals of this extension are saving Tilda pages to lhe local database and then showing them to the end user. The following instruction shows some common usage patterns for this extension. All examples assume that you are using Gii-generated CRUD. 

Saving pages
------------
To render the list of the pages from Tilda project, you can use the `TildaPageSelect` widget. It's designed to be used with the ActiveForm generated with Gii, and requires no additional setup by default. Simply add
```php
use daccess1\tilda\TildaPageSelect;
```
to the "use" section of your form template, and then replace your form field that stores Tilda page id with this code:
```php
<?= TildaPageSelect::widget([
    'model' => $model,
    'field' => 'your_field_id'
    //Optional project ID
    //'project' => *******
]) ?>
```
This widget takes all the same values as default ActiveForm input field. You can also set the project ID for listing. If you don't, the `defaultProjectID` setting will be used. As the result, the HTML select with listed Tilda pages will be rendered into your form, and it's input will be treated as any other model field.

After selecting the page, you now want to save it to the local storage. In order to do so you should update `actionCreate` and `actionUpdate` functions of your Controller like this:
```php
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    //insert this line
    Yii::$app->tilda->getPage($model->your_field_id);
    
    return $this->redirect(['view', 'id' => $model->id]);
}
```
This will save page body HTML code and assets to your local server.

Rendering pages
---------------

After your page was saved to the local storage, you can render it with the `loadPage` method of the extension. This will require several steps to implement:

In your action load page data and pass it to your view:
```php
$page = Yii::$app->tilda->loadPage(/*YOUR PAGE ID*/);
...
return $this->render(/*YOUR VIEW*/, [
    ...
    'page' => $page
]);
```
Then in your view register Tilda page assets like this:
```php
foreach ($page['styles'] as $style) {
    $this->registerCssFile($style);
}
foreach ($page['scripts'] as $script) {
    $this->registerJsFile($script,['depends' => [yii\web\JqueryAsset::className()]]);
}
```

You can now render page html code like this
```php
<?= $page['html'] ?>
```

Webhook
=======
Tilda has an option to notify your app about any pages published in your projects via a webhook. However, it has some restrictions on it's usage (see more in [Tilda API docs](http://help-ru.tilda.ws/api)). One of the ways to implement Webhook would be like this:
```php
public function actionWebhook()
    {
        $get = \Yii::$app->request->get();

        // Check request public key
        if (!\Yii::$app->tilda->verifyPublicKey($get['publickey']))
            throw new \yii\web\ForbiddenHttpException("PublicKey dosen't match");
        
        if (!isset($get['save'])) {
            // If this is actual Tilda webhook request,
            // create cURL request client 
            // Set 'baseUrl' to your current webhook Url
            $client = new Client([
                'transport' => 'yii\httpclient\CurlTransport',
                'baseUrl' => 'http://domain.com/index.php?r=tilda/webhook'
            ]);
            try {
                $client->createRequest()
                    ->setMethod('get')
                    ->setData(['save' => 1, 'page' => $get['pageid'], 'publickey' => $get['publickey']])
                    ->setOptions([
                        // Setting timeout and data return transfer
                        // options of request to prevent waiting until
                        // page download completes
                        CURLOPT_CONNECTTIMEOUT => 1,
                        CURLOPT_TIMEOUT => 1,
                        CURLOPT_RETURNTRANSFER => false,
                        CURLOPT_FORBID_REUSE => true,
                        CURLOPT_DNS_CACHE_TIMEOUT => 10,
                        CURLOPT_FRESH_CONNECT => true,
                    ])
                    ->send();
            } catch (\yii\httpclient\Exception $e) {
                // Returning success result to Tilda webhook
                \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
                return "ok";
            }
        } else {
            // Else if this is cURL recursive request, perform
            // page download 
            \Yii::$app->tilda->getPage($get['page']);
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return "ok";
    }
```

Methods
=====
After registering the component and applying required migrations, you can access it's methods anywhere in your code. However, the most common usage case was already described above.

getPage
-------
Saves tilda page and it's assets to the local database. Note: Tilda's default jQuery (1.10.2) is not downloaded (to prevent conflicts with your Yii2 jQuery asset). 
```php
Yii::$app->tilda->getPage($pageID)
```

listPages
---------
Returns list of pages in project as array of `['id' => 'title']` arrays. If no `$projectID` is provided the `defaultProjectID` setting is used instead.
```php
Yii::$app->tilda->listPages($projectID)
```

loadPage
---
Returns the array of data form the local copy of the page.
```php
$page = Yii::$app->tilda->loadPage($pageID);
```


Notes
=====
You should consider that current Tilda core css settings conflict with Bootstrap (provided with Yii2 by default). To avoid conflicts, you could either don't use `bootstrap.css` and Tilda at the same page, or fix `box-sizing` property of elements in your own stylesheet.

License
=======
This software is licensed under MIT license. For more information see [LICENSE](https://github.com/daccess1/yii2-tilda-api/blob/master/LICENSE).
