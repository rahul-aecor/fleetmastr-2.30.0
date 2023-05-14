/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */

// the semi-colon before the function invocation is a safety
// net against concatenated scripts and/or other plugins
// that are not closed properly.
;(function ( $, window, document, undefined ) {

    // undefined is used here as the undefined global
    // variable in ECMAScript 3 and is mutable (i.e. it can
    // be changed by someone else). undefined isn't really
    // being passed in so we can ensure that its value is
    // truly undefined. In ES5, undefined can no longer be
    // modified.

    // window and document are passed through as local
    // variables rather than as globals, because this (slightly)
    // quickens the resolution process and can be more
    // efficiently minified (especially when both are
    // regularly referenced in your plugin).

    // Create the defaults once
    var pluginName = "jqGridHelper",
        defaults = {
            mtype: "POST",
            datatype: "json",
            page: 1,
            rowList: [20,50,100],
            hoverrows: false,
            autowidth: true,
            shrinkToFit: false,
            height: 'auto',
            loadui: 'disable',
            //emptyrecords: 'No information available',
            rowNum: 20,
            viewrecords: true,
            recordtext: "View {0} - {1} of {2}",
            recordpos: "left",
            recordtext: "Viewing {0} - {1} of {2}",
            /*recordtext: "{1} records",*/
            pager: "#jqGridPager",
            cmTemplate: { title: false,resizable:false },
            viewsortcols : [true,'vertical',true],
            beforeRequest : function () {
                if(!$(this).hasClass('no-loading-modal')) {
                    $("#processingModal").modal('show');
                }
            },
            loadComplete: function() {
                $("#processingModal").modal('hide');
                var ts = this;
                if($('#emptyGridMessage').length){
                   // $('#emptyGridMessage').show();
                }
                else{
                    emptyMsgDiv = $("<div id='emptyGridMessage' style='padding:6px;text-align:center'><span>No information available</span></div>");
                    emptyMsgDiv.insertAfter($('#jqGrid').parent());
                }
                if (ts.p.reccount === 0) {
                    $(this).hide();
                    $('#emptyGridMessage').show();
                    $('#jqGridPager div.ui-paging-info').hide();
                } else {
                    $(this).show();
                    $('#emptyGridMessage').hide();
                    $('#jqGridPager div.ui-paging-info').show();
                }

                if ($("#jqGrid").jqGrid('getGridParam', 'reccount') == 0) {
                    $(".ui-jqgrid-hdiv").css("overflow-x", "auto")
                } else {
                    $(".ui-jqgrid-hdiv").css("overflow-x", "hidden")
                }
                    // $(".ui-jqgrid-sortable .s-ico").show();
                    
                    // if($("#jqGrid_details").length)
                    //     $("#jqGrid_details .ui-jqgrid-sortable .s-ico").hide();
            },
            beforeSelectRow: function(rowid, e) {
                return false;
            }
        };

        exportDefaults = {
            caption: '',
            buttonicon: 'glyphicon-floppy-save',
            fileProps: {
                "title": "Works", 
                "creator": "Lanes Fieldviewer"
            },
            id: 'export_jqgrid'
        }

    // The actual plugin constructor
    var originalColModel = "";
    function Plugin( element, options ) {
        originalColModel = options.colModel;
        var colModel = $.map( options.colModel, function( val, i ) {
            return (typeof val.showongrid === 'undefined' || val.showongrid === true) ? val : null;                    
        });
        this.element = element;

        // jQuery has an extend method that merges the
        // contents of two or more objects, storing the
        // result in the first object. The first object
        // is generally empty because we don't want to alter
        // the default options for future instances of the plugin
        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this.options.colModel = colModel;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {

        init: function() {
            // Place initialization logic here
            // You already have access to the DOM element and
            // the options via the instance, e.g. this.element
            // and this.options
            // you can add more functions like the one below and
            // call them like so: this.yourOtherFunction(this.element, this.options).
            $(this.element).jqGrid(this.options);
        },

        addNavigation: function() {
            // this.navOptions = $.extend( {}, navDefaults, options);
            $(this.element).navGrid(this.options.pager, {
                search: true, // show search button on the toolbar
                add: false,
                edit: false,
                del: false,
                refresh: true
            },
            {}, // edit options
            {}, // add options
            {}, // delete options
            { 
                multipleSearch: true, 
                resize: false,
                beforeSearch: function () {
                    
                }
            } // search options - define multiple search
            );
        },

        addExportButton: function(options) {
            var _pagerId='#jqGridPager';
            if((options.pagerId!=undefined || options.pagerId!=null) && options.pagerId.length>0){
                _pagerId='#'+options.pagerId;
            }
            this.exportOptions = $.extend( {}, exportDefaults, options);
            this.exportOptions.onClickButton = this.getExportClickHandler(this.element, this.exportOptions);
            $(this.element).navButtonAdd(_pagerId, this.exportOptions);
        },

        getExportClickHandler: function(el, options) {

            return function() {

                if(options.url == '/defects/data') {
                    jQuery("#jqGrid").showCol(['modified_date']);
                    jQuery("#jqGrid").hideCol(['modified_date_sort']);
                }

                var postData;
                var f = $('<form method="POST" style="display: none;"></form>');
                
                // fetch values to be set in the form
                var formToken = $('meta[name=_token]').attr('content');
                var fileProps = JSON.stringify(options.fileProps);
                var sheetProps = JSON.stringify({"fitToPage":true,"fitToHeight":true});                
                var colModel = originalColModel;

                //Custom update jqgrid column values
                var colModelLatest = $(el).jqGrid('getGridParam', 'colModel');
                var coldt = {};
                var ln = colModelLatest.length;
                var i;
                for (i = 0; i < ln; i++) {

                    coldt[colModelLatest[i]['name']] = { 'order': i, 'hidden': colModelLatest[i]['hidden'] };
                }

                $.each(colModel, function( coIndex, coValue ){
                    if(coldt.hasOwnProperty(coValue.name) == true){
                        colModel[coIndex]['hidden'] = coldt[coValue.name]['hidden'];
                        colModel[coIndex]['order'] = coldt[coValue.name]['order'];
                    }
                });
                colModel.sort(function(a, b){
                    return a.order - b.order
                });
                //End custom changes

                colModel = $.map( colModel, function( val, i ) {
                    return (typeof val.export === 'undefined' || val.export === true) ? val : null;                    
                });
                var model = JSON.stringify(colModel);
                var filters = "";
                
                postData = $(el).getGridParam("postData");
                if (postData["filters"] != undefined) {
                    filters = postData["filters"];
                }

                var sidx = "";
                if (postData["sidx"] != undefined) {
                    sidx = postData["sidx"];
                }

                var sord = "";
                if (postData["sord"] != undefined) {
                    sord = postData["sord"];
                }

                // build the form skeleton
                f.attr('action', options.url)
                 .append(
                    '<input name="_token">' +
                    '<input name="name">' + 
                    '<input name="model">' +
                    '<input name="exportFormat" value="xls">' +
                    '<input name="filters">' +
                    '<input name="sidx">' +
                    '<input name="sord">' +
                    '<input name="pivot" value="">' +
                    '<input name="pivotRows">' +
                    '<input name="fileProperties">' +
                    '<input name="sheetProperties">' +
                    '<input name="download" value="true">'
                );

                 // set form values
                 $('input[name="_token"]', f).val(formToken);
                 $('input[name="model"]', f).val(model);
                 $('input[name="name"]', f).val(options.fileProps.title);
                 $('input[name="filters"]', f).val(filters);
                 $('input[name="fileProperties"]', f).val(fileProps);
                 $('input[name="sheetProperties"]', f).val(sheetProps);
                 $('input[name="sidx"]', f).val(sidx);
                 $('input[name="sord"]', f).val(sord);
                 
                 if(options.url == '/defects/data') {
                    jQuery("#jqGrid").hideCol(['modified_date']);
                    jQuery("#jqGrid").showCol(['modified_date_sort']);
                 }

                 f.appendTo('body').submit();

            }
        }

    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function(options,additionaloptions) {
        return this.each(function() {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
            }
            else if (Plugin.prototype[options]) {
                $.data(this, 'plugin_' + pluginName)[options](additionaloptions);
            }
        });
    }

})( jQuery, window, document );