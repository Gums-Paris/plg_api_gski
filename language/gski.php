<?php

jimport('joomla.plugin.plugin');

class plgAPIGski extends ApiPlugin
{
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config = array());
		
		// Set resource path
		ApiResource::addIncludePath(dirname(__FILE__).'/gski');
		
		// Load language files
		$lang = JFactory::getLanguage(); 
		$lang->load('plg_api_gski', JPATH_ADMINISTRATOR, '', true);
		
		// Set the liste des inscrits resource to be public
//		$this->setResourceAccess('inscrits', 'public', 'get');
//		$this->setResourceAccess('paramsortie', 'public', 'get');
		$this->setResourceAccess('listesorties', 'public', 'get');
	}
}
