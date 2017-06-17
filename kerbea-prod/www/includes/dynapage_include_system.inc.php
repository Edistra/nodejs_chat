<?php
	/* INCLUDES COLONNES */
	if(!empty($result_blocs)){
		echo '<div class="area_include">';

		$ligne_bloc = $result_blocs[0];
		// Si la page n'est pas vide
		if(!empty($ligne_bloc['includes_page'])){

			// On récupère chaque include
			$list_include_page = explode('/',$ligne_bloc['includes_page']);

			// pour chaque include, on enleve les espace et on vérifie son existence dans le dossier include
			foreach($list_include_page as $include_page){

				$include_page = trim($include_page);
				if(!empty($include_page)){
					$include_actu = $array_var_actu = array();
					
					echo '<div class="include_item">';
						
						// Récupération d'un éventuel article_colonne
						preg_match('#include_article_colonne\.php\?id\=(\d+)#',$include_page,$matchs);
						
						// Récupération d'un éventuel personnalisé
						preg_match('#include_actu\.php\?(.*)#',$include_page,$matchs_actu);

						// Si c'est bien un article colonne
						if(!empty($matchs[1])){
							// la variable $id_include_article_colonne doit obligatoirement être créée avant l'appel de l'include article colonne (utilisée dans ce dernier)
							$id_include_article_colonne = (int)$matchs[1];
							include(BASE_URL.WORK_DIR.'/includes/dynapage/include_article_colonne.php');
						}

						// Si c'est bien un include personnalisé
						elseif(!empty($matchs_actu[1])){

							$array_var_actu = explode('&',$matchs_actu[1]);

							// création du tableau de configuration d'include en fonction des variables "get" écrit au niveau de l'include
							foreach($array_var_actu as $var_actu){
								$index_include_actu = substr($var_actu,0,strpos($var_actu,'='));		// Nom de l'index
								$value_include_actu = substr($var_actu,strpos($var_actu,'=')+1);		// valeur liée
								$include_actu[$index_include_actu] = $value_include_actu;			// Enregistrement dans le tableau de config d'include
							}

							// appel de l'include propre (le tableau de config étant préparé au dessus)
							include(BASE_URL.WORK_DIR.'/includes/dynapage/include_actu.php');
						}
//						elseif(file_exists('includes/dynapage/'.$include_page)){
//							include(BASE_URL.WORK_DIR.'/includes/dynapage/'.$include_page);
//						}
					echo '<div class="both"></div>
					</div>';
				}
			}
			echo '<div class="both"></div>';
		}
		echo '</div>';
	}
?>