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
After registering the component and applying required migrations, use it anywhere in your code like this:

getPage
-------
Saves tilda page to the local database
```php
Yii::$app->tilda->getPage($pageID)
```
The common use with Gii-generated CRUD could be like the following:

Update the `actionUpdate` function of your Controller (assuming that `tilda_page_id` is your model field containing integer page ID form Tilda. Change it to your actual model field or constant value.) like this
```php
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    //insert this line
    Yii::$app->tilda->getPage($model->tilda_page_id);
    
    return $this->redirect(['view', 'id' => $model->id]);
}
```

getPages
--------
Saves all pages from selected project to the local database. If no `$projectID`  is provided the `defaultProjectID` setting is used instead.
```php
Yii::$app->tilda->getPages($projectID)
```

listPages
---------
Returns list of pages in project as array of `['id' => 'title']` arrays. If no `$projectID` is provided the `defaultProjectID` setting is used instead.
```php
Yii::$app->tilda->getPages($projectID)
```

loadPage
---
Returns the array of data form the local copy of the page. The common use would be like this:

In your action load page data, then pass it to your view
```php
$page = Yii::$app->tilda->loadPage(/*YOUR PAGE ID*/);
...
return $this->render(/*YOUR VIEW*/, [
    ...
    'page' => $page
]);
```
Then in your view register page assets like this
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

Notes
=====
You should cousider that current Tilda core css settings conflict with Bootstrap (provided with Yii2 by default). To avoid conflicts, you could either don't use `bootstrap.css` and Tilda at the same page, or fix `box-sizing` property of elements in your own stylesheet.

License
=======
This software is licensed under MIT license. For more information see [LICENSE](https://github.com/daccess1/yii2-tilda-api/blob/master/LICENSE).
