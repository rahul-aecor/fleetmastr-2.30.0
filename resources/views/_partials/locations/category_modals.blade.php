<!-- Modal to add category starts here -->
<div id="add-category" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" aria-hidden="true">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="portlet box">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
              <h4 class="modal-title">Add Category</h4>
                <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                    <i class="jv-icon jv-close"></i>
                </a>
            </div>
            <div class="modal-body" id="ajax-modal-content">
                @if (count($errors) > 0)
                    <div class="alert alert-danger bg-red-rubine">
                        <!-- <p><strong>You have some form errors. Please check below.</strong></p>  -->
                        <p><strong>Please complete the errors highlighted below.</strong></p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {!! BootForm::openHorizontal(['md' => [3, 9]])->addClass('form-bordered form-validation js-add-category')->id('addCategory') !!}
                    {!! BootForm::text('Category*:', 'category_name')->addClass('category_name') !!}
                {!! BootForm::close() !!}
            </div>
            <div class="modal-footer">
            <div class="col-md-offset-2 col-md-8 ">
                    <div class="btn-group pull-left width100">
                        <button type="button" id="addCategoryCancel" class="btn white-btn btn-padding col-md-6" data-dismiss="modal">Cancel</button>
                        <button id="addCategoryBtn" type="button" class="btn red-rubine btn-padding submit-button col-md-6">Save</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal to view category starts here -->
<div id="view-category" class="modal modal-fix  fade" tabindex="-1" data-background="static" aria-labelledby="myModalLabel" data-width="630" aria-hidden="true">
    <div class="modal-dialog" style="margin-top: 0; margin-bottom: 0;">
        <div class="portlet box">
            <div class="modal-header bg-red-rubine d-flex align-items-center justify-content-between">
              <h4 class="modal-title">All Categories</h4>
              <a class="font-red-rubine" data-dismiss="modal" aria-label="Close">
                  <i class="jv-icon jv-close"></i>
              </a>
            </div>
            <div class="table-wrapper-scroll-y my-custom-scrollbar">
              <table class="table table-hover table-striped table-category">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col" width="70%">Category Name</th>
                    <th scope="col" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="view_all_categories">
                </tbody>
              </table>
            </div>

        </div>
    </div>
</div>