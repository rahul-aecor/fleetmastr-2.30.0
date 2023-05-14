module.exports = function (value) {
	var images = value.split("|");
    if (images[0]) {
        return images[0];
    }    
};