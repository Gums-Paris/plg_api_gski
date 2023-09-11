<?php

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');

use Joomla\CMS\Factory;

//JLoader::import('sortiesinscritslist', JPATH_ROOT.'/components/com_sorties/models');
//JLoader::import('sortieslist', JPATH_ROOT.'/components/com_sorties/models');

class GskiApiResourceInscrits extends ApiResource
{
	private $result;
	private $data;
	private $info;

	public function get()
	{
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sorties/models');
		
		require_once ( JPATH_ROOT.'/components/com_sorties/assets/DateSortie.class.php' );      
		
        $input = Factory::getApplication()->input;
        $sortieId = $input->getInt('sortieid', 0);
		$modelInscrits 	= JModelLegacy::getInstance('sortiesinscritslist', 'SortiesModel');
		$modelList 		= JModelLegacy::getInstance('sortieslist', 'SortiesModel');
		$result 		= array();
		$resultat       = new \stdclass;	
        $info 			= $modelList->getData();
// pour mémoire, ceci est la manière d'obtenir l'objet user correspondant au jeton si on en a besoin :
//		$user 			= $this->plugin->get('user');
    
// Normalement on n'appelle "inscrits" que si on a récupéré un sortieid, donc une sortie existe, mais par prudence on envisage que $info soit vide.
// On ne fait pas pareil pour publier_groupes parce que cela a pu changer depuis la dernière fois que GumsSki a appelé.
// Si les groupes ne sont pas faits on renvoie null.

		$publier_groupes = 0;
        if (!empty ($info)) {
			
			foreach ($info as $s) {
				if ($s->id == $sortieId){
					if ($s->publier_groupes >1) {
						$publier_groupes = 2;
						break;
					}
				}else{
					continue;
				}
			}
			
			if ($publier_groupes > 1){
				$modelInscrits->trieGroupe();
				$data = $modelInscrits->getData();
				
				foreach ($data as $key => $d) {
					$result[$key] = new \stdclass;	
					$result[$key]->id = $d->id;
					$result[$key]->userid = $d->userid;
					$result[$key]->statut = $d->statut;
					$result[$key]->responsabilite = $d->responsabilite; 
					$result[$key]->groupe = $d->groupe;
					$result[$key]->peage = $d->peage;
					$result[$key]->autonome = $d->autonome;
					$result[$key]->ordering = $d->ordering;
					$result[$key]->name = $d->name;
					$result[$key]->email = ($d->email != null) ? $d->email : '' ;
					$result[$key]->tel = ($d->tel != null) ? $d->tel : '' ;
//					$result[$key]->email = $d->email;
//					$result[$key]->tel = $d->tel;
					$result[$key]->ordre = $d->ordre;
				}
				$resultat = (object)$result;			 
				
//			 echo'<pre>';print_r($result);echo'</pre>';exit(0);			
			}else{ 
				$resultat = null; // parce que groupes non publiés
			}	
		}else{
			$resultat = null; // parce que liste sorties est vide (c'est pas possible pour GumsSki qui dans ce cas ne demande pas les participants)
		}
		$this->plugin->setResponse( $resultat );
	}
 



	public function post()
	{
		// Add your code here
		
		$this->plugin->setResponse( $result );
	}
}

