module.exports = {
    template: require('./template.html'),
    props: ['originalDefect', 'prohibitionalDefects'],
    data: function () {
    },
    ready: function() {
        
    },
    methods: {
        submitDefectDetails: function (event) {
            event.preventDefault();
            this.$dispatch('prohibitional-defects-confirmed');
        }
    }
}