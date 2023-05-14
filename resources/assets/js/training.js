var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name=_token]').attr('content');
Vue.config.debug = true; 
Vue.filter('dateFormatter', require('./filters/date-formatter'));

var contactform = require('./components/contact-form');
var groupform = require('./components/group-form');
var templateform = require('./components/template-form');
var messageform = require('./components/message-form');
var messagesform = require('./components/messages-form');
var u = require('lodash');

var messageVueObj = new Vue({
    el: '#messages-page',
    components: {
        "contactform": contactform,
        "groupform": groupform,
        "templateform": templateform,
        "messageform": messageform,
        "messagesform": messagesform,
        // "pagination": require('vue-bootstrap-pagination')
    },
    // initial data
    data: {
        siteContacts: [],
        contacts: [],
        groups: [],
        templates: [],
        messages: [],
        pagination: {
            total: 0, per_page: 1,
            from: 1, to: 0,
            current_page: 1
        },
        userdivisions: []
    }, 
    // dom ready
    ready: function () {
        Metronic.init();
        // this.getSiteContacts();
        this.getGroups();
        this.getTemplates();
        // this.getUserDivisions();
        this.getUserRegions();
        this.getPaginatedMessages();
        setTimeout(function(){
            messageVueObj.getSiteContacts();
        }, 1000);
    },
    // vue methods
    events: {
        'group-list-changed': function () {
            this.getGroups();
        },
        'template-list-changed': function () {
            this.getTemplates();
        },
        'message-list-changed': function () {
            this.getPaginatedMessages();
        }
    },    
    methods: {    
        getSiteContacts: function() {
            this.$http.get('/users/get_enabled_users', function(siteContacts){                
                this.$set('siteContacts', siteContacts);
            });
        },
        getGroups: function() {
            this.$http.get('/groups', function(groups){
                this.$set('groups', groups);
            });
        },
        getTemplates: function() {
            this.$http.get('/templates', function(templates){
                this.$set('templates', templates);
            });
        },
        // getUserDivisions: function() {
        //     this.$http.get('/users/get_user_divisions', function(userdivisions){
        //         this.$set('userdivisions', userdivisions);
        //     });
        // },
        getUserRegions: function() {
            this.$http.get('/users/get_user_regions', function(userdivisions){
                this.$set('userdivisions', userdivisions);
            });
        },
        getPaginatedMessages: function(direction) {
            var data = {
                paginate: this.pagination.per_page,
                page: this.pagination.current_page
            };
            var _this = this;
            this.$http.get('/messages/paginate', data).then(
                function(response) {
                    _this.$set('messages', response.data.data);
                    _this.$set('pagination', response.data);
                },
                function(error) {
                    toastr["error"]("Messages could not be fetched! Please refresh and try again.");
                }
            );
        }
    }
});

