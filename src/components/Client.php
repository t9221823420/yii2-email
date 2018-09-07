<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 22.01.2018
 * Time: 18:18
 */

namespace yozh\email\components;

use yozh\email\models\EmailAccount;
use yii\base\InvalidParamException;
use roopz\imap\Imap;
use yozh\base\components\helpers\ArrayHelper;

class Client
{
	
	const ORDER_BY_UDATE = 'udate';
	const ORDER_BY_MSGNO = 'msgno';
	
	const ORDER_ASC  = 'ASC';
	const ORDER_DESC = 'DESC';
	
	protected $_imap;
	protected $_account;
	protected $_connectionParams;
	
	
	protected $_folders;
	protected $_activeFolder;
	protected $_root;
	protected $_headers;
	
	public function __construct( EmailAccount $EmailAccount, $path = null )
	{
		$this->_account          = $EmailAccount;
		$this->_connectionParams = [
			'imapPath'       => $this->getAddress( $path ),
			'imapLogin'      => $EmailAccount->username,
			'imapPassword'   => $EmailAccount->password,
			'serverEncoding' => 'utf-8',
			'attachmentsDir' => '/',
		];
		
		$this->_imap = new Imap( [
			'connection' => $this->_connectionParams,
		] );
		
		$this->_imap->createConnection();
	}
	
	public static function decodeMimeStr( $string, $charset = 'utf-8' )
	{
		$newString = '';
		$elements  = imap_mime_header_decode( $string );
		for( $i = 0; $i < count( $elements ); $i++ ) {
			if( $elements[ $i ]->charset == '_default' ) {
				$elements[ $i ]->charset = 'iso-8859-1';
			}
			$newString .= self::convertStringEncoding( $elements[ $i ]->text, $elements[ $i ]->charset, $charset );
		}
		
		return $newString;
	}
	
	/**
	 * Converts a string from one encoding to another.
	 * @param string $string
	 * @param string $fromEncoding
	 * @param string $toEncoding
	 * @return string Converted string if conversion was successful, or the original string if not
	 */
	public static function convertStringEncoding( $string, $fromEncoding, $toEncoding )
	{
		$convertedString = null;
		if( $string && $fromEncoding != $toEncoding ) {
			$convertedString = @iconv( $fromEncoding, $toEncoding . '//IGNORE', $string );
			if( !$convertedString && extension_loaded( 'mbstring' ) ) {
				$convertedString = @mb_convert_encoding( $string, $toEncoding, $fromEncoding );
			}
		}
		
		return $convertedString ?: $string;
	}
	
	public static function parseSender( $email, $defaultName = '' )
	{
		
		if( !preg_match( '/(?:(?<name>.*?)[\s]*<.*?)?(?<email>[a-zA-Z0-9-_\.]+).*?@(?<domain>[a-z0-9-\.]+amazon\.[a-z]{2,})(?=(?:>|\s|$))/', $email, $matches )){
			preg_match( '/(?:(?<name>.*?)[\s]*<.*?)?(?<email>[a-zA-Z0-9-_+\.]+).*?@(?<domain>[a-z0-9-\.]+\.[a-z]{2,})(?=(?:>|\s|$))/', $email, $matches );
		}
		
		$result = [];
		isset( $matches['name'] )
			? $result['name'] = $matches['name']
			: $result['name'] = $defaultName;
		
		if( $matches['email'] ?? false ) {
			$result['email'] = $matches['email'] . '@' . $matches['domain'];
		}
		else {
			throw new \yii\base\InvalidParamException( "Can not parse email address from '$email'" );
		}
		
		return $result;
	}
	
	public function getAddress( $path = null )
	{
		$address = "{" . $this->_account->in_server . ":" . $this->_account->in_port . "/imap";
		
		/*
		if( !$EmailAccount->validate_cert ) {
			$address .= '/novalidate-cert';
		}
		*/
		
		if( strtolower( $this->_account->in_encryption_type ) == 'ssl' ) {
			$address .= '/ssl';
		}
		else if( strtolower( $this->_account->in_encryption_type ) == 'tls' ) {
			$address .= '/tls';
		}
		
		$address .= '}' . $path;
		
		return $address;
		
	}
	
	public function __call( $name, $arguments )
	{
		if( method_exists( $this->_imap, $name ) ) {
			return call_user_func_array( [ $this->_imap, $name ], $arguments );
		}
		
		throw new UnknownClassException( "Method $name does not exists." );
	}
	
