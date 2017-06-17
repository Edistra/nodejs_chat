<?php


/**
 * Verifie si une maison est disponible en mode virtuel
 * @param $alias Alias de la maison
 */
function checkVisiteVirtualAvaible($alias) {
  return file_exists('visite360/maisons/'.$alias.'/index.html');
}

function getVisiteVirtualIndex($alias) {
  return  file_get_contents('../visite360/maisons/'.$alias.'/index.html');
}
function choosePronom($pronom, $word)
{

  $is_voyelle = in_array(mb_strtolower(substr($word, 0, 1), 'utf8'), array('a', 'o', 'e', 'i', 'y', 'u'));

  if ('de' == $pronom) {

    return $is_voyelle ? 'd\'' : $pronom;

  } elseif ('le' == $pronom) {

    return $is_voyelle ? 'l\'' : $pronom;

  }

}


function plurialDetect($plurial_type, $word, $num)
{

  $is_plurial = $num > 1;


  if ('s' == $plurial_type) {

    return $is_plurial ? $word . 's' : $word;

  } elseif ('aux' == $plurial_type) {

    return $plurial_type ? $word . 'ux' : $word;

  }

}


function random_pass($car)
{

  $string = "";

  $chaine = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

  srand((double)microtime() * 1000000);

  for ($i = 0; $i < $car; $i++) {

    $string .= $chaine[rand() % strlen($chaine)];

  }

  return $string;

}


function verif_email($email)

{

  $atom = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';      // Caractères autorisés avant l'arobase

  $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)';    // Caractères autorisés après l'arobase (nom de domaine)

  $regex = '/^' . $atom . '+' .              // Une ou plusieurs fois les caractères autorisés avant l'arobase

    '(\.' . $atom . '+)*' .                    // Suivis par zéro point ou plus

    // Séparés par des caractères autorisés avant l'arobase

    '@' .                                      // Suivis d'un arobase

    '(' . $domain . '{1,63}\.)+' .            // Suivis par 1 à 63 caractères autorisés pour le nom de domaine

    // Séparés par des points

    $domain . '{2,63}$/i';                    // Suivi de 2 à 63 caractères autorisés pour le nom de domaine


  if (preg_match($regex, $email)) return true;

  else return false;

}


function remplace_accents($chaine)

{

  $string = strtr($chaine,

    utf8_decode(

      "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ%/?"),

    "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn---");

  return $string;

}


function parser_html($chaine, $nbres)

{

  // UTF8 !

  mb_internal_encoding('UTF-8');

  $chaine = html_entity_decode($chaine); // Pour transformer les accents

  $nbchar = strlen($chaine); // Nombres total de caractère dans $chaine avec balises HTML

  $res = ''; // Chaîne finale à retourner

  $i = 0; // Pour la boucle WHILE

  $cntchar = 0; // Initialise le compteur de caractère

  $openbal = 0; // Initialise la variable qui détermine si la balise en cours de lecture est ouverte ou non

  $inspan = 0; // Initialise la variable qui détermine si la balise en cours de lecture est un span


  while ($i != $nbchar) {

    $char = mb_substr($chaine, $i, 1);


    // Lettre hors balise

    if ($openbal == 0 AND $char != '<' AND $cntchar < $nbres) {

      $res .= $char;

      $cntchar++;

    }

    // Lettre dans balise

    if ($openbal == 1 AND $char != '>' AND $inspan == 0) {

      $char1 = mb_substr($chaine, $i + 1, 1);

      $char2 = mb_substr($chaine, $i + 2, 1);

      $char3 = mb_substr($chaine, $i + 3, 1);

      $bal = $char . $char1 . $char2 . $char3;


      // On insère pas les caractères balises s'il s'agit s'un <span> ou </span>

      if ($bal == 'span' OR $bal == '/spa') {

        $res = mb_substr($res, 0, -1);

        $inspan = 1;

      } else $res .= $char;

    }

    // Dernière lettre hors balise

    if ($openbal == 0 AND $char != '<' AND $cntchar == $nbres) {

      $res .= '...';

      $cntchar++;

    }

    // Ouverture de balise

    if ($char == '<' AND $openbal == 0) {

      $res .= $char;

      $openbal = 1;

    }

    // Fermeture de balise

    if ($char == '>' AND $openbal == 1) {

      if ($inspan == 0) $res .= $char;

      if ($inspan == 1) $inspan = 0;

      $openbal = 0;

    }


    $i++;

  }

  return $res;

}


