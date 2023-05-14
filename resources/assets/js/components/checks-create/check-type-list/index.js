var u = require('lodash');

module.exports = {
    template: require('./template.html'),
    props: ['screen', 'edit', 'existingDefectListParsed','isTrailerAttached', 'newDefect'],
    data: function () {  
        return {            
        }
    },
    methods: {
        handleEditDefect: function(defect, defectCategory) {
            this.$dispatch('edit-defect-clicked', defect, defectCategory);
        },
        handleRemoveDefect: function(defect) {
            this.$dispatch('remove-defect-clicked', defect);
        },
        getTitleClass(option) {
            var existingDefectListParsed = this.existingDefectListParsed;

            var prohibitionalDefects = u.filter(option.defects.defect, function(defect) {
                return (defect.prohibitional === 'yes' && (defect.selected === 'yes' || ( $.inArray(defect.id, u.keys(existingDefectListParsed) ) > -1 && existingDefectListParsed[defect.id].selected === 'yes' ) ) );
            });

            if(prohibitionalDefects.length > 0) {
                return {
                    'font-pure-red': true
                };
            }

            var nonProhibitionalDefects = u.filter(option.defects.defect, function(defect) {
                return (defect.prohibitional === 'no' && (defect.selected === 'yes' || ( $.inArray(defect.id, u.keys(existingDefectListParsed) ) > -1 && existingDefectListParsed[defect.id].selected === 'yes' ) ) );
            });

            if(nonProhibitionalDefects.length > 0) {
                return {
                    'font-pure-orange': true
                };
            }

            return {
                'font-pure-green': true
            };

        }
    }
}