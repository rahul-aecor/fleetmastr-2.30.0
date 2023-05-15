$( document ).ready( function() {
    $( "#saveVehicleDocument" ).fileupload();
    $( "#saveVehicleDocument" ).bind( "fileuploadadded", function (e, data) { 
        var inputs = data.context.find( ":input[type='text']" ); 
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.')); 
        $(inputs[0]).val(withoutext);   
    } );
    $( "#saveVehicleDocument" ).bind( "fileuploaddone", function (e, data) {
        if (data.result === 0) {
            toastr["error"]( "Image could not be uploaded" );
        } else {
            toastr["success"]( "Image uploaded successfully." );
        }
    } );
    $( "#saveVehicleDocument" ).bind("fileuploadsubmit", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );        
        if (inputs.filter(function () {
                return !this.value && $(this).prop( "required" );
            }).first().focus().length) {
            data.context.find( "button" ).prop( "disabled", false );
            return false;
        }
        data.formData = inputs.serializeArray();
    } );
    
    var dropZoneElement = $("#updateVehicleDocument .dropZoneElement");
    $( "#updateVehicleDocument").fileupload({
        filesContainer : $("#updateVehicleDocument").find($("#upload-media-modal-table > tbody")),
        dropZone : dropZoneElement
    });
    $( "#updateVehicleDocument" ).bind( "fileuploadadded", function (e, data) { 
        var inputs = data.context.find( ":input[type='text']" ); 
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.')); 
        $(inputs[0]).val(withoutext); 
        //$(inputs[0]).val(data.files[0].name);
        // if($('#upload-media-modal-table #caption').length > 1) {
        //     $('.template-upload').last().remove();
        // }
    } );
    $( "#updateVehicleDocument" ).bind( "fileuploaddone", function (e, data) {        
        toastr["success"]("Document(s) uploaded successfully.");
        $('#upload-media-modal-table tr:last').prependTo("#upload-media-modal-table");
    } );
    $( "#updateVehicleDocument" ).bind("fileuploadsubmit", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );        
        if (inputs.filter(function () {
                return !this.value;
            }).first().focus().length) {
            data.context.find("span.help-block").show();
            data.context.find(".js-file-name-td").addClass("has-error");
            data.context.find( "button" ).prop( "disabled", false );
            return false;
        }
        data.formData = inputs.serializeArray();
    } );
    $(document).on('keyup', "#updateVehicleDocument input[type='text']", function(e){
        if($(this).val()) {
            $(this).next('span.help-block').hide();
            $(this).closest(".js-file-name-td").removeClass("has-error");
        } else {
            $(this).next('span.help-block').show();
            $(this).closest(".js-file-name-td").addClass("has-error");
        }
    });
    $( "#updateVehicleDocument input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#updateVehicleDocument .dropZoneElement").addClass('is-dragover');
    });
    $( "#updateVehicleDocument input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#updateVehicleDocument .dropZoneElement").removeClass('is-dragover');
    } );
    $( "#updateVehicleDocument" ).bind( "fileuploaddestroyed", function (e, data) {        
        toastr["success"]("Document(s) deleted successfully.");
    } );
    // $( "#updateVehicleDocument" ).bind('fileuploadstop', function (e) {
    //     toastr["success"]("Document(s) uploaded successfully.");
    // });
    $('#updateVehicleDocument').addClass('fileupload-processing');
    $.ajax({
        url: $('#updateVehicleDocument').attr('action'),
        dataType: 'json',
        context: $('#updateVehicleDocument')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
        //$(this).fileupload('option', 'done').call(this, $.Event('done'), {result: result});
    });

    // setTimeout(function() {
    //     $('#documents #upload-media-modal-table').DataTable( {
    //         "lengthMenu": [[1,2,10, 25, 50, -1], [1,2,10, 25, 50, "All"]],
    //         bFilter: false,
    //         "sDom": 'lfrtip',
    //         bJQueryUI: false
    //     } );
    // }, 100);
} );