	public function getHeaders( $folder, $params = [] )
	{
		$defaults = [
			'page'   => 0,
			'offset' => 0,
			'size'   => 20,
			//'with_chains_offset' => 0, // если необходимо сдвинуть выборку из-за отфильтрованных цепочек
			//'order_by'           => self::ORDER_BY_UDATE,
			//'order_direction'    => self::ORDER_ASC,
		];
		
		$params = ArrayHelper::setDefaults( $params, $defaults );
		
		/*
		if( is_numeric( $params ) ) {
			$params = [ 'page' => (int)$params ];
		}
		
		if( is_array( $params ) ) {
			$params = array_replace( $defaults, array_intersect_key( $params, $defaults ) );
		}
		else {
			throw new InvalidParamException( "Invalid $params" );
		}
		*/
		
		/*
		if( $this->_headers && $this->_cache ) {
			return $this->_headers;
		}
		*/
		
		if( !$folder instanceof Folder ) { //
			$path   = $folder;
			$folder = $this->getFolder( $path );
		}
		
		if( !$folder ) {
			throw new InvalidParamException( "Invalid path: $path" );
		}
		
		$this->openFolder( $folder );
		
		$stream = $this->getStream();
		
		$boxInfo = $this->checkMailbox();
		
		$totalMessages = $boxInfo->Nmsgs;
		
		if( $params['page'] > 0 && $params['size'] > 0 ) {
			
			$totalPages = ceil( $totalMessages / $params['size'] );
			
			if( $params['page'] > $totalPages ) {
				$params['page'] = $totalPages; // $params['page'] будет равняться последней странице
			}
			
			$offset = ( $params['page'] - 1 ) * $params['size'] + $params['with_chains_offset'];
			
			if( $offset > $totalMessages ) {
				$offset = $totalMessages;
			}
			
			$limit = $offset + $params['size'];
			
			$offset++; // т.к. msgno начинается с 1
			
			if( $limit > $totalMessages ) {
				$limit = $totalMessages;
			}
			
		}
		else {
			$offset = 1;
			$limit  = $totalMessages;
		}
		
		if( $params['offset'] ) {
			$offset = $params['offset'] + 1;
		}
		
		if( $offset > $limit ) {
			return null;
		}
		
		/*
		$threads = imap_thread( $stream );
		
		$tree = [];
		
		foreach( $threads as $key => $val ) {
			
			list( $sequence, $type ) = explode( '.', $key );
			
			if( $type == 'num' ) {
				
				if( !isset( $tree[ $val ] ) ) {
					$tree[ $val ] = [ '' ];
				}
				
			}
			else if( $type == 'branch' ) {
				
				if( !isset( $tree[ $val ] ) ) {
					$tree[ $val ] = [];
				}
				
			}
		}
		*/
		
		$headers = imap_fetch_overview( $stream, "$offset:$limit", 0 );
		
		foreach( $headers as $header ) {
			
			if( isset( $header->subject ) ) { //
				$header->subject_decoded = self::decodeMimeStr( $header->subject );
			}
			else { //
				$header->subject_decoded = '';
			}
			
			$header->from_decoded = static::parseSender( self::decodeMimeStr( $header->from ) );
			
			if( !isset( $header->to ) ) {
				$header->to         = null;
				$header->to_decoded = [
					'email' => null,
				];
			}
			else {
				$header->to_decoded = static::parseSender( self::decodeMimeStr( $header->to ) );
			}
			
			//$header->chain = [];
			
			/*
			usort(
				$headers,
				function( $a, $b ) use ( $params ) {
					
					switch( $params['order_by'] ) {
						
						case self::ORDER_BY_UDATE:
							
							if( $params['order_direction'] == self::ORDER_DESC ) {
								return ( $a->udate < $b->udate ) ? -1 : 1;
							}
							else {
								return ( $a->udate > $b->udate ) ? -1 : 1;
							}
						
					}
				}
			);
			*/
			
		}
		
		return $headers;
	}
	
	public function getFolder( $path )
	{
		$folders = $this->getFolders( null, null, [ $path ] );
		
		if( empty( $folders ) || !isset( $folders[ $path ] ) ) { //
			return false;
		}
		else { //
			return $folders[ $path ];
		}
	}
	
	public function getFolders( $hierarchical = true, $parent_folder = null, $filter = null )
	{
		
		if( !$this->_folders ) {
			
			$this->_folders = [];
			
			$items = imap_getmailboxes( $this->getStream(), $this->getAddress(), '*' );
			//$items = $this->getListingFolders();
			
			foreach( $items as $item ) {
				
				$folder = new Folder( $this, $item );
				
				$this->_folders[ $folder->fullName ] = $folder;
				
				if( $folder->name != $folder->fullName
					&& ( $parentFullName = str_replace( $folder->delimiter . $folder->name, '', $folder->fullName ) )
					&& isset( $this->_folders[ $parentFullName ] )
				) {
					$this->_folders[ $parentFullName ]->children[ $folder->fullName ] = $folder;
				}
				else {
					$this->_root[ $folder->fullName ] = $folder;
				}
				
			}
			
		}
		
		if( is_string( $filter ) ) {
			$filter = [ $filter ];
		}
		
		if( is_array( $filter ) ) { //
			return array_intersect_key( $this->_folders, array_fill_keys( $filter, null ) );
		}
		else { //
			return $this->_folders;
		}
		
	}
	
	public function getStream( $forceConnection = true )
	{
		return $this->_imap->getImapStream( $forceConnection );
	}
	
	public function openFolder( Folder $folder )
	{
		
		static $counter = 0;
		static $count_stream = [];
		
		//if( !isset( $this->_activeFolder->path ) || $this->_activeFolder->path != $folder->path ) {
		if( $this->_activeFolder != $folder ) {
			
			$this->_activeFolder = $folder;
			
			/*
			$this->_connectionParams['imapPath'] = $folder->path;
			$this->_imap->setConnection( $this->_connectionParams );
			*/
			
			$stream = $this->getStream( true );
			
			$count_stream[] = $stream;
			$counter++;
			
			if( is_resource( $stream ) ) { //
				
				try {
					imap_reopen( $stream, $folder->path, OP_READONLY );
				} catch( \yii\base\ErrorException $e ) {
					$trap = 1;
				}
				
			}
			else { //
				$trap = 1;
			}
			
		}
	}
	
	public function getMessage( $folder, $msgNo )
	{
		if( !( $folder instanceof Folder ) ) { //
			$folder = $this->getFolder( $folder );
		}
		
		if( $folder instanceof Folder ) { //
			
			$this->openFolder( $folder );
			
			return new Message( $msgNo, 1, $this );
		}
		
		return null;
	}
	
}