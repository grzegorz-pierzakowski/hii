hii
===========

Extended models for Gii, the code generator of Yii2 Framework


What is it?
-----------

Hii provides automatic model generation for complex db models. If there is more than one relation between two models than hii will help you to cope with it.
The base table column name will be used to prepare the relation name. You may custom that by adding a customMap into the config.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

    composer.phar require grzegorz-pierzakowski/hii:"*"

    or you can add this into the composer.json

The generators are registered automatically in the application bootstrap process, if the Gii module is enabled

Usage
-----

Visit your application's Gii (eg. `index.php?r=gii` and choose one of the generators from the main menu screen.

For basic usage instructions see the [Yii2 Guide section for Gii](http://www.yiiframework.com/doc-2.0/guide-tool-gii.html).

Features
--------

### Model generator

- generates separate model classes to customize and base models classes to regenerate
- table prefixes can be stipped off model class names (not bound to db connection setting)

Use custom generators models
-----------------------------------------------

```
$config['modules']['gii'] = [
    'class'      => 'yii\gii\Module',
    'allowedIPs' => ['127.0.0.1'],
    'generators' => [
        'hii-model' => [
            'class'     => 'grzegorzpierzakowski\hii\model\Generator',
            //setting for out templates
            'templates' => [
                'mymodel' =>
                    '@app/giiTemplates/model/default',
            ],
            'customMap' => [
            ]
        ]
    ],
];
```

