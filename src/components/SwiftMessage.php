<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 25.01.2018
 * Time: 14:57
 */

namespace yozh\email\components;

class SwiftMessage extends \yii\swiftmailer\Message
{
	public function setTo( $to )
	{
		
		if( $address = $this->_parseAddress( $to ) ) {
			return parent::setTo( $address['email'] );
		}
		
		throw new \yii\base\InvalidParamException( "Invalid adress  to: $to" );
		
	}
	
	protected function _parseAddress( $address )
	{
		preg_match( '/(?<name>.*?)[\s<]*(?<email>[a-zA-Z0-9-_\.]+@[a-z0-9-\.]+\.[a-z]{2,})(?=(?:>|\s|$))/', trim( $address ), $matches );
		
		if( $matches ) { //
			return [
				'full'  => trim( $address ),
				'name'  => $matches['name'],
				'email' => $matches['email'],
			];
		}
		else { //
			return null;
		}
		
	}
	
	public function setFrom( $from )
	{
		if( $address = $this->_parseAddress( $from ) ) {
			return parent::setFrom( $address['email'] );
		}
		
		throw new \yii\base\InvalidParamException( "Invalid adress From: $from" );
		
	}
	
	
	public function setInReplyTo( $message_id )
	{
		$message_id = '<' . trim( $message_id, '<>' ) . '>';
		
		$this->getSwiftMessage()->getHeaders()->addTextHeader( 'In-Reply-To', $message_id );
		$this->getSwiftMessage()->getHeaders()->addTextHeader( 'References', $message_id );
		
		return $this;
	}
	
}