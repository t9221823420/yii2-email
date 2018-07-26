<?php

namespace yozh\email;

use yozh\base\Module as BaseModule;

class Module extends BaseModule
{

	const MODULE_ID = 'email';
	
	public $controllerNamespace = 'yozh\\' . self::MODULE_ID . '\controllers';
	
}
