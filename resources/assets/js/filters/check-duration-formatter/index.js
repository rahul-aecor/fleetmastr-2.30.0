module.exports = function (value) {    
	
	var outputString = "";
	if (value != null) {
		var time = value.split(":");
		if (typeof(time[0]) != 'undefined' && time[0] != "00") {
			outputString+=time[0]+" hours ";
		}
		if (typeof(time[1]) != 'undefined' && time[1] != "00") {
			outputString+=time[1]+" mins ";
		}
		if (typeof(time[2]) != 'undefined' && time[2] != "00") {
			outputString+=time[2]+" seconds";
		}
	}
	else{
		outputString = "N/A";
	}
    
    return outputString;
};