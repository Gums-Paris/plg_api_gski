<?php

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');
jimport('joomla.user.user');

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\FormModel;

/* noter que :
 * le résultat renvoyé au demandeur par GET ou POST doit toujours être un objet (pas un array) 
 * sinon erreur "utilisation de la method clone sur un non-objet"
 * 
 * les données envoyées par POST doivent être x-www-form-urlencoded (pas objet json envoyé en raw)
 */
 
class GskiApiResourceLogistique extends ApiResource
{

	private $data;

	public function get()
	{		
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sorties/models');
		FormModel::addIncludePath(JPATH_SITE . '/components/com_sorties/models');
		
		require_once ( JPATH_ROOT.'/components/com_sorties/helpers/sorties.php' );      

		$model = JModelLegacy::getInstance('Logistik', 'SortiesModel');
/		$modelForm = FormModel::getInstance('Logistikform', 'SortiesModel');		

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
			$data->canedit  =  $canEdit;	
			$id = $data->id;
// si on récupère les données pour pouvoir les modifier il faut faire un checkout			
			if ($input->getWord('task', '')=='edit' and $id>0){ $modelForm->checkout($id);}
			
			}else{
			$data = null;
			}
		}
		catch (Exception $e)
		{
			$data->erreur  =  $e->getCode();
		}
		$this->plugin->setResponse( $data );
	}

	public function post()
	{
		FormModel::addIncludePath(JPATH_SITE . '/components/com_sorties/models');
	
		require_once ( JPATH_ROOT.'/components/com_sorties/helpers/sorties.php' );      
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
/			$modelForm = FormModel::getInstance('Logistikform', 'SortiesModel');		
			$task = $input-> getString ('task', '');
			
			$retour = new \stdClass;	
			
// task=checkin	 ne touche pas aux données de la base, permet de faire un checkin de la ligne en cours d'édition 
// dans le cas ou l'usager de GumsSki touche le bouton "Annuler"
			if ($id>0 && $task == 'checkin') {
				$modelForm->checkin($id);
				$retour->retour	= $id;
				$this->plugin->setResponse( $retour );
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
				$retour->retour = $result;
				
				$this->plugin->setResponse( $retour );
			}  
		}	
		else
		{
			ApiError::raiseError(401, "action interdite", 'APIUnauthorisedException');		
		}
	} 

}
