<?php
	include('../../../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
	include(BASE_URL.WORK_DIR.'/includes/session.php');
	
	$select_all_agences = $PDO -> query('SELECT * FROM agence where actif_agence = 1 ORDER BY nom_agence ASC');
	$count_all_agence = $select_all_agences -> rowCount();
	$select_all_agences = $select_all_agences -> fetchAll();
	
	if(!empty($count_all_agence)):
		
		// On récupère l'url en fonction de l'agence pour éviter les probleme de cross-script en ajax
		$base_url = !empty( $_SESSION[ 'front' ][ 'agence' ] ) ? RESSOURCE_URL_AGENCE : RESSOURCE_URL;
?>
	<script type="text/javascript" src="<?php echo $base_url; ?>/js/jquery.placeholder.js"></script>
	<script type="text/javascript" src="<?php echo $base_url; ?>/js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="<?php echo $base_url; ?>/js/js_validate.js"></script>
	<script type="text/javascript">
		$(function(){
			$('#contact_form').placeholder().submit(function(){
				if($('#contact_form').valid()){
					
					$('input:submit').attr('disabled', 'disabled');
					
					var datas = $('#contact_form').serialize();
					$.post('<?php echo $base_url; ?>/ajax/postContact.ajax.php',datas, function(data){
						if(data.error == 0){
							$('#contact_form').hide();
							$('#msg_err').text(data.message).show();
						}
						else{
							alert(data.message);
						}
						
						$('input:submit').removeAttr('disabled');
						
					}, 'JSON');
				}
				return false;
			});
		});
	</script>
	
	<p class="text mrg_b_s"></p>
	
	<div id="msg_err"></div>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="contact_form">
		<div class="form_item demi"><input type="text" name="prenom" placeholder="Prénom" title="Prénom"/></div>
		<div class="form_item demi last"><input type="text" name="nom" placeholder="Nom" title="Nom"/></div>
		<div class="form_item demi"><input type="text" name="telephone" placeholder="Téléphone" title="Téléphone"/></div>
		<div class="form_item demi last"><input type="text" name="email" placeholder="E-mail" title="E-mail"/></div>
		<div class="both"></div>
		<div class="form_item" id="select">
			<?php
				// Si pas dans zone d'une agence actuelle : liste de toutes les agences
				if(empty( $_SESSION[ 'front' ][ 'agence' ] )):
			?>
				<select name="agence" id="agence">
					<option value="">Choisir une agence</option>
					<?php
						foreach($select_all_agences as $line_agence){
							echo '<option value="'.$line_agence['id_agence'].'">'.$line_agence['nom_agence'].'</option>';
						}
					?>
				</select>
			<?php
				else:
					$id_agence = $nom_agence = '';
					foreach( $select_all_agences as $line_agence ){
						if( $line_agence[ 'alias_agence' ] == $_SESSION[ 'front' ][ 'agence' ] ){
							$id_agence = $line_agence[ 'id_agence' ];
							$nom_agence = $line_agence[ 'nom_agence' ];
							break;
						}
					}
			?>
				<input type="hidden" name="agence"  value="<?php echo $id_agence; ?>"/>
				<div><?php echo $nom_agence; ?></div>
			<?php
				endif;
			?>
		</div>
		<input type="hidden" name="modele" id="modele" value=""/>
		<div class="form_item submit"><input type="submit" value="Envoyer" /></div>
		<div class="both"></div>
	</form>
<?php
	else:
		echo 'Le formulaire de contact est indisponible pour le moment.<br /><br />Vous pouvez :
		<br />- Soit réessayer ultérieurement votre demande de contact et ainsi pouvoir choisir une de nos agences en particulier.
		<br />- Soit envoyer votre demande de contact directement à l\'adresse suivante : <a href="mailto:'.EMAIL_ADMIN.'" class="underline">'.EMAIL_ADMIN.'</a>';
	endif;
?>