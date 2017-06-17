$(document).ready(function(){
	$('div.form_item').append('<div class="error_area"></div>');
	
	jQuery.validator.setDefaults({
		errorPlacement: function(error, element) {
			$(element).parents('div.form_item').find('.error_area').html(error);
		},
		highlight : function(element, errorClass, validClass){
			$(element).parents('div:first').addClass('error_parent').removeClass('valid_parent');
			$(element).parents('div.form_item').find('.error_area').show();
		},
		unhighlight: function(element, errorClass, validClass) {
			$(element).parents('div:first').removeClass('error_parent').addClass('valid_parent');
			$(element).parents('div.form_item').find('.error_area').hide();
		}
	});
	
	jQuery.validator.addMethod("notEqual", function(value, element, param) {
		return this.optional(element) || value != param;
	}, "Veuillez indiquer une valeur");
	
	/*
	jQuery.validator.addMethod("verifPseudo", function(value, element,param) {
		var result;
		$.ajax({
			async : false,
			data : {pseudo : value},
			url: 'ajax/checkPseudo.php',
			success: function(data) {
				result = data;
			}
		});
		if(result == 1){
			$('.error_pseudo').html('Ce pseudo est disponible');
		}
		else{
			$('.error_pseudo').html('Ce pseudo est indisponible');
		}
		return result==param;
	}, "Veuillez indiquer une valeur");
	*/
	jQuery.validator.addMethod("frenchDate", function(value, element) {
		return value.match(/^(\d){2,2}\/(\d){2,2}\/(\d){4,4}$/);
	},"Veuillez indiquer une date au format dd/mm/yyyy.");
	
	$.validator.addMethod("check_date_of_birth", function(value, element) {
		var naissance = $("#date_naissance").val();
		naissance = naissance.split('/');
		
		var day = naissance[0];
		var month = naissance[1];
		var year = naissance[2];
		var age =  18;

		var mydate = new Date();
		mydate.setFullYear(year, month-1, day);

		var currdate = new Date();
		currdate.setFullYear(currdate.getFullYear() - age);

		return currdate >= mydate;
		
	}, "Vous devez être majeur");
	
	$.validator.addMethod('regexp', function(value, element, param) {
		return this.optional(element) || value.match(param);
	},
	'Le contenu est invalide');
	
// règle de validation de la partie propriétaire
	$('#contact_form').validate({
		rules :{
			prenom :{
				required : true
			},
			nom :{
				required : true
			},
			message :{
				required : true
			},
			agence :{
				required : true
			},
			email :{
				required : true,
				email : true
			},
			telephone :{
				required : true,
				regexp : /^(\d\d(\s?)){4}(\d\d)$/
			}
		},
		messages:{
			prenom :{
				required : 'Ce champ est obligatoire'
			},
			nom :{
				required : 'Ce champ est obligatoire'
			},
			message :{
				required : 'Ce champ est obligatoire'
			},
			agence :{
				required : 'Vous devez sélectionner une agence'
			},
			email :{
				required : 'Ce champ est obligatoire',
				email : 'L\'email doit être valide'
			},
			telephone :{
				required : 'Ce champ est obligatoire',
				regexp : 'Le numéro de téléphone doit être valide.'
			}
		}
	});
});