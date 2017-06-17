<?php
	
/*
    $array_option_menu = array( 'nom', 'separ', 'class' );
    $array_option_menu est nécessaire avant l'appel de cet include
    $array_menus doit être rempli depuis le fichier /includes/sql_commun.inc.php
*/
//echo '<pre>';
//print_r($array_menus);
//echo '</pre>';
if( !empty( $array_menus[ $array_option_menu[ 'nom' ] ] )){
    $list_param_menu = array( 'separ', 'classe', 'btn-home', 'html_before', 'html_after', 'exclude', 'no_target_blank', 'set_url');
    foreach( $list_param_menu as $param_menu ){
        $array_option_menu[ $param_menu ] = !empty( $array_option_menu[ $param_menu ] ) ? $array_option_menu[ $param_menu ] : '';
    }

    /* Par défaut on part toujours de RESSOURCE_URL comme base d'url, ici il faut prendre en compte que dans les pages des centres la base d'url est RESSOURCE_URL suivi de /centre-<nom_du_centre>
    sinon on ramenera toujours vers une page d'un menu principal et on perdra le coté "centre" */
    $base_url_link_menu = !empty( $array_option_menu[ 'base_url' ] ) ? $array_option_menu[ 'base_url' ] : RESSOURCE_URL;

    $treeMenu = $array_menus_actif = $class_menu = $menu_sortie = '';

//    echo '<div class="container-menu menu-'.$array_option_menu[ 'nom' ].' '.$array_option_menu[ 'classe' ].'">
//    <div class="menu-light no-ecran no-tablette">
//        <div class="accueil">
//            <div class="actionMenu"><span class="">Accueil</span></div>
//            <div class="both"></div>
//        </div>
//    </div>';

    $treeMenu = createTreeMenu(0, $array_menus[ $array_option_menu[ 'nom' ] ], array(), 1);
    // Récupération de la hiérarchie des menus actif
    $array_menus_actif = findAllParent_actif($treeMenu, $id_menu_sel);

    $menu_sortie = $array_option_menu['html_before'];
//    $menu_sortie .= '<div id="container_main_menu"><ul class="menu-1">';
    $menu_sortie .= '<ul>';
    $first_passe_niv0 = true;

    /* Gestion du bouton home s'il existe */
    if(is_array($array_option_menu['btn-home']) && !empty($array_option_menu['btn-home']['content'])){
        $class_btn_home = 'menu-1-item';
        $class_btn_home .= !empty($array_option_menu['btn-home']['class']) ? ' '.$array_option_menu['btn-home']['class'] : '';
        $class_btn_home .= 1 == $id_menu_sel ? ' active' : '';
        $menu_sortie .= '<li class="'.$class_btn_home.'"><a href="';
        $menu_sortie .= !empty($array_option_menu['btn-home']['link']) ? $array_option_menu['btn-home']['link'] : RESSOURCE_URL;
        $menu_sortie .= '">'.$array_option_menu['btn-home']['content'].'</a></li>';
        $first_passe_niv0 = false;
    }

    // Lecture des menus des niveau 0
    foreach($treeMenu as $index => $menu_niv_0){
        
        // exclusion des menus listé via l'option "exclude"
        if(is_array($array_option_menu[ 'exclude' ]) && in_array($menu_niv_0['id_menu'], $array_option_menu[ 'exclude' ])){
            continue;
        }
        if(is_array($array_option_menu['no_target_blank']) && in_array($menu_niv_0['id_menu'], $array_option_menu['no_target_blank'])){
            $menu_niv_0['urlBlank_menu'] = 0 ;
        }
        
        if(is_array($array_option_menu['set_url']) && array_key_exists($menu_niv_0['id_menu'], $array_option_menu['set_url'])){
            $menu_niv_0['url_menu'] = $array_option_menu['set_url'][$menu_niv_0['id_menu']];
        }
        
        $class_menu = 'menu-1-item';
        $class_menu .= (empty($menu_niv_0['mobile_menu'])) ? ' no-mobile': '';
        $class_menu .= (empty($menu_niv_0['tablette_menu'])) ? ' no-tablette': '';
        $class_menu .= (empty($menu_niv_0['ecran_menu'])) ? ' no-ecran': '';
        $class_menu .= (!empty($menu_niv_0['ss_menu'])) ? ' menu-item-parent': '';

        if(!$first_passe_niv0 && !empty($array_option_menu[ 'separ' ])) $menu_sortie .= '<li class="separ'.$class_menu.'">'.$array_option_menu[ 'separ' ].'</li>';
        else $first_passe_niv0 = false;

        // Récupération de la classe à mettre sur le menu
        $class_menu .= (count($menu_niv_0['ss_menu'])) ? ' has-ssmenu': '';
        $class_menu .= (!empty($array_menus_actif) && in_array($menu_niv_0['id_menu'], $array_menus_actif)) ? ' active': '';

        if(!empty($menu_niv_0['url_menu'])){
            if( strpos($menu_niv_0['url_menu'],'http:') !== false ){
                $url_menu = $menu_niv_0['url_menu'];
            }
            else{
                $url_menu = $menu_niv_0['url_menu'] != '/' ? $base_url_link_menu.'/'.$menu_niv_0['url_menu'] : $base_url_link_menu;
            }
        }
        elseif(!empty($menu_niv_0['id_page'])){
            $url_menu = $base_url_link_menu.'/'.rewrite_nom($menu_niv_0['alias_page']).'.html';
        }
        else{
            $url_menu = '#';
        }

        $class_menu .= $url_menu == '#' ? '' : '';

        // Ecriture du menu en cours
        $menu_sortie .= '<li class="'.$class_menu.'">'.createLink($menu_niv_0['nom_menu'],$url_menu,'',$menu_niv_0['urlBlank_menu']);

        if(!empty($menu_niv_0['ss_menu'])){

//            $menu_sortie .= '<div class="container-ssmenu">
//                <span class="fleche no-mobile no-tablette"></span>
//                <ul class="menu-2">';
            $menu_sortie .= '<ul class="menu-2">';

            // Lecture des menus des niveau 1 (si existant)
            foreach($menu_niv_0['ss_menu'] as $menu_niv_1){
                
                
                // exclusion des menus listé via l'option "exclude"
                if(is_array($array_option_menu[ 'exclude' ]) && in_array($menu_niv_1['id_menu'], $array_option_menu[ 'exclude' ])){
                    continue;
                }
                if(is_array($array_option_menu['no_target_blank']) && in_array($menu_niv_1['id_menu'], $array_option_menu['no_target_blank'])){
                    $menu_niv_1['urlBlank_menu'] = 0 ;
                }

                $class_menu = 'menu-2-item';

                $class_menu .= (empty($menu_niv_1['mobile_menu'])) ? ' no-mobile': '';
                $class_menu .= (empty($menu_niv_1['tablette_menu'])) ? ' no-tablette': '';
                $class_menu .= (empty($menu_niv_1['ecran_menu'])) ? ' no-ecran': '';

                // Récupération d'un éventuel menu actif
                $class_menu .= (!empty($array_menus_actif) && in_array($menu_niv_1['id_menu'], $array_menus_actif)) ? ' active': '';

                // Récupération de l'url
                if(!empty($menu_niv_1['url_menu'])){
                    if( strpos($menu_niv_1['url_menu'],'http:') !== false ){
                        $url_menu = $menu_niv_1['url_menu'];
                    }
                    else{
                        $url_menu = $menu_niv_1['url_menu'] != '/' ? $base_url_link_menu.'/'.$menu_niv_1['url_menu'] : $base_url_link_menu;
                    }
                }
                elseif(!empty($menu_niv_1['id_page'])){
                    $url_menu = $base_url_link_menu.'/'.rewrite_nom($menu_niv_1['alias_page']).'.html';
                }
                else{
                    $url_menu = '#';
                }

                // Ecriture du menu en cours
                $menu_sortie .= '<li class="'.$class_menu.'">'.createLink($menu_niv_1['nom_menu'],$url_menu,'',$menu_niv_1['urlBlank_menu']).'</li>';
            }
//            $menu_sortie .= '</ul></div>';
            $menu_sortie .= '</ul>';
        }
        $menu_sortie .= '</li>';
    }
//    $menu_sortie .= '</ul></div>';
    $menu_sortie .= '</ul>';
    $menu_sortie .= $array_option_menu['html_after'];

//    echo $menu_sortie.'
//        <div class="both"></div>
//    </div>';
    echo $menu_sortie;
}