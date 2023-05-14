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
        addAnswerClicked: function (questionIndex) {
            this.questions[questionIndex]['answers'].push({'is_correct': false, 'text': ''});
            this.$nextTick(function () {
                Metronic.initAjax();
            });
        },
        deleteAnswerClicked: function (questionIndex, answerIndex) {
            this.questions[questionIndex]['answers'].splice(answerIndex, 1);
        },
        addQuestionClicked: function() {
            this.questions.push({
                "question": "",
                "answers": [
                    {
                        "text": "",
                        "is_correct": true
                    },
                    {
                        "text": "",
                        "is_correct": false
                    }
                ]
            });
            this.$nextTick(function () {
                Metronic.initAjax();
            });
        },
        deleteQuestionClicked: function (questionIndex) {            
            this.questions.splice(questionIndex, 1);
        },
        answerChecked: function (questionIndex, answerIndex) {
            // Mark all the answers for this question as incorrect
            for (var index = 0; index < this.questions[questionIndex]['answers'].length; ++index) { 
                this.questions[questionIndex]['answers'][index]['is_correct'] = false;
            }            
            // Mark the current answer as correct
            this.questions[questionIndex]['answers'][answerIndex]['is_correct'] = true;
        }
    }    
}