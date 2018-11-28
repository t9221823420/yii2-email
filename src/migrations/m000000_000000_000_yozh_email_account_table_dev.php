<?php

use yozh\base\components\db\Migration;
use yozh\email\models\EmailAccount;

/**
 * Class m180301_142414_add_column_to_properties_table
 */
class m000000_000000_000_yozh_email_account_table_dev extends Migration
{
	protected static $_table;
	
	public function __construct( array $config = [] )
	{
		
		static::$_table = static::$_table ?? EmailAccount::getRawTableName();
		
		parent::__construct( $config );
		
	}
	
	public function safeUp( $params = [] )
	{
		parent::safeUp( [
			'mode' => 0 ? static::ALTER_MODE_UPDATE : static::ALTER_MODE_IGNORE,
		] );
	}
	
	public function getColumns( $columns = [] )
	{
		$protocols = [ 'No', 'SSL', 'TLS' ];
		
		return parent::getColumns( [
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
			
			'out_send_from' => $this->string()->null()->after( 'out_encryption_type' ),
			'out_username'  => $this->string()->null()->after( 'out_send_from' ),
			'out_password'  => $this->string()->null()->after( 'out_username' ),
			
			'use_incoming_credentials' => $this->boolean()->defaultValue( true )->after( 'out_password' ),
			'enabled'                  => $this->boolean()->defaultValue( true )->after( 'use_incoming_credentials' ),
		] );
	}
	
	
}
