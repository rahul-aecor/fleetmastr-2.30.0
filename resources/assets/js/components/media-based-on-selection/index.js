module.exports = {
    template: require('./template.html'),
    props: ['screen', 'edit', 'is_trailer_attached'],
    data: function () {  
        return {
        }
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