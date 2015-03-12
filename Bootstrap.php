<?php
/**
 * author Grzegorz Pierzakowski
 * @link http://helgusoft.pl/
 * @copyright Copyright (c) 2015 helgusoft, Gdańsk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace grzegorzpierzakowski\hii;

use yii\base\Application;
use yii\base\BootstrapInterface;


class Bootstrap implements BootstrapInterface
{

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['hii-model'])) {
                $app->getModule('gii')->generators['hii-model'] = 'grzegorzpierzakowski\hii\model\Generator';
            }
        }
    }
}