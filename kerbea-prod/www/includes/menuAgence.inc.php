<?php

    $array_class_menu_agence_active = array_fill(0, 5, '');

    $active_menu = empty($environnement_agence['menu']['active']) ? 0 : $environnement_agence['menu']['active'];

    $array_class_menu_agence_active[$active_menu] = ' active';

?>



<div class="container-menu menu-principal ">

    <div class="menu-light no-ecran no-tablette">

        <div class="accueil">

            <div class="actionMenu"><span class="">Accueil</span></div>

            <div class="both"></div>

        </div>

    </div>

    <div id="container_main_menu">

        <ul class="menu-1">

    <!-- each li ending tag must be on its predecessor's line (cursed inline-block)-->

            <li class="menu-1-item menu-home<?php echo $array_class_menu_agence_active [0]; ?>">

                <a href="<?php echo RESSOURCE_URL.'/agence-'.$_GET['alias']; ?>"><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/menu-home.png"></a>

            </li><li class="menu-1-item<?php echo $array_class_menu_agence_active [1]; ?>">

                <a href="<?php echo RESSOURCE_URL.'/agence-'.$_GET['alias']; ?>/maisons-individuelles-3">Nos maisons</a>

            </li><li class="menu-1-item<?php echo $array_class_menu_agence_active [2]; ?>">

                <a href="<?php echo RESSOURCE_URL.'/agence-'.$_GET['alias']; ?>/annonces-terrain-maison">Nos offres</a>

            </li><li class="menu-1-item<?php echo $array_class_menu_agence_active [3]; ?>">

                <a href="<?php echo RESSOURCE_URL.'/agence-'.$_GET['alias']; ?>/realisations">Nos réalisations</a>

            </li><li class="menu-1-item<?php echo $array_class_menu_agence_active [4]; ?>">

                <a href="<?php echo RESSOURCE_URL.'/agence-'.$_GET['alias']; ?>/chantier">Nos chantiers</a>

            </li><li class="menu-1-item has-ssmenu"><a href="#">Conseils</a>

                <div class="container-ssmenu">

                    <span class="fleche no-mobile no-tablette"></span>

                    <ul class="menu-2">

                        <li class="menu-2-item"><a href="http://www.maisons-kerbea.fr/pourquoi-construire.html" target="_blank">pourquoi construire ?</a></li>

                        <li class="menu-2-item"><a href="http://www.maisons-kerbea.fr/les-etapes-de-votre-projet.html" target="_blank">Les étapes de votre projet</a></li>

                        <li class="menu-2-item"><a href="http://www.maisons-kerbea.fr/les-aides-financieres.html" target="_blank">Les aides financières</a></li>

                        <li class="menu-2-item"><a href="http://www.maisons-kerbea.fr/calculs.php" target="_blank">Simulateurs de prêts</a></li>

                        <li class="menu-2-item"><a href="http://www.maisons-kerbea.fr/la-reglementation-thermique.html" target="_blank">La réglementation thermique</a></li>

                        <li class="menu-2-item"><a href="http://www.maisons-kerbea.fr/lexique.php" target="_blank">Lexique</a></li>

                    </ul>

                </div>

            </li>

        </ul>

        <div class="both"></div>

    </div>

</div>
