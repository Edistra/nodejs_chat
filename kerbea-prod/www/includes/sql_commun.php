<?php
// On crée le tableau de selection des requete s'il n'existe pas
if(empty($array_requetes)){
    $array_requetes = array();
}

/* ################################################################################ */

$select_all_agences = $PDO -> query('SELECT nom_agence, alias_agence FROM agence WHERE actif_agence = 1 ORDER BY nom_agence ASC');
$select_all_agences_array = $select_all_agences ->fetchAll();
// Selection des sections
$select_section = $PDO -> query('SELECT * FROM dyna_section ORDER BY id_section');
// Selection des menus

if($select_section -> rowCount()){

    foreach($select_section as $line_section){
        $result_select_menu = $PDO->query("SELECT *
        FROM dyna_menu
        JOIN dyna_section ON dyna_menu.id_section = dyna_section.id_section
        LEFT JOIN dyna_page ON dyna_menu.id_page = dyna_page.id_page
        WHERE publier_menu = '1'
        AND dyna_menu.id_section = ".$PDO -> quote( $line_section['id_section'] )."
        AND dyna_menu.langue_menu = ".$PDO -> quote($_SESSION['langue'])."
        ORDER BY idOrdre_menu");
        $compt_select_menu = $result_select_menu->rowCount();
        $result_select_menu = $result_select_menu->fetchAll();

        foreach($result_select_menu as $row){
            $array_menus[$line_section['nom_section']][$row['id_menu']] = array(
                'id_menu' => $row['id_menu'],
                'nom_section' => $row['nom_section'],
                'idParent_menu' => $row['idParent_menu'],
                'nom_menu' => $row['nom_menu'],
                'url_menu' => $row['url_menu'],
                'urlBlank_menu' => $row['urlBlank_menu'],
                'nom_page' => $row['nom_page'],
                'langue_menu' => $row['langue_menu'],
                'alias_page' => $row['alias_page'],
                'id_page' => $row['id_page'],
                'mobile_menu'=>$row['mobile_menu'],
                'tablette_menu'=>$row['tablette_menu'],
                'ecran_menu'=>$row['ecran_menu'],
                'includes_menu'=>$row['includes_menu']
            );
        }
    }
}

/**
    @parent id du menu parent

    @array tableau contenant tous les menus
    @array_parent tableau contenant tous les ancetres d'un menu donné
*/
function createTreeMenu($parent,$array_source,$array_parent,$niveau){
    $i = 0;
    // Tableau qui contiendra l'arbre des menus
    $array_sortie = array();

    // On parcourt tous les menus
    foreach ($array_source AS $menu){
        // Si le menu en cours appartient au bon niveau actuellement exploré
        if ($parent == $menu['idParent_menu']){

            // Le menu i du tableau de retour vaut le menu actuellement lu
            $array_sortie[$i] = $menu;
            $array_sortie[$i]['niveau'] = $niveau;
            // Si le menu parent de ce menu n'est pas encore écrit dans la liste de ses ancetres, on le met dedans
            if(!in_array($menu['idParent_menu'], $array_parent)){
                $array_parent[] = $menu['idParent_menu'];
            }
            // On ajoute au menu actuel la liste de ses ancetres
            $array_sortie[$i]['parents'] = $array_parent;

            // Recherche d'éventuels sous menus, pour le menu actuel, qui auront pour parents_id l'id de ce menu
            $array_sortie[$i]['ss_menu'] = createTreeMenu($menu['id_menu'],$array_source,$array_parent,($niveau+1));


            // Un menu a été trouvé, on peut déplacer le curseur pour écrire le prochain
            $i++;
        }
    }
    // On retourne la liste de ces menus frere
    return $array_sortie;
}



/**
    Recherche un élément et retourne tous ses parents dans un tableau
    @array (array) tableau de menu cible de la fonction de recherche
    @needle (string) valeur à rechercher dans le array_menu
*/
function findAllParent_actif($array, $needle) {

    // On parcourt chaque menu (au premier lancement on ne verra dans cette boucle que les menu de niveau 0);
    for($i = 0; $i < count($array); $i++){
        foreach ($array[$i] as $key => $value) {
            // Si on trouve un id et que la valeur correspond à celle recherchée

            if($key == 'id_menu' && $value == $needle){

                // On récupère les élements parents de cet élément et on y ajoute son propre id (pour avoir un tableau d'adn complet)
                $array_retour = $array[$i]['parents'];
                $array_retour[] = $value;

                return $array_retour;
            }
            // Si on trouve une ligne de sous menus
            elseif($key == 'ss_menu' && count($array[$i][$key])){

                // On relance une recherche dans ses sous menus
                $trouve = findAllParent_actif($value,$needle);

                // Si la recherche dans les sous menus à été un succès, on renvoit la valeur directement
                if($trouve !== false){
                    return $trouve;
                }
            }
        }
    }
    // Si aucun des menus de ces niveaux n'a donné de résultat
    return false;
}