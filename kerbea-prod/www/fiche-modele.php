<?php

include('../base_url.php');

include(BASE_URL . '/conf/conf.php');

include(BASE_URL . '/conf/connexion.php');

include(BASE_URL . '/conf/fonctions.php');


include(BASE_URL . WORK_DIR . '/config/langues.cfg.php');

include(BASE_URL . WORK_DIR . '/includes/session.php');

include(BASE_URL . WORK_DIR . '/config/grid.conf.php');


$_SESSION['langue'] = DEFAULT_LANG;

$environnement_agence['menu'] = array('active' => 1);


if (empty($_GET['id'])) {

  header('location:' . RESSOURCE_URL);

  exit;

}


$id_modele = $_GET['id'];


$select_modeles = $PDO->prepare('

SELECT *

FROM modele_maison

JOIN gamme ON gamme.id_gamme = modele_maison.id_gamme

WHERE publier_modele_maison = 1

AND alias_modele_maison = :id_modele_maison

ORDER BY idOrdre_gamme DESC, idOrdre_modele_maison DESC');

$select_modeles->execute(array('id_modele_maison' => $id_modele));


if (!$select_modeles->rowCount()) {

  header('location:' . RESSOURCE_URL . '/modeles.php');

  exit;

}


$line_modele = $select_modeles->fetch();


// Tableau de sélection du menu lié en fonction de la langue (il faut indiquer les 2 (menu et page) pour les pages hybrides

$array_menu_sel_langue = array('fr' => 5);
$array_page_sel_langue = array('fr' => 0);


$id_menu_sel = !empty($array_menu_sel_langue[$_SESSION['langue']]) ? $array_menu_sel_langue[$_SESSION['langue']] : '';

$id_page_sel = !empty($array_page_sel_langue[$_SESSION['langue']]) ? $array_page_sel_langue[$_SESSION['langue']] : '';


if (!empty($id_page_sel)) {

  $result_select_menu_sel = $PDO->query("SELECT *

    FROM dyna_menu

    JOIN dyna_page ON dyna_page.id_page = dyna_menu.id_page

    WHERE dyna_page.id_page = " . $PDO->quote($id_page_sel) . "

    AND publier_menu = '1'");

  $compt_select_menu_sel = $result_select_menu_sel->rowCount();


  if (!empty($compt_select_menu_sel)) {

    $ligne_select_menu_sel = $result_select_menu_sel->fetch();


// On indique le menu selectionné en fonction de la page affichée

    $id_menu_sel = $ligne_select_menu_sel['id_menu'];

  }

}


// Contient la récupération des menus / sous menu / catégorie des commentaires

include(BASE_URL . WORK_DIR . '/includes/sql_commun.php');


// Requete des blocs (pour indiquer les bonnes images etc).

$result_blocs = $PDO->prepare("

    SELECT *

    FROM dyna_page

    LEFT JOIN dyna_bloc ON dyna_page.id_page = dyna_bloc.id_page

    WHERE dyna_page.id_page = :id_page

    AND publier_page = '1'

    ORDER BY dyna_bloc.posy_bloc ASC, dyna_bloc.posx_bloc ASC");

$result_blocs->execute(array(

  'id_page' => $id_page_sel

));

$nb_blocs = $result_blocs->rowCount();

$result_blocs = $result_blocs->fetchAll();


$array_id_bloc_image = array(0);


foreach ($result_blocs as $line_bloc) {

  if ($line_bloc['type_bloc'] == 'image' || $line_bloc['type_bloc'] == 'slider') {

    $array_id_bloc_image[] = $line_bloc['id_bloc'];

  }

}


$qMarks = str_repeat('?,', count($array_id_bloc_image) - 1) . '?';


/* Selection des image et des datas liées data pour toutes les images de cette page */

$select_image_data = $PDO->prepare('

    SELECT image.*,

    image_data.name_imageData, image_data.value_imageData

    FROM image

    LEFT JOIN image_data ON image.id_image = image_data.id_image

    WHERE image.nom_module = "dynapage"

    AND image.id_liaison IN (' . $qMarks . ')

    ORDER BY idOrdre_image DESC

');


$select_image_data->execute($array_id_bloc_image);

$count_image_data = $select_image_data->rowCount();

$select_image_data = $select_image_data->fetchAll();


foreach ($result_blocs as $index_bloc => $line_bloc) {

  if ($line_bloc['type_bloc'] == 'image' || $line_bloc['type_bloc'] == 'slider') {


    $id_image_old = '';

    $index_nb_image_dans_bloc = -1;

    foreach ($select_image_data as $line_image) {


      if ($line_image['id_liaison'] == $line_bloc['id_bloc']) {


        /* Si c'est la première ligne image trouvé pour le bloc en cours */

        if ($id_image_old != $line_image['id_image']) {

          $index_nb_image_dans_bloc++;


          /* Création de la partie "image" dans la line_bloc en cours */

          $result_blocs[$index_bloc]['images'][$index_nb_image_dans_bloc]['nom'] = $line_image['nom_image'];

          $id_image_old = $line_image['id_image'];

        }

        $result_blocs[$index_bloc]['images'][$index_nb_image_dans_bloc]['datas'][$line_image['name_imageData']] = $line_image['value_imageData'];

      }

    }

  }

}


/* ############################################### Référencement par défaut ############################################### */


$seo['upline'] = $seo['title'] = 'Modèle ' . $line_modele['nom_modele_maison'] . ' | Gamme ' . strtoupper($line_modele['nom_gamme']) . ' | Maisons Kerbea';

if ('' != ALIAS_URL) $seo['upline'] = $seo['title'] .= ' ' . $select_infos_agence['nom_agence'];

$seo['keywords'] = $seo['description'] = $seo['baseline'] = '';


/* ######################################################################################################################## */


/* Récupération du référencement */

$select_referencement = $PDO->query('SELECT * FROM seo JOIN dyna_page ON seo.seo_id = dyna_page.seo_id WHERE id_page = ' . $PDO->quote($id_page_sel));

$count_referencement = $select_referencement->rowCount();


// Si une ligne de référencement existe

if (!empty($count_referencement)) {

// On indique qu'on a pas besoin du référencement par défaut

  $referencement_trouve = true;


// remplissage du référencement

  $ligne_referencement = $select_referencement->fetch();

  $seo['title'] = !empty($ligne_referencement['seo_title']) ? $ligne_referencement['seo_title'] : $seo['title'];

  $seo['description'] = !empty($ligne_referencement['seo_description']) ? $ligne_referencement['seo_description'] : $seo['description'];

  $seo['keywords'] = !empty($ligne_referencement['seo_keywords']) ? $ligne_referencement['seo_keywords'] : $seo['keywords'];

  $seo['upline'] = !empty($ligne_referencement['seo_upline']) ? $ligne_referencement['seo_upline'] : $seo['upline'];

  $seo['baseline'] = !empty($ligne_referencement['seo_baseline']) ? $ligne_referencement['seo_baseline'] : $seo['baseline'];

}


/* ############################################### REQUETES DIVERSES ############################################### */


$select_variante_modele = $PDO->prepare('

  SELECT variante_modele.*, gamme.*, modele_maison.*

  FROM variante_modele

  JOIN modele_maison ON variante_modele.id_modele_maison = modele_maison.id_modele_maison

  JOIN gamme ON modele_maison.id_gamme = gamme.id_gamme

  WHERE publier_modele_maison = 1

  AND modele_maison.id_modele_maison = :id_modele_maison');


$select_variante_modele->execute(array('id_modele_maison' => $line_modele['id_modele_maison']));


$count_variante_modele = $select_variante_modele->rowCount();

$select_variante_modele = $select_variante_modele->fetchAll();


if (!empty($_SESSION['agence']['alias'])) {

  $select_image_style_selected = $PDO->prepare('

      SELECT image_modele_maison

      FROM modele_par_agence

      JOIN style_par_modele_maison ON modele_par_agence.id_stylemodmai = style_par_modele_maison.id_stylemodmai

      WHERE id_agence = :id_agence AND modele_par_agence.id_modele_maison = :id_modele_maison

      ORDER BY image_modele_maison ASC

    ');


  $select_image_style_selected->execute(array(

    'id_agence' => $_SESSION['agence']['id'],

    'id_modele_maison' => $line_modele['id_modele_maison']

  ));

  $select_image_style_selected = $select_image_style_selected->fetchAll();

}

?>


<!DOCTYPE HTML>

<html lang="fr-FR">

<head>

  <meta charset="UTF-8"/>

  <title><?php echo $seo['title']; ?></title>

  <meta name="description" content="<?php echo strip_tags($line_modele['description_modele_maison']); ?>"/>

  <meta name="keywords" content=""/>


  <meta property="og:title" content="<?php echo $seo['title']; ?>"/>

  <meta property="og:description" content="<?php echo strip_tags($line_modele['description_modele_maison']); ?>"/>

  <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>"/>

  <meta property="og:image"
        content="<?php echo RESSOURCE_URL . '/medias/modele_maison/galerie/grande/' . $line_modele['image1_modele_maison']; ?>"/>


  <?php include(BASE_URL . WORK_DIR . '/includes/include_css_js_commun.inc.php'); ?>

  <link rel="stylesheet"
        href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel . '&amp;token=' . uniqid(10); ?>"/>

  <link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/form.css"/>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.css" />
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js"></script>

  <script>

    $(document).ready(function () {

      $(".fancybox").fancybox({
        iframe : {
          preload: true
        }
      });



      var slider_realisation = $('#slider_realisation').carouselYnd({

        slider_clip: '#slider_realisation_clip',

        slider_item: '.slider_realisation_item',

        auto: 0,

        autoTempo: 7000,

        callback: function () {

          var slide_actif = slider_realisation.getSlideActif();

          $('#container_vignette .vignette.actif').removeClass('actif');

          $('#container_vignette .vignette:eq(' + slide_actif + ')').addClass('actif');

        }

      });


      $('#container_vignette .vignette').click(function (event) {

        event.preventDefault();


        if (slider_realisation.isAnimationRun() || $(this).hasClass('actif')) {

          return false;

        }


        var slide_index = $(this).index('.vignette');

        $('#container_vignette .vignette.actif').removeClass('actif');

        $(this).addClass('actif');


        $('#slider_realisation_clip').fadeOut(400, function () {

          $(this).css({display: 'block', visibility: 'hidden'});

          slider_realisation.slideMan(slide_index, 'right', 0, function () {

            $('#slider_realisation_clip').css({display: 'none', visibility: 'visible'}).fadeIn(400);

          });

          if (window.matchMedia("(max-width: 767px)").matches) {

            $('html, body').animate({scrollTop: $("#slider_realisation").offset().top}, 1000);

          }


        });


      });


      /* Actions au démarrage */

      $('#container_vignette .vignette:first').addClass('actif');


      var lastLoadInfobox,

        id_o;


      id_o = <?php echo $line_modele['id_modele_maison']; ?>;

      id_a = "<?php echo $alias = !empty($_GET['alias']) ? $_GET['alias'] : '';  ?>";


      // Contact Modele

      $(document).on('click', '.contact, .contactus', function () {

        window.scrollTo(0, 0);


        if (lastLoadInfobox != 'contact') {

          $('.infobox .infobox_area').removeClass('big').addClass('small');


          $('.infobox .infobox_title, .infobox .infobox_content').empty();

          $('.infobox .infobox_title').text('Je souhaite être contacté(e)');

          $('.infobox .infobox_content').load(RESSOURCE_URL + '/ajax/form/contact_modele.inc.php?id=' + id_o + '&alias=' + id_a, function () {

            $('.infobox_content #contact_form #offre').val(id_o);

          });

          $('.infobox').show();


          lastLoadInfobox = 'contact';

        }

        else {

          $('.infobox').show();

        }

        return false;

      });


      //Contact demande fiche

      $(document).on('click', '.brochure', function () {

        window.scrollTo(0, 0);


        if (lastLoadInfobox != 'brochure') {

          $('.infobox .infobox_area').removeClass('big').addClass('small');


          $('.infobox .infobox_title, .infobox .infobox_content').empty();

          $('.infobox .infobox_title').text('Demander la brochure');

          $('.infobox .infobox_content').load(RESSOURCE_URL + '/ajax/form/download.inc.php?id=' + id_o, function () {

            $('.infobox_content #contact_form #offre').val(id_o);

          });

          $('.infobox').show();


          lastLoadInfobox = 'brochure';

        }

        else {

          $('.infobox').show();

        }

        return false;

      });


      // Plan

      modele = {

        id: "<?php echo $select_variante_modele[0]['id_variante_modele']; ?>",

        nom: "<?php echo $select_variante_modele[0]['nom_modele_maison']; ?>"

      };


      gamme = {

        nom: "<?php echo $select_variante_modele[0]['nom_gamme']; ?>"

      };


      $(document).on('click', '.variante', function () {

        window.scrollTo(0, 0);


        var idVariante = $(this).attr('data-id');


        if (lastLoadInfobox != 'plan_' + idVariante) {

          $('.infobox .infobox_area').removeClass('small').addClass('big');


          $.getJSON(RESSOURCE_URL + '/ajax/getPlan.ajax.php', {"variante": idVariante}, function (data) {

            if (data.error == 0) {

              var chambre = data.variante.nombreChambre_variante_modele > 1 ? 'chambres' : 'chambre',

                surface = parseInt(data.variante.surface_variante_modele, 10).toFixed(2).replace(',', '.'),

                content = [

                  '<div class="plan"><img src="' + RESSOURCE_URL + '/medias/variante_modele/plans/moyenne/' + data.variante.plan1_variante_modele + '" alt="" /></div>',

                  '<div class="infos">',

                  '<div class="variante_resume mrg_b_m">',

                  '<span class="chambre">' + data.variante.nombreChambre_variante_modele + ' ' + chambre + '</span> ',

                  '<span class="surface">' + surface + ' m²</span>',

                  '</div>',

                  '<div class="description">' + data.variante.description_variante_modele + '</div>',

                  '</div>',

                  '<div class="both"></div>',

                  '<div class=" mrg_t_l"><a href="" class="contact">Je souhaite être contacté(e)<img class="mrg_l_s" src="' + RESSOURCE_URL + '/images/pictos/fl-r-right.png" alt=""/></a></div>'

                ];

              content = content.join('');


              $('.infobox .infobox_title, .infobox .infobox_content').empty();

              $('.infobox .infobox_area .infobox_title').html('<strong>' + modele.nom + ' ' + data.variante.nombreChambre_variante_modele + ' ' + chambre + '</strong> de la gamme ' + gamme.nom);

              $('.infobox .infobox_area .infobox_content').html(content);

              $('.infobox').show();

            }

            else {

              alert(data.message);

            }

          });

          lastLoadInfobox = 'plan_' + idVariante;

        }

        else {

          $('.infobox').show();

        }


        return false;

      });


      $(' .infobox .black_mask, .infobox .btn_close').click(function () {

        $('.infobox').hide();

      });


    });

  </script>


</head>

<body>

<div class="infobox">

  <div class="black_mask"></div>

  <div class="infobox_area">

    <div class="infobox_title"></div>

    <div class="infobox_content"></div>

    <div class="btn_close"></div>

  </div>

</div>

<?php include(BASE_URL . WORK_DIR . '/includes/header.inc.php'); ?>

<div id="container-content">

  <?php if (!empty($environnement_agence['background_image'])): ?>

    <div class="img-back-agence"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>

  <?php endif; ?>


  <div class="content-hover">

    <div class="site-width relative">

      <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL;
        if (!empty($_SESSION['agence']['alias'])) {
          echo '/agence-' . $_GET['alias'];
        } ?>">Maisons Kerbéa<?php if (!empty($_SESSION['agence']['alias'])) {
            echo ' ' . $_SESSION['agence']['alias'];
          } ?></a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><a
          href="<?php echo RESSOURCE_URL;
          if (!empty($_SESSION['agence']['alias'])) {
            echo '/agence-' . $_GET['alias'];
          } ?>/maisons-individuelles-<?php echo $line_modele['id_gamme']; ?>">Modèles de maisons -
          Gamme <?php echo strtoupper($line_modele['nom_gamme']); ?></a><img
          src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png"
          alt=""/><?php echo $line_modele['nom_modele_maison']; ?></div>


      <h1 class="black-title">
        Gamme <?php echo strtoupper($line_modele['nom_gamme']) . ' - ' . $line_modele['nom_modele_maison']; ?></h1>

      <?php include(BASE_URL . WORK_DIR . '/includes/share.php'); ?>

      <div class="fiche">

        <div class="medias">

          <div class="part-left">

            <div id="slider_realisation">

              <div id="slider_realisation_clip">

                <?php

                for ($i = 1; $i <= 8; $i++) {

                  if (!empty($line_modele['image' . $i . '_modele_maison'])) {

                    if (isset($select_image_style_selected) && count($select_image_style_selected)) {

                      $image_not_found = true;

                      foreach ($select_image_style_selected as $image_selected) {

                        if ($image_selected['image_modele_maison'] == $i) {

                          $image_not_found = false;

                          break;

                        }

                      }

                      if ($image_not_found) {

                        continue;

                      }

                    }

                    echo '<div class="slider_realisation_item">

                                                    <a class="" data-lightbox="' . $line_modele['image' . $i . '_modele_maison'] . '" href="' . RESSOURCE_URL . '/medias/modele_maison/galerie/grande/' . $line_modele['image' . $i . '_modele_maison'] . '">

                                                        <img src="' . RESSOURCE_URL . '/medias/modele_maison/galerie/moyenne-high/' . $line_modele['image' . $i . '_modele_maison'] . '" alt=""/>

                                                    </a>

                                                </div>';

                  }

                }

                ?>

              </div>

            </div>


            <!--                                --><?php //if(!empty($line_modele['image1_modele_maison'])): ?>

            <!--                                    <img src="-->
            <?php //echo RESSOURCE_URL.'/medias/modele_maison/galerie/moyenne-high/'.$line_modele['image1_modele_maison']; ?><!--" alt=""/>-->

            <!--                                --><?php //endif; ?>

          </div>

          <div class="part-right">

            <a href="#" class="contact"><span>Je souhaite être contacté(e)</span><img class="mrg_l_s"
                                                                                      src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-right.png"
                                                                                      alt=""/></a>

            <div id="container_vignette" class="no-mobile">

              <?php

              for ($i = 1; $i <= 8; $i++) {

                if (!empty($line_modele['image' . $i . '_modele_maison'])) {

                  if (isset($select_image_style_selected) && count($select_image_style_selected)) {

                    $image_not_found = true;

                    foreach ($select_image_style_selected as $image_selected) {

                      if ($image_selected['image_modele_maison'] == $i) {

                        $image_not_found = false;

                        break;

                      }

                    }

                    if ($image_not_found) {

                      continue;

                    }

                  }

                  echo '<div class="vignette galerie"><img src="' . RESSOURCE_URL . '/medias/modele_maison/galerie/vignette/' . $line_modele['image' . $i . '_modele_maison'] . '" alt=""/></div>';

                }

              }

              ?>

            </div>

          </div>

          <div class="both"></div>
          <?php if(checkVisiteVirtualAvaible($line_modele['alias_modele_maison'])): ?>
          <div class="part-left ">
            <a href="<?php echo RESSOURCE_URL; ?>/visite360/maisons/<?php echo $line_modele['alias_modele_maison'] ?>/index.html"  <?php if($device_detect->isMobile()):?> target="_blank" class="visite360" <?php else: ?>data-fancybox-type="iframe"  class="fancybox visite360" <?php endif; ?>>
              <img class="cardboard" src="<?php echo RESSOURCE_URL ?>/images/pictos/cardboard.png" alt=""/>
              <span>VISITEZ CETTE MAISON EN RÉALITÉ VIRTUELLE !</span>
              <img class="visite360picto" src="<?php echo RESSOURCE_URL ?>/images/pictos/visite360.png" alt=""/>
            </a>
          </div>
          <?php endif; ?>
          <div class="both"></div>
        </div>

        <div class="description">

          <div class="part-left">

            <div class="text"><?php echo $line_modele['description_modele_maison']; ?></div>

            <div class="variantes">

              <?php foreach ($select_variante_modele as $variante): ?>

                <div class="variante" data-id="<?php echo $variante['id_variante_modele']; ?>">

                  <span
                    class="chambre"><?php echo $variante['nombreChambre_variante_modele']; ?><?php echo plurialDetect('s', 'chambre', $variante['nombreChambre_variante_modele']); ?></span><span
                    class="surface"><?php echo $variante['surface_variante_modele']; ?>
                    m²</span><?php if (!empty($variante['plan1_variante_modele'])) {
                    echo '<span class="plan"><img src="' . RESSOURCE_URL . '/images/pictos/plan.jpg" alt=""/></span>';
                  } ?>

                </div>

              <?php endforeach; ?>

            </div>

            <div class="relative no-mobile">

              <a href="#" class="contact"><span>Je souhaite être contacté(e)</span><img class="mrg_l_s"
                                                                                        src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-right.png"
                                                                                        alt=""/></a>

              <div class="bb"></div>

            </div>

          </div>

          <div class="part-right">


            <?php if (!empty($line_modele['fiche_modele_maison'])): ?>

              <a class="brochure" href="#">Demander la brochure</a>

              <?php

            endif;

            if (!empty($select_infos_agence)): ?>

              <div class="align_c">

                <?php

                echo '<strong>MAISONS KERBÉA ' . mb_strtoupper($select_infos_agence['nom_agence'], 'utf8') . '</strong>' .

                  '<br /><br />' . $select_infos_agence['adresse_agence'] .

                  '<br />' . $select_infos_agence['codePostal_agence'] . ' ' . $select_infos_agence['ville_agence'] .

                  '<br /><br />Tél : ' . $select_infos_agence['telephone_agence'];


                if (!empty($line_offre['fax_agence'])) {

                  echo '<br />Fax : ' . $select_infos_agence['fax_agence'];

                }

                ?>

                <br/><br/><a href="#" class="contactus gnl_red">Contactez-nous</a>

              </div>

            <?php endif; ?>

          </div>

          <div class="both"></div>

        </div>

      </div>

    </div>

  </div>

</div>


<?php include(BASE_URL . WORK_DIR . '/includes/footer.inc.php'); ?>

</body>

</html>
