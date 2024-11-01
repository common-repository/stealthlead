function checkallfields(){
		if(document.manage_offer.offer_name.value == ""){	
			alert("Offer Name field can not be left blank.");
			document.manage_offer.offer_name.focus();
			return false;
		}
		if(document.manage_offer.offer_start.value == ""){	
			alert("Offer Start Date field can not be left blank.");
			document.manage_offer.offer_start.focus();
			return false;
		}else if(document.manage_offer.offer_start.value == "0000-00-00"){
			alert("Please enter the correct Offer Start Date.");
			document.manage_offer.offer_start.focus();
			return false;		
		}
		if(document.manage_offer.offer_end.value == ""){	
			alert("Offer End Date field can not be left blank.");
			document.manage_offer.offer_end.focus();
			return false;
		}else if(document.manage_offer.offer_end.value == "0000-00-00"){
			alert("Please enter the correct Offer End Date.");
			document.manage_offer.offer_end.focus();
			return false;		
		}	
		
		var StartDate= document.manage_offer.offer_start.value;
	    var EndDate= document.manage_offer.offer_end.value;
	    var eDate = new Date(EndDate);
	    var sDate = new Date(StartDate);
	   if(StartDate!= '' && StartDate!= '' && sDate> eDate){
			alert("Please ensure that the End Date is greater than or equal to the Start Date.");
			document.manage_offer.offer_end.focus();
			return false;
		}
		
		if(document.manage_offer.offer_url.value != ""){
			var url =  document.manage_offer.offer_url.value;
			var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
			if(!RegExp.test(url)){
				alert("Please enter the Offer URL in Correct Format.");
				document.manage_offer.offer_url.focus();
				return false;
			}
		}	
				
	}
	
	
function delete_page(name, id){
	if(confirm("Are you sure you want to delete this Offer \""+ name +"\" ?")){
		window.location='admin.php?page=stealthlead_offers_admin&action=stealthlead_deleteOffer&id='+id;
	}else{
		return false;
	}
}

function status_change(statusName, value, name, id){
	if(confirm("Are you sure you want to "+statusName+" this Offer \""+ name +"\" ?")){
		window.location='admin.php?page=stealthlead_offers_admin&action=stealthlead_statusChangeOffer&value='+value+'&id='+id;
	}else{
		return false;
	}
}