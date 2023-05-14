@extends('layouts.default')

@section('styles')
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui.css') }}" rel="stylesheet" type="text/css"/>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-noscript.css') }}"></noscript>
    <noscript><link rel="stylesheet" href="{{ elixir('css/jquery-file-upload/jquery.fileupload-ui-noscript.css') }}"></noscript>
@endsection

@section('scripts')
    <script src="{{ elixir('js/jquery-file-upload/jquery.ui.widget.js') }}" type="text/javascript"></script>
    <!-- The Templates plugin is included to render the upload/download listings -->
    <script src="{{ elixir('js/jquery-file-upload/tmpl.min.js') }}" type="text/javascript"></script>
    <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
    <script src="{{ elixir('js/jquery-file-upload/load-image.min.js') }}" type="text/javascript"></script>
    <!-- The Canvas to Blob plugin is included for image resizing functionality -->
    <script src="{{ elixir('js/jquery-file-upload/canvas-to-blob.min.js') }}" type="text/javascript"></script>
    <!-- blueimp Gallery script -->
    <script src="{{ elixir('js/blueimp-gallery/jquery.blueimp-gallery.min.js') }}" type="text/javascript"></script>
    <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.iframe-transport.js') }}" type="text/javascript"></script>
    <!-- The basic File Upload plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload.js') }}" type="text/javascript"></script>
    <!-- The File Upload processing plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-process.js') }}" type="text/javascript"></script>
    <!-- The File Upload image preview & resize plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-image.js') }}" type="text/javascript"></script>
    <!-- The File Upload audio preview plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-audio.js') }}" type="text/javascript"></script>
    <!-- The File Upload video preview plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-video.js') }}" type="text/javascript"></script>
    <!-- The File Upload validation plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-validate.js') }}" type="text/javascript"></script>
    <!-- The File Upload user interface plugin -->
    <script src="{{ elixir('js/jquery-file-upload/jquery.fileupload-ui.js') }}" type="text/javascript"></script>

    <script src="{{ elixir('js/vehicle-doc-upload.js') }}" type="text/javascript"></script>
@endsection

@section('content')
    <div class="page-title-inner">
        <h3 class="page-title">{{ $title }}</h3><br>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                {!! BootForm::open()->action('/vehicles/get_store_docs/'.$id)->id('saveVehicleDocument')->multipart() !!}
                    <div class="portlet box bg-red-rubine">
                        <div class="portlet-title">
                            <div class="caption">
                                Upload Vehicle Documents
                            </div>                    
                        </div>
                        <div class="portlet-body">
                            <div class="fileupload-buttonbar">
                                <!-- The fileinput-button span is used to style the file input field as button -->
                                <span class="btn green fileinput-button grey-gallery">
                                    <i class="fa fa-plus"></i>
                                    <span>Add files... </span>
                                    <input type="file" name="files[]" multiple="">
                                </span>
                                <!-- <button type="submit" class="btn blue start"><i class="fa fa-upload"></i>Start upload</button>
                                <button type="reset" class="btn warning cancel grey-gallery">Cancel upload</button> -->
                                <!-- <button type="button" class="btn btn-danger delete"><i class="fa fa-trash"></i>Delete</button>
                                <input type="checkbox" class="toggle"> -->
                            </div>

                            <!-- The table listing the files available for upload/download -->
                            <table role="presentation" class="table table-striped clearfix" id="upload-media-table">
                                <tbody class="files">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <button type="button" class="btn bg-red-rubine" id="save-vehicle-doc-btn">Save</button>
                                <button type="button" class="btn default cancel-btn grey-gallery">Cancel</button>
                            </div>
                        </div>
                    </div>
                {!! BootForm::close() !!}
                <!-- END FORM-->
            </div>
        </div>
    </div>

    <script id="template-upload" type="text/x-tmpl">
        {% for (var i=0, file; file=o.files[i]; i++) { %}
            <tr class="template-upload fade">
                <td>
                    <span class="preview"></span>
                </td>
                <td>
                    <p class="name">{%=file.name%}</p>
                    <strong class="error text-danger"></strong>
                </td>
                <td>
                    <p class="size">Processing...</p>
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
                </td>
                <td>
                    <input type="text" placeholder="Enter document name" id="caption" name="name"/>
                </td>
                <td>
                    {% if (!i && !o.options.autoUpload) { %}
                        <button class="btn start bg-red-rubine" disabled>
                            <i class="glyphicon glyphicon-upload"></i>
                            <span>Start</span>
                        </button>
                    {% } %}
                    {% if (!i) { %}
                        <button class="btn grey-gallery cancel">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>Cancel</span>
                        </button>
                    {% } %}
                </td>
            </tr>
        {% } %}
    </script>

    <script id="template-download" type="text/x-tmpl">
        {% for (var i=0, file; file=o.files[i]; i++) { %}
            <tr class="template-download fade">
                <td>
                    <span class="preview">
                        {% if (file.thumbnailUrl) { %}
                            <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                        {% } %}
                    </span>
                </td>
                <td>
                    <p class="name">
                        {% if (file.url) { %}
                            <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                        {% } else { %}
                            <span>{%=file.name%}</span>
                        {% } %}
                    </p>
                    {% if (file.error) { %}
                        <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                    {% } %}
                </td>
                <td>
                    <span class="size">{%=o.formatFileSize(file.size)%}</span>
                </td>
                <td>
                    {% if (file.deleteUrl) { %}
                        <button class="btn grey-gallery delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                            <i class="glyphicon glyphicon-trash"></i>
                            <span>Delete</span>
                        </button>
                    {% } else { %}
                        <button class="btn btn-warning cancel">
                            <i class="glyphicon glyphicon-ban-circle"></i>
                            <span>Cancel</span>
                        </button>
                    {% } %}
                </td>
                <td>&nbsp;</td>
            </tr>
        {% } %}
    </script>
@endsection