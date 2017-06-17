<?php
	// vérification de l'existence de la variable lié à cet include
	if(!empty($id_include_article_colonne)){

		// selection des informations liées
		$select_article_colonne = $PDO -> prepare('SELECT * FROM article_colonne WHERE id_articleCol = :id_articleCol AND publier_articleCol = 1');
		$select_article_colonne -> execute(array('id_articleCol' => $id_include_article_colonne));
		$count_article_colonne = $select_article_colonne -> rowCount();

		if(!empty($count_article_colonne)){
			$ligne_article_colonne = $select_article_colonne -> fetch();
			$style_bg_title = !empty($ligne_article_colonne['titreBgColor_articleCol']) ? ' background:#'.$ligne_article_colonne['titreBgColor_articleCol'].';padding:10px;' : '';
			$style_text_title = !empty($ligne_article_colonne['titreColor_articleCol']) ? ' color:#'.$ligne_article_colonne['titreColor_articleCol'].';' : '';
			$style_bg_article = !empty($ligne_article_colonne['textBgColor_articleCol']) ? ' background:#'.$ligne_article_colonne['textBgColor_articleCol'].';padding:10px' : '';
			$style_color_article = !empty($ligne_article_colonne['textColor_articleCol']) ? ' color:#'.$ligne_article_colonne['textColor_articleCol'].';' : '';
			
			$article_colonne = '<div class="article_colonne">';
				$article_colonne .= !empty($ligne_article_colonne['titre_articleCol']) ? '<h2 style="'.$style_bg_title.$style_text_title.'">'.createLink($ligne_article_colonne['titre_articleCol'],$ligne_article_colonne['link_articleCol'],'',$ligne_article_colonne['linkBlank_articleCol']).'</h2>' : '';
				$article_colonne .= !empty($ligne_article_colonne['image1_articleCol']) ? '<div class="img">'.createLink('<img src="'.RESSOURCE_URL.'/medias/article_colonne/galerie/moyenne/'.$ligne_article_colonne['image1_articleCol'].'" alt="" />',$ligne_article_colonne['link_articleCol'],'',$ligne_article_colonne['linkBlank_articleCol']).'</div>' : '';
				$article_colonne .= !empty($ligne_article_colonne['descriptionLongue_articleCol']) ? '<div class="text" style="'.$style_bg_article.$style_color_article.'">'.$ligne_article_colonne['descriptionLongue_articleCol'].'</div>' : '';
			$article_colonne .= '</div>';
			
			echo $article_colonne;
		}
	}
?>