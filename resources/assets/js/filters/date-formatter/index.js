module.exports = function (value) {
    return moment.utc(value, "YYYY-MM-DD HH:mm:SS").local().format("HH:mm:SS DD MMM YYYY");
};