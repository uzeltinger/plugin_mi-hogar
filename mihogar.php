<?php
jimport('joomla.plugin.plugin');
//class structure example
class plgAPIMihogar extends ApiPlugin
{
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config = array());		
		// Set resource path
		ApiResource::addIncludePath(dirname(__FILE__).'/mihogar');
		$this->setResourceAccess('login', 'public', 'post');
		// Load language files
		$lang = JFactory::getLanguage(); 
		$lang->load('com_users', JPATH_ADMINISTRATOR, '', true);		
	}
}
?>