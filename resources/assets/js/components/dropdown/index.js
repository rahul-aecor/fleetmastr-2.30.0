module.exports = {
    template: require('./template.html'),
    props: ['screen', 'edit', 'is_trailer_attached'],
    data: function () {  
        return {
        }
    },
    computed: {
        getDropdownValues: function() {
            let values = '';
            for(let index = 0; index < this.screen.dropdowns.length; index++) {
                let dropdown = this.screen.dropdowns[index];
                if(dropdown.answer != '' && typeof dropdown.answer != 'undefined' && typeof dropdown.answer !== 'null' && dropdown.answer !== null) {
                    values += dropdown.label + ': ' + dropdown.answer + ', ';
                }
            };
            values = values.substring(0, values.length - 2);
            return values;
        }
    },
    methods: {
        handleEditDefect: function(defect, defectCategory) {
            this.$dispatch('edit-defect-clicked', defect, defectCategory);
        },
        
    }
}