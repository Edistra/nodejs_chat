<?php

include('../../base_url.php');

include(BASE_URL . '/conf/conf.php');

include(BASE_URL . '/conf/connexion.php');

include(BASE_URL . '/conf/fonctions.php');

$id_modele = $_GET['id'];

$select_modeles = $PDO->prepare('
SELECT *
FROM modele_maison
WHERE publier_modele_maison = 1 AND id_modele_maison = :id_modele_maison');

$select_modeles->execute(array('id_modele_maison' => $id_modele));

if (!$select_modeles->rowCount()) {
  echo 'La visite virtuel demandé est introuvable';
  exit();
}

$line_modele = $select_modeles->fetch();

$nomMaison = $line_modele['alias_modele_maison'];

if(!checkVisiteVirtualAvaible($nomMaison)) {
  echo "La visite virtuel demandé n'est pas disponible pour cette maison" ;
  exit();
}

echo getVisiteVirtualIndex($nomMaison);


