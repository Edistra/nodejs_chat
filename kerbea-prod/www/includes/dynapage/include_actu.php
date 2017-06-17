<?php
	/*
	* Valeurs possible de la configuration de l'include via $include_actu
	* [home] = {boolean} -  restreint les résultats à ceux cochés en tant que publier home
	* [exception] = {string} -  la liste des id a ne pas afficher séparé par un |
	*/
	
	$where_actus = 'WHERE publier_actu = 1';
	
	if(!empty($include_actu['home'])){
		$where_actus .= ' AND publierHome_actu = 1';
	}
		
	if(!empty($include_actu['exception'])){
		$include_actu['exception'] = str_replace('|',',',$include_actu['exception']);
		$where_actus .= ' AND id_actu NOT IN ('.$PDO -> quote($include_actu['exception']).')';
	}
	
	$select_actus = $PDO -> prepare('SELECT *
	FROM actualite '.$where_actus.'
	AND langue_actu = "'.$_SESSION['langue'].'"
	AND (
		(dateDebut_actu<= NOW() AND dateFin_actu > NOW())
		OR (dateDebut_actu = "0000-00-00" AND dateFin_actu = "0000-00-00")
		OR (dateDebut_actu <= NOW() AND dateFin_actu = "0000-00-00")
		OR (dateDebut_actu = "0000-00-00" AND dateFin_actu > NOW())
	)
	ORDER BY idOrdre_actu DESC');
	$select_actus -> execute();
	$count_actus = $select_actus -> rowCount();
	
	if(!empty($count_actus)){
        echo ' <div id="container_actu">
            <div id="actu">
                <div class="bloc_une">

                    <h3 class="title_une">'.t('à la une','site').'</br>
                        <a href="'.RESSOURCE_URL.'/'.$_SESSION['langue'].'/actualite.php" class="link_une">'.t('tous les articles','site').'</a></h3>
                    <span class="simulHeight"></span>
                </div>';
		$select_actus = $select_actus -> fetchAll();
		
		echo '<div id="slider_actu">
			<div id="slider_actus_clip">';
				 
				foreach($select_actus as $line_actu){
					$url_actu = !empty($line_actu['link_actu']) ? $line_actu['link_actu'] : RESSOURCE_URL.'/'.$_SESSION['langue'].'/actualite.php?id='.$line_actu['id_actu'];
					echo '<div class="slider_actus_item">';
						$class_left_text = '';
						if(!empty($line_actu['image1_actu'])){
							echo createLink('<img src="'.RESSOURCE_URL.'/medias/actualite/galerie/moyenne/'.$line_actu['image1_actu'].'" alt="" class="block"/>', $url_actu, 'image', $line_actu['linkBlank_actu']);
							
							$class_left_text = ' image_on';
						}
						echo '<div class="area_text'.$class_left_text.'">
							<h3 class="title">'.createLink($line_actu['titre_actu'],$url_actu, '', $line_actu['linkBlank_actu']).'</h3>
							<div class="text">'.$line_actu['descriptionCourte_actu'].'</div>
							<div class="read_more">'.createLink(ucfirst(t('lire la suite', 'site')),$url_actu, '', $line_actu['linkBlank_actu']).'</div>
						</div>
					</div>';
				}
			echo '</div>
                </div>
            </div>
        </div>';
	}
?>