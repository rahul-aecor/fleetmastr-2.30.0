<form class="form-horizontal display-settings" role="form" action="/settings/store" method="POST" novalidate>
    <div class="row">
      <div class="col-md-12">
            {{ csrf_field() }}
            <div class="form-body">
                <p class="margin-bottom-20">To preview a new primary colour for the site, select a new colour from the pallette below and click on the 'Ok' button. To use this new colour as the primary colour click on the 'Save' button.</p>

                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group d-flex">
                            <label for="url" class="col-md-3 control-label align-self-center pt-0"><span class="bold">fleet</span>mastr URL*:</label>
                            <div class="col-md-4">
                                <input type="text" name="url" id="url" class="form-control" disabled="disabled" value="{{ config('app.url') }}">
                            </div>
                        </div>
                        <div class="form-group d-flex">
                            <label for="colorSelector2" class="col-md-3 control-label align-self-center pt-0">Primary colour*:</label>
                            <div class="col-md-4">
                                <input value="{{ setting('primary_colour') }}" class="form-control settings_primary_color" name="primary_colour" type="hidden">
                                <div id="customWidget" class="customWidget" style="background-color: #{{ setting('primary_colour') }}">
                                    <input type="text" id="colorpickerHolder2" value="{{ setting('primary_colour') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="logo" class="col-md-3 control-label">Logo*:</label>
                            <div class="col-md-5">
                                <img src="{{ setting('logo') }}" 
                                    alt="Logo" 
                                    class="img-thumbnail settings-logo-preview" 
                                    id="current-main-product-image"
                                    style="max-width: 160px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="image_prev_container">
                                                <img src="#" alt="" class="image_prev">
                                                <input type="hidden" id="x" name="x">
                                                <input type="hidden" id="y" name="y">
                                                <input type="hidden" id="w" name="w">
                                                <input type="hidden" id="h" name="h">
                                            </div>        
                                        </div>
                                    </div>
                                    <div class="upload-btn-wrapper">
                                        <button class="btn btn-h-45 red-rubine" id="settingsLogoBtn">Change image</button>
                                        <input type="file" name="settings_logo" id="settingsLogo" accept="image/*" />
                                    </div>

                                    <div class="float-right">
                                        <input type="hidden" value="{{ setting('logo') }}" name="image" id="main-product-image-form-input"/>
                                        <button id="settingsLogoUpload" class="btn btn-h-45 red-rubine" style="display:none;">Crop and upload</button>
                                    </div>

                                    <div class="form-text text-muted">Optimum image dimensions: 150px &times; 150px  (jpg, png or gif)</div>
                            </div>
                        </div>    
                    </div>
                </div>
            </div>

            <div class="row mt-2 pt15">
                <div class="col-md-4 col-md-offset-4">
                    <button type="submit" class="btn red-rubine btn-padding btn-block" name="submit">Save</button>
                </div>
            </div>
      </div>    
    </div><!-- /.modal-content -->
</form>
