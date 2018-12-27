<?php
class MihogarApiResourceLogout extends ApiResource
{
	public function get()
	{
        $app = JFactory::getApplication();
		$error  = $app->logout();
		$this->plugin->setResponse( $error );
	}

	public function post()
  	{		
        $app = JFactory::getApplication();
		$error  = $app->logout();
		$this->plugin->setResponse( $error );
  	}
}
?>