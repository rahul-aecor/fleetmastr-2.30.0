module.exports = {
    twoWay: false,
    params: ['id', 'index'],
    binder: function (ccc) {
        let self = this
        let idee = self.el.getAttribute('id')
        console.log('bind triggered in directive editor');
        console.log('params: ' + this.params.id);
        console.log(this.params);
        tinymce.init({
            selector: '#' + idee,
 
            setup: function(editor) {
                console.log('setup');
         
                editor.on('init', function() {
                    this.fire('keyup')                    
                    this.setContent(ccc);
                })
           
                editor.on('keyup', function() {
                    self.set(this.getContent())
                })
            },
 
            plugins: [
                'link image media'
            ]
        })
    },
    update: function(neu, old) {
        console.log('update');
        console.log(neu);
        if (neu == undefined || neu == null) 
            neu = ''
 
        let idee = this.params.id//this.el.getAttribute('id')
        console.log(idee);
        if (tinymce.get(idee) == null) {
            this.binder(neu)
            return
        }
 
        tinymce.get(idee).setContent(neu)
       // tinymce.get(idee).fire('keyup')
    },
    unbind: function() {
        console.log('unbind');
        //tinymce.remove('#' + this.el.getAttribute('id'))
    },
    paramWatchers: {
        content: function () {
            console.log('param watcher triggered');
            // tinymce.get(this.params.id).setContent(this.params.content, {format: 'raw'});
        }
    }
}