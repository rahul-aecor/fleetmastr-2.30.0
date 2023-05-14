var editor = require('../template-form/directives/editor');

module.exports = {
    template: require('./template.html'),
    props: ['questions', 'plugins'],
    directives: {
        editor: editor
    },
    data: function () {
        return {
           
        }
    },
    // dom ready
    ready: function () {        
    },
    methods: {
        addQuestionClicked: function() {
            this.questions.push({
                "text": "",
            });
            this.$nextTick(function () {
                Metronic.initAjax();
            });
        },
        deleteQuestionClicked: function (questionIndex) {            
            this.questions.splice(questionIndex, 1);
        }
    }
}