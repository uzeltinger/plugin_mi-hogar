<?php
class MihogarApiResourceLogin extends ApiResource
{
	public function get()
	{
		//http://joomla.local/index.php?option=com_api&app=users&resource=login&format=raw
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$response['status'] = 'ok';
		$response['is_guest'] = $user->get('guest');
		$response['userid'] = $user->get('id');
		$response['session_id'] = $session->getId();
		$response['session_expire'] = $session->getExpire();		
		//$response['hash'] = $this->store($response);
		
		//$table = JTable::getInstance('Key', 'ApiTable');
		//$table->loadByHash('33');
		//print_r( $table->id );die();

		$this->plugin->setResponse( $response );
	}

	public function post()
  		{
		//	http://joomla.local/index.php?option=com_api&app=users&resource=login&format=raw
		//{	"username":"admin",	"password":"admin" }
		$app = JFactory::getApplication();
		if (JFactory::getUser()->get('guest') != 1)
		{
		$response['status'] = 'Already logged in'; // Already logged in
		$response['login_result'] = true;

		$user = JFactory::getUser();
		$userid = $user->get('id');

			if ($userid == 0)
			{
				$response['status'] = 'User not enabled'; // User not enabled
				$response['login_result'] = false;
				return false;
			} else {
				// Success
				$response['status'] = 'ok';
				$response['userid'] = $userid;
				$response['username'] = $user->get('username');
				$response['session_id'] = JFactory::getSession()->getId();
				$response['login_result'] = true;				
				$response['hash'] = $this->store($response);
			}
		
		}else{	

			// Get the input data as JSON
			$json = new JInputJSON;
			$json_data = json_decode($json->getRaw(), true);
		

			$credentials = array();
			$credentials['username'] = (isset($json_data)) ? @$json_data['username'] : $app->input->getString('username');
			$credentials['password'] = (isset($json_data)) ? @$json_data['password'] : $app->input->getString('password');

			$options = array();
			$options['silent'] = true;			

			$result = false;
			if ($app->login($credentials, $options) === true)
			{
			$user = JFactory::getUser();
			$userid = $user->get('id');
			if ($userid == 0)
				{
					$response['status'] = 'User not enabled'; // User not enabled
					$response['login_result'] = false;
					return false;
				} else {
					// Success
					$response['status'] = 'ok';
					$response['userid'] = $userid;
					$response['username'] = $user->get('username');
					$response['session_id'] = JFactory::getSession()->getId();
					$response['login_result'] = true;							
					$response['hash'] = $this->store($response);
				}
			} else {
			// Login failed
			$response['status'] = 'Login failed'; // Login failed
			$response['login_result'] = false;
			}
		
		}
		$this->plugin->setResponse( $response );
  	}


    public function post22222()
	{
		// Validation Error sets HTTP 400
		ApiError::raiseError(10001, "Invalid Email", 'APIValidationException');

		// Access Error sets HTTP 403
		ApiError::raiseError(11001, "Not authorised", 'APIUnauthorisedException');

		// Not Found Error sets HTTP 404
		ApiError::raiseError(12001, "Record not found", 'APINotFoundException');

		// General Error sets HTTP 400
		ApiError::raiseError(10000, "Bad Request", 'APIException');

	}
	
	public function store($data = false)
	{
		$db		 = JFactory::getDBO();		
		$query = 'DELETE FROM #__api_keys WHERE userid = ' . $data['userid'];
		$db->setQuery($query);
		$db->query();

		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_api/tables');	
		$row 	= JTable::getInstance('Storekey', 'ApiTable');

		if ($data['userid'])
		{			
				$string = $data['userid'] . time();
				// @TODO : Better hashing algo
				$hash = md5($string);

				$dataSave['userid'] = $data['userid'];
				$dataSave['hash'] = $hash;
				$dataSave['state'] = 1;
				$dataSave['checked_out'] = 0;
				$dataSave['per_hour'] = 0;
				$dataSave['created_by'] = $data['userid'];
				
				if (!$row->bind($dataSave)) {
					 $setError = $db->getErrorMsg() ;
					echo $db->getErrorMsg();
					return null;
				}		
				if (!$row->check()) {
					$setError = $db->getErrorMsg() ;
					echo $db->getErrorMsg();
					return null;
				}		
				if (!$row->store()) {
					$setError = $db->getErrorMsg() ;	
					echo $db->getErrorMsg();	
					return null;
				}
		
				return $row->hash;
		}
	}
}
?>