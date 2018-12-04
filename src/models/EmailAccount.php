<?php

namespace yozh\email\models;

use Yii;

use yozh\crud\models\BaseActiveRecord as ActiveRecord;

/**
 * This is the model class for table "email_account".
 *
 * @property int $id
 * @property string $title
 * @property string $email
 * @property string $username
 * @property string $password
 * @property int $require_authentication
 * @property string $in_server
 * @property int $in_port
 * @property string $in_encryption_type
 * @property string $out_server
 * @property int $out_port
 * @property string $out_encryption_type
 * @property int $use_incoming_credentials
 * @property int $enabled
 */
class EmailAccount extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%yozh_email_account}}';
	}
	
	/**
	 * @inheritdoc
	 * @return EmailAccountQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new EmailAccountQuery( get_called_class() );
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules( $rules = [], $update = false )
	{
		static $_rules;
		
		if( !$_rules || $update ) {
			
			$_rules = parent::rules( \yozh\base\components\validators\Validator::merge( [
				
				[ [ 'title', 'email', 'username', 'password', 'in_server', 'in_port' ], 'required' ],
				[ [ 'email', 'out_send_from', ], 'email' ],
				[ [ 'enabled', 'require_authentication', 'use_incoming_credentials', ], 'boolean' ],
				[ [ 'in_port', 'out_port', ], 'integer' ],
				[ [ 'in_encryption_type', 'out_encryption_type' ], 'string' ],
				[ [ 'title', 'username', 'password', 'in_server', 'out_server' ], 'string', 'max' => 255 ],
				[ [ 'out_username', 'out_password' ], 'string', 'max' => 255 ],
			
			], $rules ) );
			
		}
		
		return $_rules;
		
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels( ?array $only = null, ?array $except = null, ?bool $schemaOnly = false )
	{
		return [
			'id'                       => Yii::t( 'app', 'ID' ),
			'title'                    => Yii::t( 'app', 'Title' ),
			'email'                    => Yii::t( 'app', 'Email' ),
			'username'                 => Yii::t( 'app', 'Username' ),
			'password'                 => Yii::t( 'app', 'Password' ),
			'require_authentication'   => Yii::t( 'app', 'Require Authentication' ),
			'in_server'                => Yii::t( 'app', 'In Server' ),
			'in_port'                  => Yii::t( 'app', 'In Port' ),
			'in_encryption_type'       => Yii::t( 'app', 'In Encryption Type' ),
			'out_server'               => Yii::t( 'app', 'Out Server' ),
			'out_port'                 => Yii::t( 'app', 'Out Port' ),
			'out_encryption_type'      => Yii::t( 'app', 'Out Encryption Type' ),
			'use_incoming_credentials' => Yii::t( 'app', 'Use Incoming Credentials' ),
			'enabled'                  => Yii::t( 'app', 'Enabled' ),
		];
	}
	
	public function attributesIndexList( ?array $only = null, ?array $except = null, ?bool $schemaOnly = false )
	{
		return [
			'title',
		];
	}
	
	public function attributesViewList( ?array $only = null, ?array $except = null, ?bool $schemaOnly = false )
	{
		return [
			'title',
		];
	}
}
