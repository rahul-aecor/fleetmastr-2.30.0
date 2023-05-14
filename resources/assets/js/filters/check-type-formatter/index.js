module.exports = function (value) {
    if (value.toLowerCase() == 'vehicle check') {
        return 'Vehicle take out';
    }
    else if (value.toLowerCase() == 'vehicle check on-call') {
        return 'Vehicle take out (On-call)';
    }
    else if (value.toLowerCase() == 'return check') {
        return 'Vehicle return';
    }                
    else if (value.toLowerCase() == 'report defect') {
        return 'Defect report';
    }
    else if (value.toLowerCase() == 'defect report') {
        return 'Defect report';
    }
};