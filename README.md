hii
===========

still at development stage


Extended models for Gii, the code generator of Yii2 Framework


What is it?
-----------

Hii provides automatic model generation for complex db models. 
Supports:
- many relations between two models
- tbl2tbl tables names
- cascade model structure: 
        models
        |-base/ model.php
        |-model.php

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
            // put your custom pairs 'table' => 'Model' map here
            'tableModelMap' => [],
            // pairs of column => RelationName that will be generated when found 
            // if two tables have more than one relation
            'customRelations' => []
        ]
    ],
];
```

