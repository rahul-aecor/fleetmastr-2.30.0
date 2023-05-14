module.exports = {
    template: require('./template.html'),
    props: ['screen', 'edit', 'is_trailer_attached'],
    data: function () {  
        return {            
        }
    },
    computed: {
    },
    filters: {
        ucfirst: function (value) {
            value = value.toLowerCase();
            return value.charAt(0).toUpperCase() + value.slice(1);
        }
    },
    methods: {
        handleEditDefect: function(defect, defectCategory) {
            this.$dispatch('edit-defect-clicked', defect, defectCategory);
        },
        getDropdownValues: function(dropdowns) {
            let values = '';
            for(let index = 0; index < dropdowns.length; index++) {
                let dropdown = dropdowns[index];
                if(dropdown.answer != '' && typeof dropdown.answer !== 'undefined' && typeof dropdown.answer !== 'null' && dropdown.answer !== null) {
                    values += dropdown.label + ': ' + dropdown.answer + ', ';
                }
            };
            values = values.substring(0, values.length - 2);
            return values;
        },
        getInputValues: function(inputs) {
            let values = '';
            for(let index = 0; index < inputs.length; index++) {
                let input = inputs[index];
                if(input.answer != '' && typeof input.answer !== 'undefined' && typeof input.answer !== 'null' && input.answer !== null) {
                    values += input.label + ': ' + input.answer + ', ';
                }
            };
            values = values.substring(0, values.length - 2);
            return values;
        },
        checkImage: function(image) {
            if (!image.startsWith("https://")) {
                this.$http.post( '/update/checkimage', { temp_id:image } ).then(
                    function(response) {
                        if(response == '') {
                            return image;
                        } else {
                            return response;
                        }
                    }, 
                    function(error) {
                        return image;
                    }
                );
            } else {
                return image;
            }
        }
    }
}