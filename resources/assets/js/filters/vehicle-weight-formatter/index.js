module.exports = function (value) {
    if (value.toLowerCase() === 'non-hgv') {
        return "Non-HGV"
    }
    else if (value.toLowerCase() === 'hgv') {
        return "HGV"
    }
};