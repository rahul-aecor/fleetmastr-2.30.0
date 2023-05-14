module.exports = {
    template: require('./template.html'),
    props: ['screen', 'edit', 'is_trailer_attached'],
    data: function () {  
        return {
        }
    },
    computed: {
        getInputValues: function() {
            let values = '';
            for(let index = 0; index < this.screen.inputs.length; index++) {
                let input = this.screen.inputs[index];
                if(input.answer != '' && typeof input.answer != 'undefined' && typeof input.answer !== 'null' && input.answer !== null) {
                    values += input.label + ': ' + input.answer + ', ';
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