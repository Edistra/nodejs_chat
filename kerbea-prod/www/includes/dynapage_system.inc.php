<?php
if( !empty( $result_blocs[ 0 ][ 'page_includes' ])) {
    echo '<div class="include_exist">';
}
//print_r($result_blocs);
echo '<div class="block-grey"">
			<div class="container">';
if( !empty( $nb_blocs )) {
    $count_slider = 0;

    foreach( $result_blocs as $line_bloc ){
        if( !empty( $line_bloc[ 'id_bloc' ] )):
            /* a modifier : intégrer les différentes valeurs dans un fichier dans /config */
            /* Création de la variable contenant toutes les classes du bloc en cours. On commence par la classe propre au bloc qui contient le style perso et les margins */

            $bloc[ 'classes' ] = ' bstyle_'.$line_bloc[ 'id_bloc' ];

            foreach( $grid_conf[ 'variables' ] as $name_var => $variable ){
                $bloc_temp = json_decode( $line_bloc[ $name_var.'_bloc' ] );
                $bloc[ 'classes' ] .= ' b'.substr( $variable[ 0 ], 0, 1 ).'exl_'.$bloc_temp -> ecranXl.' b'.substr( $variable[ 0 ], 0, 1 ).'e_'.$bloc_temp -> ecran.' b'.substr( $variable[ 0 ], 0, 1 ).'t_'.$bloc_temp -> tablette.' b'.substr( $variable[ 0 ], 0, 1 ).'m_'.$bloc_temp -> mobile;
            }

            $bloc[ 'responsive' ] = json_decode($line_bloc[ 'responsive_bloc' ]);
            foreach( array( 'ecranXl', 'ecran', 'tablette', 'mobile' ) as $mode ){
                if( empty($bloc[ 'responsive' ] -> $mode )){
                    $bloc[ 'classes' ] .= ' no_'.$mode;
                }
            }

            $bloc[ 'classes' ] .= $line_bloc['type_bloc'] == 'image' ? ' bloc_image' : '';
            $bloc[ 'classes' ] .= $line_bloc['type_bloc'] == 'slider' ? ' bloc_slider' : '';

            $data_slider = $line_bloc[ 'type_bloc' ] == 'slider' ? 'data-slidernumber="'.++$count_slider.'"' : '';

//            echo '<div class="bloc_container'.$bloc[ 'classes' ].' '.$line_bloc[ 'class_bloc' ].'" style="background:'.$line_bloc[ 'backgroundContainer_bloc' ].';" '.$data_slider.'>
//							<div class="bloc_inner">';
            echo '<div class="bloc_inner">';

            if( $line_bloc['type_bloc'] == 'image' || $line_bloc['type_bloc'] == 'slider' ){
                if( $line_bloc['type_bloc'] == 'slider' ){
                    echo '<div class="bloc_slider_clip">';
                }
                foreach( $line_bloc[ 'images' ] as $line_image ){
                    $container_image = '<img src="'.RESSOURCE_URL.'/medias/dynapage/grande/'.$line_image[ 'nom' ].'" alt="';
                    $container_image .= !empty( $line_image[ 'datas' ][ 'alt' ] ) ? $line_image['datas'][ 'alt' ].'"' : '"';
                    $container_image .= !empty( $line_image[ 'datas' ][ 'title' ] ) ? ' title="'.$line_image[ 'datas' ][ 'title' ].'"' : '';
                    $container_image .= ' class="bloc_image" />';
                    $container_image .=  !empty( $line_image[ 'datas' ][ 'legende' ] ) ? '<span class="legende">'.$line_image[ 0 ][ 'datas' ][ 'legende' ].'</span>' : '';

                    if(!empty($line_image[ 'datas' ][ 'lightbox' ])){
                        $container_image = '<a href="'.RESSOURCE_URL.'/medias/dynapage/grande/'.$line_image[ 'nom' ].'" data-lightbox="'.$line_image[ 'nom' ].'">'.$container_image.'</a>';
                    }
                    elseif( !empty( $line_image[ 'datas' ][ 'url' ] ) ){
                        $url_blank = !empty( $line_image[ 'datas' ][ 'url_blank' ] ) ? ' target="_blank"' : '';
                        $container_image = '<a href="'.$line_image[ 'datas' ][ 'url' ].'"'.$url_blank.'>'.$container_image.'</a>';
                    }
                    if($line_bloc['type_bloc'] == 'slider'){
                        $container_image = '<div class="bloc_slider_item">'.$container_image.'</div>';
                    }
                    echo $container_image;

                    /* Si le type est image on arrete après le premier tour de boucle sinon on les parcourt toutes puisque c'est un slider*/
                    if( $line_bloc[ 'type_bloc' ] == 'image' ){
                        break;
                    }
                }
                if( $line_bloc['type_bloc'] == 'slider' ){
                    echo '</div>';
                }
            }
            else{
                echo $line_bloc['texte_bloc'];
            }
            echo '</div>
						</div>';
        endif;
    }
}
else {
    echo 'Aucun contenu n\'est disponible pour le moment.';
}

/* Fermeture du dynagrid et zone dyna */
echo '</div>
		</div>';

if( !empty( $result_blocs[ 0 ][ 'page_includes' ])) {
    echo '</div>';
}
?>