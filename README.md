Yii2 Tilda Api
==============
Tilda platform api extension for Yii2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require globus/yii2-tilda-api:dev-master
```

or add

```
"globus/yii2-tilda-api": "*"
```
to the require section of your `composer.json` file.


Apply migrations

```
php yii migrate --migrationPath=@vendor/globus/yii2-tilda-api/migrations
```

Usage
-----

Once the extension is installed, include this in common/config/main-local.php file:

```php
    'components' => [
         ...
         'tilda' => [
             'class' => 'daccess1\tilda\TildaApi',
             'publicKey' => '**********',
             'secretKey' => '**********',
             // Change URL if needed
             'assetsUrl' => 'https://'.$_SERVER['HTTP_HOST'].'.xsph.ru/tilda',
             // Change 'public_html' to the frontend web directory if needed
             // (default Yii2 directory is 'frontend/web')
            'assetsPath' => dirname(dirname(__DIR__)).'/public_html/tilda',
             //Optional settings
             //'defaultProjectID' => '**********'
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
-------
```php
Yii::$app->tilda->getPage($pageID)
```
Saves tilda page to local database

getPages
--------
```php
Yii::$app->tilda->getPages($projectID)
```
Saves all pages from selected project to the local database. If no $projectID  is provided the defaultProjectID setting is used instead.

listPages
--------
```php
Yii::$app->tilda->getPages($projectID)
```
Returns list of pages in project as array ['id' => 'title']. If no $projectID is provided the defaultProjectID setting is used instead.