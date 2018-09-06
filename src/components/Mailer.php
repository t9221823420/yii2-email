<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 25.01.2018
 * Time: 13:17
 */

namespace yozh\email\components;

use yozh\email\models\EmailAccount;

final class Mailer extends \yii\swiftmailer\Mailer
{
	public $messageClass = 'yozh\email\components\SwiftMessage';
	
	public $htmlLayout = false;
	
	public $textLayout = false;
	
	protected $_send_from;
	
	public function setTransport( $emailAccount, $close = true )
	{
		$config = [
			'class'      => \Swift_SmtpTransport::class,
			'host'       => $emailAccount->out_server,
			'username'   => $emailAccount->username,
			'password'   => $emailAccount->password,
			'port'       => $emailAccount->out_port,
			'encryption' => $emailAccount->out_encryption_type,
		];
		
		if( !$emailAccount->use_incoming_credentials
			//&& !empty( $emailAccount->out_email )
			&& !empty( $emailAccount->out_username )
			&& !empty( $emailAccount->out_password )
		) {
			$config['username'] = $emailAccount->out_username;
			$config['password'] = $emailAccount->out_password;
		}
		
		if( !empty( $emailAccount->out_send_from ) ){
			$this->_send_from = $emailAccount->out_send_from;
		}
		else{
			$this->_send_from = null;
		}
		
		parent::setTransport( $config );
		
		if( $close ){
			$this->getTransport()->stop();
		}
		
		return $this;
	}
	
	public function send( $message )
	{
		if( !empty($this->_send_from) ){
			$message->from = $this->_send_from;
		}
		
		return parent::send( $message ); // TODO: Change the autogenerated stub
	}
	
	
}