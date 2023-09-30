<?php

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');


class GskiApiResourceListesorties extends ApiResource
{
	private $result;
	private $data;
	private $info;

	public function get()
	{
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sorties/models');

		require_once ( JPATH_ROOT.'/components/com_sorties/assets/DateSortie.class.php' );      
		
		$modelList 		= JModelLegacy::getInstance('sortieslist', 'SortiesModel');
		$result 		= array();
		$resultat       = new \stdclass;	
        $modelList->_activite = 1;
        $info 			= $modelList->getData();
// pour mémoire, ceci est la manière d'obtenir l'objet user correspondant au jeton si on en a besoin :
//		$user 			= $this->plugin->get('user');

 //echo' info <pre>';print_r($info);echo'</pre>';exit(0);
		if (!empty ($info)){
			
			foreach ($info as $key => $d) {
				$result[$key] = new \stdclass;	
				$tmp =  new DateSortie($d->date);
				$result[$key]->date_bdh = $tmp->date_format_bdh($d->jours);
				$result[$key]->id = $d->id;
				$result[$key]->titre = $d->titre; 
				$result[$key]->date = $d->date;
				$result[$key]->jours = $d->jours;
				$result[$key]->publier_groupes = $d->publier_groupes;
				$result[$key]->responsable = $d->name;
				$result[$key]->id_responsable = $d->responsable;

				$contactsResCar = $this->getResponsable($d->responsable);
				$result[$key]->email_rescar = $contactsResCar->email;
				$result[$key]->tel_rescar = $contactsResCar->tel; 
			}
		    $resultat = (object)$result;	
		}else{
			$resultat = null;
		}
// echo' resultat <pre>';print_r($resultat);echo'</pre>';exit(0);			
		 
		$this->plugin->setResponse( $resultat );
	}

	  /**
	  * Récupère le nom et le mail de l'organisateur
		*/
	  function getResponsable($id)
	  {
		$db = JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query = "select user.name, user.email, 
		if(cb_mobile<>'', cb_mobile, cb_telfixe ) as tel 
		from  #__users as user 
		left join `#__comprofiler` as cb on cb.user_id = user.id            
		where user.id = {$id}";
		$db->setQuery($query);
		$responsable = $db->loadObject();
		return $responsable;
	  }

	public function post()
	{
		// Add your code here
		
		$this->plugin->setResponse( $result );
	}
}

