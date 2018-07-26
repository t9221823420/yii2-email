<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 30.05.2018
 * Time: 17:09
 */

include __DIR__ . '/_header.php';

/** @var \yii\web\View $this */
print $this->render( $parentViewPath . '/view', $_params_ );