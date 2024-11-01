function animateButton() {		
	document.getElementById("linkup").className += " animate";
}
        
function toggle() {
    var ele = document.getElementById("toggleAdvance");
    var text = document.getElementById("displayAdvance");
    if(ele.style.display == "block") {
        ele.style.display = "none";
        text.innerHTML = "Advance";
    }
    else {
        ele.style.display = "block";
        text.innerHTML = "Hide Advance";
    }
} 
function toggleOpenAdvance() {
    var ele = document.getElementById("toggleAdvance");
    var text = document.getElementById("displayAdvance");
    ele.style.display = "block";
    text.innerHTML = "Hide Advance";
}
