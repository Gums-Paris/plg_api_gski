<?php

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');
jimport('joomla.user.user');

use \Joomla\CMS\Factory;

JLoader::import('logistikform', JPATH_ROOT.'/components/com_sorties/models');
JLoader::import('logistik', JPATH_ROOT.'/components/com_sorties/models');
JLoader::import('sorties', JPATH_ROOT.'/components/com_sorties/helpers');

class GskiApiResourceLogistique extends ApiResource
{

	private $data;

	public function get()
	{
		$model = JModelLegacy::getInstance('Logistik', 'SortiesModel');
		$modelForm = JModelLegacy::getInstance('Logistikform', 'SortiesModel');

// on a besoin de savoir si ce user a le droit d'éditer pour lui présenter ou pas le bouton 'Modifier' dans GumsSki/logistique
		$user = $this->plugin->get('user');
		$input = Factory::getApplication()->input;
		$userId = $user->id;
		$sortieId = $input->getString('sortieid');
		$canEdit = SortiesHelper::canEditLogistique($userId, $sortieId);

		$data = new \stdClass;	
		try
		{
			$data = $model->getItem();
			if (!empty($data)) {
				foreach($data as $key => $value) {
					$result[$key] = trim($value);
				}
			$result['canedit'] = $canEdit;
				
			$id = $data->id;
			if ($input->getWord('task', '')=='edit' and $id>0){ $modelForm->checkout($id);}
			
			}else{
				$result = null;
			}
		}
		catch (Exception $e)
		{
			$result = $e->getCode();
		}
		$this->plugin->setResponse( $result );
	}

	public function post()
	{
		$user = $this->plugin->get('user');
		$input = Factory::getApplication()->input;
		$data = $input->post->getArray([
			'id'         => 'int',
			'sortieid' => 'string',
			'hotelchauffeurs'   => 'string',
			'tphchauffeurs'  => 'string',
			'dinerretour'    => 'string',
			'deposes'	=> 'string',
			'reprises'	=> 'string',
			'coursesprevues' => 'string',
			'meteo'      => 'string',
			'secours'      => 'string',
			]);  

		if (SortiesHelper::canEditLogistique($user->id, $data['sortieid']))
		{  
			$id   = $input->getInt('id', 0);
			$modelForm = JModelLegacy::getInstance('Logistikform', 'SortiesModel');
			$task = $input-> getString ('task', '');
			
// task=checkin	 ne touche pas aux données de la base, permet de faire un checkin de la ligne en cours d'édition 
// dans le cas ou l'usager de GumsSki touche le bouton "Annuler"
			if ($id>0 && $task == 'checkin') {
				$modelForm->checkin($id);
				$this->plugin->setResponse( $id );
			}else{
// sauvegarde normale		
				
				if ($id>0)
				{
					$modelForm->checkout($id);
				}
				
				$result = $modelForm->save($data);
				
				if ($result)
				{
					$modelForm->checkin($result);
				} 
				
				$this->plugin->setResponse( $result );
			}  
		}	
		else
		{
			ApiError::raiseError(401, "action interdite", 'APIUnauthorisedException');		
		}
	} 

}
