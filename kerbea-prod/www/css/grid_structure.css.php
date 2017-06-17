<?php

include('../../base_url.php');
include(BASE_URL.'/conf/conf.php');
include(BASE_URL.'/conf/connexion.php');
include(BASE_URL.'/conf/fonctions.php');
include(BASE_URL.WORK_DIR.'/config/grid.conf.php');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
header("Content-type: text/css");

$array_work = array();

echo '
	#dynagrid{
		position:relative;
	}

	@media screen and (min-width: 1000px) {
		.zone_dyna{
			width:'.( $grid_conf[ 'pas' ][ 'x' ][ 'ecran' ]['value'] * $grid_conf[ 'col' ] ).'px;
            margin:0 auto;
		}
	}
	@media screen and (max-width: 999px) and (min-width: 768px) {
		.zone_dyna {
			width:'.( $grid_conf[ 'pas' ][ 'x' ][ 'tablette' ][ 'value' ] * $grid_conf[ 'col' ] ).'px;
            margin:0 auto;
		}
	}
	@media screen and (max-width: 767px) {
		
	}
	.full_height .bloc_inner{
		position:relative;
		height:100%;
	}';

if( empty($_GET[ 'id' ] )){
    exit;
}

echo '.bloc_container{
		position:absolute;
		overflow:hidden;
	}';

// Requete des blocs (pour indiquer les bonnes images etc).
$result_blocs = $PDO->prepare("SELECT
		dyna_bloc.id_bloc,
		dyna_bloc.width_bloc width,
		dyna_bloc.height_bloc height,
		dyna_bloc.posx_bloc posx,
		dyna_bloc.posy_bloc posy,
		dyna_bloc.marginInner_bloc margin,
		dyna_bloc.styleInner_bloc style
	
		FROM dyna_page
		JOIN dyna_bloc ON dyna_page.id_page = dyna_bloc.id_page
		WHERE dyna_page.id_page = :id_page
		AND publier_page = '1'
		ORDER BY dyna_bloc.posy_bloc ASC, dyna_bloc.posx_bloc ASC ");
$result_blocs -> execute(array(
    'id_page' => $_GET['id']
));
$count_blocs = $result_blocs -> rowCount();

if(empty( $result_blocs )){
    exit;
}

$result_blocs = $result_blocs -> fetchAll();

/* récupération  */

$maxHeight = array('ecranXl' => 0, 'ecran' => 0, 'tablette' => 0, 'mobile' => 0);
foreach( $result_blocs as $line_bloc ){
    foreach( $grid_conf[ 'variables' ] as $name_var => $variable ){
        $decode_temp = json_decode( $line_bloc[ $name_var] );
        $array_work[ $name_var ]['ecran'][] = $decode_temp -> ecran;
        $array_work[ $name_var ]['tablette'][] = $decode_temp -> tablette;
        $array_work[ $name_var ]['mobile'][] = $decode_temp -> mobile;
    }

    /* J'en profite pour calculer la hauteur maximale pour chaque mode d'affichage */
    $heightBlocDecode = json_decode( $line_bloc['height'] );
    $posyBlocDecode = json_decode( $line_bloc['posy'] );

    foreach( $maxHeight as $modeMaxHeight => $value ){
        $blocHeightPos_mode = $heightBlocDecode -> $modeMaxHeight + $posyBlocDecode -> $modeMaxHeight;
        if( $blocHeightPos_mode > $value){
            $maxHeight[ $modeMaxHeight ] = $blocHeightPos_mode;
        }
    }
}
if(!empty( $count_blocs )){
    foreach( $grid_conf[ 'variables' ] as $name_var => $variable ){
        $array_work[ $name_var ]['ecran'] = array_unique( $array_work[ $name_var ]['ecran'] );
        $array_work[ $name_var ]['tablette'] = array_unique( $array_work[ $name_var ]['tablette'] );
        $array_work[ $name_var ]['mobile'] = array_unique( $array_work[ $name_var ]['mobile'] );
    }
}

/* Ecran */
echo '@media screen and (min-width: 1000px) {
		.zone_dyna #dynagrid{
			width:'.( $grid_conf[ 'pas' ][ 'x' ][ 'ecran' ]['value'] * $grid_conf[ 'col' ] ).'px;
			height:'.( $grid_conf[ 'pas' ][ 'y' ][ 'ecran' ]['value'] * $maxHeight[ 'ecran' ] ).'px;
		}';

