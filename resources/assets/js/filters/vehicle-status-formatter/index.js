module.exports = function (value) {
    if (value.toLowerCase() === 'roadworthy' || value.toLowerCase() === 'roadworthy (with defects)') {
        return "<span class='label label-success label-results'>" + value + "</span>"
    }

    else if (value.toLowerCase() === 'vor' || value.toLowerCase() === 'vor - accident damage' || value.toLowerCase() === 'vor - bodyshop' || value.toLowerCase() === 'vor - mot' || value.toLowerCase() === 'vor - service' || value.toLowerCase() === 'vor - bodybuilder' || value.toLowerCase() === 'vor - quarantined') {
        return "<span class='label label-danger label-results'>" + value + "</span>"
    }
    else {
        return "<span class='label label-warning label-results'>" + value + "</span>"
    }
};