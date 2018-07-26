<?php

namespace yozh\email;

class AssetBundle extends \yozh\base\AssetBundle
{

    public $sourcePath = __DIR__ .'/../assets/';

    public $css = [
        //'css/yozh-email.css',
	    //['css/yozh-email.print.css', 'media' => 'print'],
    ];
	
    public $js = [
        //'js/yozh-email.js'
    ];
	
    public $depends = [
        //'yii\bootstrap\BootstrapAsset',
    ];	
	
	public $publishOptions = [
		//'forceCopy'       => true,
	];
	
}