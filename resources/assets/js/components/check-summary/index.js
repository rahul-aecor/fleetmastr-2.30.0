import moment from 'moment'
import moment_timezone from 'moment-timezone'

module.exports = {
    template: require('./template.html'),
    props: ['check'],
    data: function () {
        return {
        }
    },
    methods: {
    	formatDateTime(value) {
            return moment.utc(value).tz('Europe/London').format('HH:mm:ss DD MMM YYYY');
    	}
    }
}
