module.exports = {
    template: require('./template.html'),
    props: ['screen', 'edit'],
    data: function () {  
        return {            
        }
    },
    methods: {
        handleEditDefect: function(defect, defectCategory) {
            this.$dispatch('edit-defect-clicked', defect, defectCategory);
        }
    }
}