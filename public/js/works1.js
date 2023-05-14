$( document ).ready( function() {
    $( "#status" ).change( function(){
        if( $(this).val() == "Completed" ) {
            $( "#complete-time" ).attr( "class", "form-group" ).show();
        } else {
            $( "#complete-time" ).attr( "class", "form-group" ).hide();
            $( "#complete-time .form_datetime input" ).val( "" );
        }
    } );

    //Initialize form validation
    var validateRules = {
        "reference_id": {
            required: true
        },
        "status": {
            required: true
        },
        "started_at": {
            required: true
        }
    };

    $( "#submit-button" ).click(function(){
        var formId = $( ".form-horizontal" ).attr( "id" );
        checkValidation( validateRules, formId );
    });
} );