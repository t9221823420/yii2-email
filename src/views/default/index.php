<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 30.05.2018
 * Time: 17:09
 */

// $path = $this->context->module->getParentPath( 'views' . DIRECTORY_SEPARATOR . $this->context->id ) . DIRECTORY_SEPARATOR . basename( __FILE__ );

include __DIR__ . '/_header.php';

/** @var \yii\web\View $this */
print $this->render( $parentViewPath . '/index', $_params_ );