function rewrite_nom($nom)
{

  $remplace = array("²", "(", ")", " ", "'", '"', ",", ".", "?", "!", "’", ":", "&", '>', '<', '*');

  $preg_search = "#(-)+#";


  return $nom = preg_replace($preg_search, "-", remplace_accents(str_replace($remplace, "-", strtolower(utf8_decode($nom)))));

  // return $nom = remplace_accents(strtolower(str_replace($remplace,"-",$nom)));

}


function log_file($nom_fic, $msg)
{

  $ip = $_SERVER['REMOTE_ADDR'];

  $file = fopen("$nom_fic", "a");

  fwrite($file, "[" . date("d/m/y G:i:s", time()) . "] -- $ip -- $msg\r\n");

  fclose($file);

}


function cutLongWords($str, $length = '60', $separation = ' ')
{

  return preg_replace('/([^ ]{' . $length . '})/si', '\\1' . $separation, $str);

}


function make_seed()
{

  list($usec, $sec) = explode(' ', microtime());

  return (float)$sec + ((float)$usec * 100000);

}


// Fonction de transformation d'une date "normale" en date mysql

function date_transform($date, $separateur = "/")
{

  $date_mysql = implode($separateur, array_reverse(explode($separateur, $date)));

  return $date_mysql;

}


function debug($variable)
{

  echo '<pre>';

  print_r($variable);

  echo '</pre>';

}


/**
 *
 * Fonction de création automatique d'un lien
 *
 * @content = contenu à traiter cible du lien
 *
 * @url (string) url du lien
 *
 * @class (string) class du <a>
 *
 * @target (boolean) = cible du lien
 *
 * @title (string) = titre du <a>
 */

function createLink($content, $url = '', $class = '', $target = false, $title = '')
{

  $target_blank = !empty($target) ? ' target="_blank"' : '';

  $class = !empty($class) ? ' class="' . $class . '"' : '';

  $sortie = !empty($url) ? '<a href="' . $url . '"' . $target_blank . $class . '>' . $content . '</a>' : $content;

  return $sortie;

}


/**
 * Fonction de traduction
 * le tableau l18n contient les traductions des langues chargées une première fois (si 5 traductions à faire dans une même page, le fichier include de traduction n'est appelé qu'une seule fois)
 *
 * @text (string) = text à traduire
 *
 * @file (string) = fichier source de traduction
 *
 * @lang (string) = langue cible
 *
 * @path (string) = chemin vers le dossier principal de traduction
 */


$l18n = array();


function t($text, $file = "site", $lang = '', $path = null)
{

  global $l18n;


  if (empty($lang)) {

    $lang = isset($_SESSION['langue']) ? $_SESSION['langue'] : $lang = DEFAULT_LANG;

  }


  if ($lang != DEFAULT_LANG) {

    if ($path == null) {

      $path = BASE_URL . WORK_DIR . '/trad';

    }


    if (file_exists($path . '/' . $lang . '/' . $file . '.php') && !isset($l18n[$lang])) {

      $l18n[$lang] = include($path . '/' . $lang . '/' . $file . '.php');

    }


    if (!empty($l18n[$lang][$text])) {

      return $l18n[$lang][$text];

    } elseif (DEBUG_TRANSLATE) {

      $debug = debug_backtrace();

      $log = fopen(BASE_URL . '/conf/logs/translate', 'a');

      fwrite($log, "Trad manquante : $lang # $text # " . $_SERVER['REQUEST_URI'] . " line " . $debug[0]['line'] . "\n");

      fclose($log);

    }

  }

  return $text;

}
