<div id="container-header" class="container<?php if($_SESSION['agence']['session']){ echo ' agence';} ?>">
    <header class="clearfix">
        <a class="responsive-menu-toggle"></a>
        <div class="top-menu">
            <?php
            $is_agence = !empty($_SESSION['agence']['alias']);
            $contact_url = $is_agence ? RESSOURCE_URL.'/agence-'.$_SESSION['agence']['alias'].'/contact' : RESSOURCE_URL.'/contact';
            $phone_number = $select_infos_agence['telephone_agence'];
            ?>
            <ul class="menu-1">
                <li><a href="<?php echo $contact_url; ?>">Contact</a></li>
                <li><a href="https://www.maisons-kerbea.fr/le-club-k.html" target="_blank">Le Club K</a></li>
                <?php if($is_agence){ ?><li class="menu-1-item"><a href="http://www.maisons-kerbea.fr">Site national</a></li><?php } ?>
                <li><a href="http://www.maisons-kerbea.fr/devenir-franchise.php" target="_blank">Devenir franchisé</a></li>
                <?php if($is_agence){ ?><li class="phone"><i class="fa fa-mobile"></i><a href="tel:<?php echo str_replace(' ', '', $phone_number); ?>" class="number"><?php echo $phone_number; ?></a></li><?php } ?>
            </ul>
        </div>
        <div class="logo"><a href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>"></a></div>
        <div class="slogan"></div>
<!--        --><?php //if(!$_SESSION['agence']['session']): ?>
<!--        <div class="search toggle">-->
<!--            <form action="--><?php //echo RESSOURCE_URL; ?><!--/liste-agence.php" method="post" id="search_agence_postal" class="right mrg_r_s light">-->
<!--                <input type="text" name="searchPlaceAgence" id="searchPlaceAgence" class="" value="--><?php //echo !empty($_POST['searchPlaceAgence']) ? $_POST['searchPlaceAgence'] : ''; ?><!--" placeholder="Votre code postal" required/>-->
<!--                <input type="submit" value="" />-->
<!--            </form>-->
<!--        </div>-->
<!--        --><?php //endif; ?>

<!--        --><?php //if($_SESSION['agence']['session']): ?>
<!--        <div id="agence-header-infos">-->
<!--            <div class="name"><span class="intro-name">Maisons Kerbéa</span> <strong>--><?php //echo $select_infos_agence['nom_agence']; ?><!--</strong></div><div class="zipc">--><?php //echo substr($select_infos_agence['codePostal_agence'], 0, 2); ?><!--</div>-->
<!--        </div>-->
<!--        <div id="phone-black" class="header-phone">-->
<!--            <a href="tel:--><?php //echo str_replace(' ','',$select_infos_agence['telephone_agence']); ?><!--" target="_blank">-->
<!--                <span class="num-tel">--><?php //echo $select_infos_agence['telephone_agence']; ?><!--</span>-->
<!--            </a>-->
<!--        </div>-->
<!--        --><?php //endif; ?>
        <nav class="main-menu">
        <?php
        if(!$_SESSION['agence']['session']):
            $array_option_menu = array(
                'nom' => 'principal',
                'btn-home' => array(
                    'content' => 'Accueil',
                    'class' => 'menu-home'
                )
            );
            include(BASE_URL.WORK_DIR.'/includes/menu.inc.php');
        else:
            include(BASE_URL.WORK_DIR.'/includes/menuAgence.inc.php');
        endif;
        ?>
        <?php
            if($_SESSION['agence']['session']):
                $link_contact = '/agence-'.$_SESSION['agence']['alias'].'/contact';
            else:
                $link_contact = '/contact';
            endif;
        ?>
        </nav>
    </header>
</div>