<?php

use yozh\base\components\db\Migration;

/**
 * Class m180301_142414_add_column_to_properties_table
 */
class m000000_000000_email_account_table_dev extends Migration
{
	protected static $_table = 'email_account';
	
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		
		$protocols = [ 'No', 'SSL', 'TLS' ];
		
		static::$_columns = [
			'id'                     => $this->primaryKey(),
			'title'                  => $this->string()->notNull(),
			'email'                  => $this->string()->notNull(),
			'username'               => $this->string()->notNull(),
			'password'               => $this->string()->notNull(),
			'require_authentication' => $this->boolean()->defaultValue( true ),
			
			'in_server'          => $this->string()->notNull(),
			'in_port'            => $this->integer()->notNull(),
			'in_encryption_type' => $this->enum( $protocols )->notNull()->defaultValue( 'No' ),
			
			'out_server'          => $this->string()->null(),
			'out_port'            => $this->integer()->null(),
			'out_encryption_type' => $this->enum( $protocols )->notNull()->defaultValue( 'No' ),
			
			'out_send_from'    => $this->string()->null()->after( 'out_encryption_type' ),
			'out_username' => $this->string()->null()->after( 'out_send_from' ),
			'out_password' => $this->string()->null()->after( 'out_username' ),
			
			'use_incoming_credentials' => $this->boolean()->defaultValue( true )->after( 'out_password' ),
			'enabled'                  => $this->boolean()->defaultValue( true )->after( 'use_incoming_credentials' ),
		];
		
		$this->alterTable( [
			'mode' => static::ALTER_MODE_IGNORE,
		] );
		
		return false;
		
	}
	
	
}
