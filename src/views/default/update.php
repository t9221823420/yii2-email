<?php

use yii\helpers\Html;

include __DIR__ . '/_header.php';

include( Yii::getAlias($parentViewPath . DIRECTORY_SEPARATOR  . basename( __FILE__ ) ) );