Yii2 Tilda Api
==============
Tilda platform api extension for Yii2

This is a fork of [nariman924/yii2-tilda-api](https://github.com/nariman924/yii2-tilda-api) project, which seems to be abandoned for a couple of years.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require daccess1/yii2-tilda-api:dev-master
```

or add

```
"daccess1/yii2-tilda-api": "dev-master"
```
to the require section of your `composer.json` file.


Apply migrations

```
php yii migrate --migrationPath=@vendor/daccess1/yii2-tilda-api/migrations
```

Usage
-----

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
After this, simply use it in your code like this:

```php
Yii::$app->tilda->getPage($pageID)
```

Methods
=======

getPage
---
```php
Yii::$app->tilda->getPage($pageID)
```
Saves tilda page to local database

getPages
---
```php
Yii::$app->tilda->getPages($projectID)
```
Saves all pages from selected project to the local database. If no `$projectID`  is provided the `defaultProjectID` setting is used instead.

listPages
---
```php
Yii::$app->tilda->getPages($projectID)
```
Returns list of pages in project as array of `['id' => 'title']` arrays. If no `$projectID` is provided the `defaultProjectID` setting is used instead.

License
=======
This software is licensed under MIT license. For more information see [LICENSE](https://github.com/daccess1/yii2-tilda-api/blob/master/LICENSE).
