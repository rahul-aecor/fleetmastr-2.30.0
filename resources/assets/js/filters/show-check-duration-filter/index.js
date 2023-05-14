module.exports = function (value) {
    if (value.toLowerCase() == 'vehicle check') {
        return '';
    }
    else if (value.toLowerCase() == 'return check') {
        return '';
    }                
    else if (value.toLowerCase() == 'report defect') {
        return 'hidden';
    }
};