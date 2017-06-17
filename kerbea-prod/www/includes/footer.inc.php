<footer>
    <div class="grey-stripes"></div>
    <div class="container">
        <div class="col footer-logo">
            <div class="logo-xs"></div>
        </div>
        <div class="col">
            <?php if(count($select_all_agences_array)): ?>
                <h4>Constructeurs en régions</h4>
                <ul class="list-agence">
                    <?php foreach($select_all_agences_array as $agence): ?>
                        <li><a href="<?php echo RESSOURCE_URL.'/agence-'.$agence['alias_agence']; ?>">Constructeur Kerbéa <?php echo $agence['nom_agence']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="col">
            <?php
//            print_r($array_menus);
            $array_option_menu = array('nom' => 'pied', 'classe' => 'menu_pied');

            if(empty($_SESSION['agence']['alias'])){
                $array_option_menu['no_target_blank'] = array(28);
            }
            include(BASE_URL.WORK_DIR.'/includes/menu.inc.php');
            ?>
        </div>
        <div class="col">
            <h4>Kerbéa</h4>
            <ul class="menu-2">
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/financement-conseils-aides.html">Bureau d'étude Kerbêa</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/presse.html">Espace presse</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/nos-agences">Les agences Kerbêa</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/garanties.html">Les garanties Kerbêa</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/maisons-individuelles-1">Les maisons Kerbêa</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/nos-agences">Nos offres terrains et maisons</a></li>
                <li class="menu-2-item"><a href="#">PTZ+</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/mentions-legales.html">Mentions légales</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/a-propos-des-cookies.html">a-propos-des-cookies</a></li>
            </ul>
        </div>
        <div class="col">
            <h4>Guide pratique</h4>
            <ul class="menu-2">
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/actualites/le-cout-de-construction-d-une-maison-6">Le coût de construction d’une maison</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/actualites/qu-est-ce-qu-une-maison-traditionnelle-7">Qu’est-ce qu’une maison traditionnelle ?</a></li>
                <li class="menu-2-item"><a href="https://www.maisons-kerbea.fr/actualites/avantages-du-ptz-8">Avantage du PTZ</a></li>
                <li class="menu-2-item"><a href="http://www.maisons-kerbea.fr/actualites/la-loi-pinel-41">Loi PINEL</a></li>
            </ul>
        </div>
    </div>
</footer>