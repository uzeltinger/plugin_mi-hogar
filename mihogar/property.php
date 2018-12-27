<?php
class MihogarApiResourceProperty extends ApiResource
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
			}             

            // Get the input data as JSON
			$json = new JInputJSON;
			$json_data = json_decode($json->getRaw(), true);		

			$credentials = array();
			$credentials['username'] = (isset($json_data)) ? @$json_data['username'] : $app->input->getString('username');
			$credentials['password'] = (isset($json_data)) ? @$json_data['password'] : $app->input->getString('password');
        
            $response = $this->saveProperty();

		}else{	

			$response['status'] = 'ocurrió un error';
		
		}
		$this->plugin->setResponse( $response );
      }
      
      private function saveProperty(){      
        $retorno[] = 'tratando de guardar';
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_osproperty/tables');	
        //require_once(JPATH_ROOT."/components/com_osproperties/classes/listing.php");
        $db = JFactory::getDBO();
		$row = JTable::getInstance('Property','OspropertyTable');
        $user = JFactory::getUser();
		$json = new JInputJSON;
        $json_data = json_decode($json->getRaw(), true);
		$row->bind($json_data);
        $id = $json_data['id'];
        $isNew = ($id == 0 ? 1:0);
        
        $row->agent_id = $this->getAgentID();
        $row->company_id = $this->getCompanyId();
        $row->isFeatured = 0;
        $row->state = 52;
        $row->country = 9;

        if($id == 0){
			$row->created = date("Y-m-d",time());
			$row->created_by = $user->id;
			$row->hits =  0;
			$row->modified = $row->created;
            $row->approved = 1;
            $row->published = 1;
            $row->publish_up = date("Y-m-d");
			JFolder::create(JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$id);
			JFolder::create(JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$id.DS."thumb");
			JFolder::create(JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$id.DS."medium");
		}else{
			$row->modified = date("Y-m-d",time());
			$row->modified_by = $user->id;
			$row->company_id  = JRequest::getInt('company_id',0);
        }


        //store into database
		if (!$row->store()) {
            JError::raiseError(500, $row->getError() );
            return 'error';
        }

        $pro_alias = $id . "-" . JApplication::stringURLSafe($json_data['pro_name']);

		$db->setQuery("Update #__osrs_properties set pro_alias = '$pro_alias' where id = '$id'");
		$db->query();

        $id = $row->id;

		//Update into Property table
		/*
		$catid = $row->category_id;
		$db->setQuery("Delete from #__osrs_property_categories where pid = '$id'");
		$db->query();
		
		$db->setQuery("Insert into #__osrs_property_categories (id,pid,category_id) values (NULL,'$id','$catid')");
		$db->query();
*/

	$categoryIds = [];
	$categoryIdsObject = $json_data['categoryIds'];
	//print_r($categoryIdsObject);die();
		foreach($categoryIdsObject as $ctids){
		$categoryIds[] = $ctids['id'];
		}

		if(count($categoryIds) > 0){
			$db->setQuery("Delete from #__osrs_property_categories where pid = '$id'");
			$db->query();
				foreach ($categoryIds as $catid){
					$db->setQuery("Insert into #__osrs_property_categories (id,pid,category_id) values (NULL,'$id','$catid')");
					$db->query();
					//echo '<br>'.$catid;
				}
			}
				if(isset($json_data['base64Image'])){
					$base64Image=$json_data['base64Image'];
				}
        

        if(isset($base64Image) && $base64Image!=''){		
            $base64_string=$base64Image;
            $output_file = $id.time().'.jpg';            

            $j = 1;
			$photo_name    = "photo_".$j;
			$desc_name     = "photodesc_".$j;
			$ordering_name = "ordering_".$j;
			$photorecord   = JTable::getInstance('Photo','OspropertyTable');
			$photorecord->id = 0;
			$photorecord->image_desc = '';
			$photorecord->pro_id     = $id;
            $photorecord->ordering   = 1;


            $image_name = $output_file;
                    $original_image_link = JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$id.DS.$image_name;
                    $data = explode( ',', $base64Image );	
                    $guarda = JFile::write($original_image_link, base64_decode( $data[ 1 ] ));
                    //$retorno[] = $guarda;
					//copy and resize
					//thumb
					$thumb_image_link = JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$id.DS."thumb".DS.$image_name;
                    $guarda_thumb = JFile::write($thumb_image_link, base64_decode( $data[ 1 ] ));
					//$guarda_thumb = JFile::copy($original_image_link,$thumb_image_link);
                    //$retorno[] = $guarda_thumb;
					
					//medium
				    $medium_image_link = JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$id.DS."medium".DS.$image_name;
                    $guarda_medium = JFile::write($medium_image_link, base64_decode( $data[ 1 ] ));
				    //$guarda_medium = JFile::copy($original_image_link,$medium_image_link);
                    //$retorno[] = $guarda_medium;
						    
				    $photorecord->image = $image_name;
				    //save the image
					$photorecord->store();               
            
        
                    //  remove foto
                    $image = $json_data['image'];
                    if($image==''){
                        $image_id = $json_data['image_id'];
                        $db = JFactory::getDbo();
                        
                                $db->setQuery("Select image from #__osrs_photos where id = '$image_id'");
                                $image = $db->loadResult();
                                $db->setQuery("Select pro_id from #__osrs_photos where id = '$image_id'");
                                $pro_id = $db->loadResult();
                                @unlink(JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$pro_id.DS.$image);
                                @unlink(JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$pro_id.DS."thumb".DS.$image);
                                @unlink(JPATH_ROOT.DS."images".DS."osproperty".DS."properties".DS.$pro_id.DS."medium".DS.$image);
                                $db->setQuery("Delete from #__osrs_photos where id = '$image_id'");
                                $db->query();
                    }
                    
                           
                        
                    
            //$offer_pictures_path = $this->base64_to_jpeg($base64_string, $output_file, $id);
           /* $picture_path = JBusinessUtil::makePathFile(OFFER_PICTURES_PATH.($offerId)."/".$output_file);
            $datapictures['pictures'][0]['picture_info'] = '';
            $datapictures['pictures'][0]['picture_path'] = $picture_path;
            $datapictures['pictures'][0]['picture_enable'] = 1;
            $this->storePictures($datapictures, $offerId, $oldId);*/
            }
            //$categoryIds = [];
            //$categoryIds = $category_id;
            $amenities = null;
            $pro_full_desc = null;
            
            $retorno['guardado'] = true;

        require_once(JPATH_ROOT . '/administrator/components/com_osproperty/'.DS."diportal".DS."save.php");


        //print_r($json_data);die('kk');

          return $retorno;    

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
    
    static function getAgentID(){
		global $mainframe;
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$db->setQuery("Select id from #__osrs_agents where user_id = '$user->id'");
		$agent_id = $db->loadResult();
		return $agent_id;
    }
    static public function getCompanyId(){
		global $mainframe;
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		
			$db->setQuery("Select id from #__osrs_companies where user_id = '$user->id' and published = '1'");
			$count = $db->loadResult();
			//echo $count;
			//die();
			if($count == 0){
				return 0;
			}else{
				return  $count;
			}
		
	}
}
?>