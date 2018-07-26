<?php

namespace yozh\email\controllers;

use Yii;
use yozh\email\models\EmailAccount;
use yozh\form\ActiveField;
use yozh\crud\controllers\DefaultController as Controller;

class DefaultController extends Controller
{
	public static function defaultModelClass()
	{
		return EmailAccount::class;
	}
	
}
