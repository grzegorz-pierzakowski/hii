hii
===========

Extended models for Gii, the code generator of Yii2 Framework


What is it?
-----------

Hii provides automatic model generation for complex db models. 
It supports:
- many relations between two models
- 'name2other_name' db table names
- cascade model structure: 
```
models
|- base / model.php  <- this one has automaticly generated relations
|- model.php
```
- relation to self is possible only by setting it in 'customRelations'
- autogenerating static methods findBy{UniqieField}

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

    composer.phar require grzegorz-pierzakowski/hii:"*"

    or you can add this into the composer.json:

    "grzegorz-pierzakowski/hii": "1.0.0"

The Hii-model generator is registered automatically in the application bootstrap process, if the Gii module is enabled

Use custom options (It's in params untill Yii2 enables passing config to generators)
-----------------------------------------------

```
$config['params']['hii-model'] = [
            // put your custom pairs of 'table' => 'ModelName' map here
            'tableModelMap' => [
            ],
            // put your pairs of 'column_name' => 'RelationSuffix' map here
            // this will allow to generate more than one relation between 2 models
            'customRelations' => [
            ]
        ]
       
```
Usage
-----

Visit your application's Gii (eg. `index.php?r=gii` and choose Hii Model from the main menu screen.

For basic usage instructions see the [Yii2 Guide section for Gii](http://www.yiiframework.com/doc-2.0/guide-tool-gii.html).

Let's assume you have a ggroup table represented by Group object and ggroup has user_id and user_last_id columns. You have two relations to User object then.
If you set the project as:
```
$config['params']['hii-model'] = [
    'customRelations' => [
        'last_user_id' => 'Last'
    ],
    'tableModelMap' => [
        'ggroup' => 'Group'
    ]
]

will generate 2 relations in User:


```
Magic will happen and your models will have relations as below:

```
Group User->myGroup
Group User->myGroupLast
User Group->lastUser
User Group->user
```

