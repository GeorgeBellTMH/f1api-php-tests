<?php 

	
	/**
	 * Helper Class for the FellowshipOne.com API.
	 * @class FellowshipOne
	 * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
	 * @copyright 2012 Daniel Boorn
	 * @author Daniel Boorn daniel.boorn@gmail.com
	 * @author Tracy Mazelin tracy.mazelin@activenetwork.com.  Methods added, removed and adapted for PHPUnit Tests
	 * @requires PHP PECL OAuth, http://php.net/oauth, packaged with OAuth Adapter when PHP PECL OAuth is not present. PECL OAuth is STRONGLY Recommended for Modularity.
	 *
	 */

	class FellowshipOne{

		const TOKEN_CACHE_FILE = 0;
		const TOKEN_CACHE_SESSION = 1;
		const TOKEN_CACHE_CUSTOM = 2;
		
		public $settings;
		
		public $paths = array(
			'tokenCache'=> '../tokens/',//file path to local folder
			'general' => array(
				'accessToken'=>'/v1/Tokens/AccessToken',
			),
			'portalUser' => array(
				'userAuthorization'=>'/v1/PortalUser/Login',
				'accessToken'=>'/v1/PortalUser/AccessToken',
			),
			
		);
		
	
		/**
		 * contruct fellowship one class with settings array that contains
		 * @param unknown_type $settings
		 */
		public function __construct($settings){
			$this->settings = (object) $settings;
		}
					
		/**
		 * Generic HTTP GET function
		 * @param string $endpoint 
		 * @return object
		 */
		public function get($endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			return $this->fetchJson($url);
		}

		/**
		 * Generic HTTP POST function
		 * @param object $model 
		 * @param string $endpoint 
		 * @return object
		 */
		public function post($model, $endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_POST);
		}
		
		/**
		 * Generic HTTP PUT function
		 * @param object $model 
		 * @param string $endpoint 
		 * @return object
		 */
		public function put($model, $endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_PUT);
		}
		
		/**
		 * Generic HTTP DELETE function
		 * @param string $endpoint 
		 * @return object
		 */
		public function delete($endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			return $this->fetchJson($url,$model=null,OAUTH_HTTP_METHOD_DELETE);
		}


		/* Generic HTTP GET IMAGE function
		 * @param string $endpoint 
		 * @return object
		 */
		public function get_img($endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			return $this->fetchJson($url, $model=null, OAUTH_HTTP_METHOD_GET, $contentType='image/jpg');
		}


		/**
		 * Generic HTTP POST IMAGE function
		 * @param stream $img
		 * @param string $endpoint 
		 * @return object
		 */
		public function post_img($file, $endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			return $this->fetchJson($url,$file,OAUTH_HTTP_METHOD_POST, $contentType='image/jpg');
		}

		/**
		 * Generic HTTP PUT IMAGE function
		 * @param stream $img
		 * @param string $url
		 * @return object
		 */
		public function put_img($file, $url){
			return $this->fetchJson($url,$file,OAUTH_HTTP_METHOD_PUT, $contentType='image/jpg');
		}


		/**
		 * Grab the personId or householdId of a random person in the db
		 * @return array
		 */
		public function randomId(){
			$r = $this->fetchJson($this->settings->baseUrl . "/v1/People/Search?createdDate=2001-01-01");
			$additionalPages = $r['body']['results']['@additionalPages'];
			$randomPage = rand(0, $additionalPages);
			$r = $this->fetchJson($this->settings->baseUrl . "/v1/People/Search?createdDate=2001-01-01&page={$randomPage}");
			$index = rand(0,19); // 20 records per page is default
			$id = array(
				'person' => $r['body']['results']['person'][$index]['@id'],
				'household' => $r['body']['results']['person'][$index]['@householdID']
				);
			return $id;
		}
		
		/**
		 * BEGIN: OAuth Functions
		 */
		
		/**
		 * directly set access token. e.g. 1st party token based authentication
		 * @param array $token
		 */
		public function setAccessToken($token){
			$this->accessToken = (object) $token;
		}
		
		/**
		 * fetches JSON request on F1, parses and returns response
		 * @param string $url
		 * @param string|array $data
		 * @param const $method
		 * @param string $contentType
		 * @return object|boolean
		 */
		public function fetchJson($url,$data=null,$method=OAUTH_HTTP_METHOD_GET,$contentType="application/json"){
			try{
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($this->accessToken->oauth_token, $this->accessToken->oauth_token_secret);
				$headers = array(
					'Content-Type' => $contentType,
				);
				if($o->fetch($url, $data, $method, $headers)){
					$response["http_code"] = $o->getLastResponseInfo()['http_code'];
					$response["body"] = json_decode($o->getLastResponse(),true);
						 if($this->settings->debug){
						 	print_r($response);
						 }
				return $response;
				}
	
			}catch(OAuthException $e){
				$this->error = array(
					'error'=>true,
					'http_code'=>$e->getCode(),
					'response'=>$e->lastResponse,	
					'data'=>$data,
					'url'=>$url,
				);
				if($this->settings->debug){
					print_r($this->error);
					}
				return $this->error;
			}
		}	
		
		/**
		 * get access token from session by username
		 * @param string $username
		 * @return array|NULL
		 */
		protected function getSessionAccessToken($username){
			if(isset($_SESSION['F1AccessToken'])){
				//be sure to return object with "oauth_token" and "oauth_token_secret" properties
				return (object) $_SESSION['F1AccessToken'];
			}
			return null;
		}
		
		/**
		 * get cached access token by username
		 * @param string $username
		 * @param const $cacheType
		 * @return array|NULL
		 */
		protected function getAccessToken($username,$cacheType,$custoHandlers){
			switch($cacheType){
				case self::TOKEN_CACHE_FILE:
					$token = $this->getFileAccessToken($username);
					break;
				case self::TOKEN_CACHE_SESSION:
					$token = $this->getSessionAccessToken($username);
					break;
				case self::TOKEN_CACHE_CUSTOM:
					if($username){
						$token = call_user_func($custoHandlers['getAccessToken'],$username);
					}else{
						$token = call_user_func($custoHandlers['getAccessToken']);
					}
			}
			if($token) return $token;
		}
		
		
		
		/**
		 * save access token to session
		 * @param array $token
		 */
		protected function saveSessionAccessToken($token){
			$_SESSION['F1AccessToken'] = (object) $token;
		}
		
				
		/**
		 * 2nd Party credentials based authentication
		 * @param string $username
		 * @param string $password
		 * @param const $cacheType
		 * @return boolean
		 */
		public function login2ndParty($username,$password,$cacheType=self::TOKEN_CACHE_SESSION,$custoHandlers=NULL){
			$token = $this->getAccessToken($username,$cacheType,$custoHandlers);
			
			//$this->debug($token);
			
			if(!$token){
				$token = $this->obtainCredentialsBasedAccessToken($username,$password);
				$this->saveSessionAccessToken($token);
			}
			
			$this->accessToken = $token;
			
			return true;
		
		}

		/**
		 * obtain credentials based access token from API
		 * @param string $username
		 * @param string $password
		 * @return array
		 */
		protected function obtainCredentialsBasedAccessToken($username,$password){
			try{
				$message = urlencode(base64_encode("{$username} {$password}"));
				$url = "{$this->settings->baseUrl}{$this->paths['portalUser']['accessToken']}?ec={$message}";
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				return (object) $o->getAccessToken($url);
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
			
	}

	