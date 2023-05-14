module.exports = {
    template: require('./template.html'),
    props: ['screen', 'edit', 'is_trailer_attached'],
    data: function () {  
        return {
        }
    },
    computed: {
    },
    methods: {
        handleEditDefect: function(defect, defectCategory) {
            this.$dispatch('edit-defect-clicked', defect, defectCategory);
        },
        
    }
}