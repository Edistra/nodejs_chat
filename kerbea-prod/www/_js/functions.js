// Remplace toutes les occurences d'une chaine
function replaceAll(str, search, repl) {
    while (str.indexOf(search) != -1){
		str = str.replace(search, repl);
	}
    return str;
}
    // Remplace les caractères accentués (+ espace)
function remplace_accents(str) {
    var norm = new Array("À","Á","Â","Ã","Ä","Å","à","á","â","ã","ä","å","Ò","Ó","Ô","Õ","Ö","Ø","ò","ó","ô","õ","ö","ø","È","É","Ê","Ë","è","é","ê","ë","Ç","ç","Ì","Í","Î","Ï","ì","í","î","ï","Ù","Ú","Û","Ü","ù","ú","û","ü","ÿ","Ñ","ñ","%","/","?");
    var spec = new Array("a","a","a","a","a","a","a","a","a","a","a","a","o","o","o","o","o","o","o","o","o","o","o","o","e","e","e","e","e","e","e","e","c","c","i","i","i","i","i","i","i","i","u","u","u","u","u","u","u","u","y","n","n","-","-","-");
    for (var i = 0; i < spec.length; i++){
		str = replaceAll(str, norm[i], spec[i]);
	}
    return str;
}

function rewrite_nom(my_string){
	var new_string = '';
	new_string = remplace_accents(my_string);
	new_string = new_string.toLowerCase();
	new_string = new_string.replace( /[^a-z0-9]/g,"-");
	new_string = new_string.replace(/(\-)+/g,'-');
	
	return new_string;
}