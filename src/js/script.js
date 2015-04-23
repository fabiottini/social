
function oscuraTesto(num){
	document.getElementById("txt_"+num).style.background='url("img/sfondoTesto.png") 0, 0, repeat';
}

function visualizzaTesto(num){
	document.getElementById("txt_"+num).style.background ="";
}

var currentPage = new Array();
function scorriPagina(numPagina, label){
	var oldDiv = "#"+label;
	var div = "#"+label+numPagina;
	if( currentPage[label] == undefined ){
		oldDiv += "1";
		currentPage[label] = numPagina;

		jQuery( oldDiv ).fadeOut( "slow", function() {
		    jQuery( div ).fadeIn( "slow", function() {

		  	});
	  	});
	}else{
		if(currentPage[label] != numPagina){
			oldDiv += currentPage[label];

			jQuery( oldDiv ).fadeOut( "slow", function() {
			    jQuery( div ).fadeIn( "slow", function() {

			  	});
		  	});
			currentPage[label] = numPagina;
		}
	}


}