if(!empty( $count_blocs )){
    /* ecriture des styles css pour écran et en largeur, hauteur, position x et y */
    foreach( $grid_conf[ 'variables' ] as $name_var => $variable ){
        foreach( $array_work[ $name_var ][ 'ecran' ] as $value){
            echo '.b'.substr($variable[ 0 ],0,1).'e_'.$value.'{
						'.$variable[ 0 ].' : '.( $value * $grid_conf[ 'pas' ][ $variable[ 1 ]][ 'ecran' ]['value'] ).$grid_conf[ 'pas' ][ $variable[ 1 ]][ 'ecran' ]['unite'].';
					}';
        }
    }

    /* ecriture de la classe spécifique au bloc en cours : style perso et margin */
    foreach( $result_blocs as $line_bloc ){
        $margin = json_decode($line_bloc[ 'margin' ]);
        $style = json_decode($line_bloc[ 'style' ]);
        echo '.bstyle_'.$line_bloc[ 'id_bloc' ].' .bloc_inner{
					margin:'.$margin -> ecran.';
					'.$style -> ecran.'
				}';
    }
}
echo '}';

/* Tablette */
echo '@media screen and (max-width: 999px) and (min-width: 768px) {
		.zone_dyna #dynagrid{
			width:'.( $grid_conf[ 'pas' ][ 'x' ][ 'tablette' ][ 'value' ] * $grid_conf[ 'col' ] ).'px;
			height:'.( $grid_conf[ 'pas' ][ 'y' ][ 'tablette' ]['value'] * $maxHeight[ 'tablette' ] ).'px;
		}';

if(!empty( $count_blocs )){

    /* ecriture des styles css pour tablette et en largeur, hauteur, position x et y */
    foreach( $grid_conf[ 'variables' ] as $name_var => $variable ){
        foreach( $array_work[ $name_var ][ 'tablette' ] as $value){
            echo '.b'.substr($variable[ 0 ],0,1).'t_'.$value.'{
						'.$variable[ 0 ].' : '.( $value * $grid_conf[ 'pas' ][ $variable[ 1 ]][ 'tablette' ]['value'] ).$grid_conf[ 'pas' ][ $variable[ 1 ]][ 'tablette' ]['unite'].';
					}';
        }
    }

    /* ecriture de la classe spécifique au bloc en cours : style perso et margin */
    foreach( $result_blocs as $line_bloc ){
        $margin = json_decode($line_bloc[ 'margin' ]);
        $style = json_decode($line_bloc[ 'style' ]);
        echo '.bstyle_'.$line_bloc[ 'id_bloc' ].' .bloc_inner{
					margin:'.$margin -> tablette.';
					'.$style -> tablette.'
				}';
    }
}
echo '}';

/* mobile */
echo '@media screen and (max-width: 767px) {
		.zone_dyna #dynagrid{
			width:100%;
			height:'.( $grid_conf[ 'pas' ][ 'y' ][ 'mobile' ]['value'] * $maxHeight[ 'mobile' ] ).'px;
		}';

if(!empty( $count_blocs )){
    /* ecriture des styles css pour mobile et en largeur, hauteur, position x et y */
    foreach( $grid_conf[ 'variables' ] as $name_var => $variable ){
        foreach( $array_work[ $name_var ][ 'mobile' ] as $value){
            echo '.b'.substr($variable[ 0 ],0,1).'m_'.$value.'{
					'.$variable[ 0 ].' : '.( $value * $grid_conf[ 'pas' ][ $variable[ 1 ]][ 'mobile' ]['value'] ).$grid_conf[ 'pas' ][ $variable[ 1 ]][ 'mobile' ]['unite'].';
				}';
        }
    }

    /* ecriture de la classe spécifique au bloc en cours : style perso et margin */
    foreach( $result_blocs as $line_bloc ){
        $margin = json_decode($line_bloc[ 'margin' ]);
        $style = json_decode($line_bloc[ 'style' ]);
        echo '.bstyle_'.$line_bloc[ 'id_bloc' ].' .bloc_inner{
				margin:'.$margin -> mobile.';
				'.$style -> mobile.'
			}';
    }
}

echo '}';

?>
