module.exports = function (value) {

    if (value.toLowerCase() === 'roadworthy') {
        return "<span class='label label-success label-results'>Roadworthy</span>"
    }

    else if (value.toLowerCase() === 'unsafetooperate') {
        return "<span class='label label-danger label-results'>Unsafe to operate</span>"
    }
    else if(value.toLowerCase() === 'safetooperate'){
        return "<span class='label label-warning label-results'>Safe to operate</span>"
    }
};