<div class="row">
    <div class="col-md-12">
        <form class="form" id="{{ $formId }}">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="row align-items-center flex-grow-1">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" id='{{ $searchId }}' placeholder="Search by name" name="search_by_name">
                                </div>
                            </div>
                            {{-- <div class="col-md-3 pr-0">
                                <div class="form-group">
                                    @if($categoryId == 'reportCategory')
                                        {!! Form::select('category', ['' => ''] + $standardCategories, null, ['id' => $categoryId, 'class' => 'form-control select2me select2-category-list']) !!}
                                    @elseif($categoryId == 'category-select')
                                        {!! Form::select('category', ['' => ''] + $getAllCategories, null, ['id' => $categoryId, 'class' => 'form-control select2me select2-category-list']) !!}
                                    @else
                                        {!! Form::select('category', ['' => ''] + $categories, null, ['id' => $categoryId, 'class' => 'form-control select2me select2-category-list']) !!}
                                    @endif
                                </div>
                            </div> --}}
                            <div>
                                <div class="d-flex">
                                    <div class="d-flex mb-0" style="flex:none;">
                                        <button class="btn red-rubine btn-h-45" type="submit">
                                            <i class="jv-icon jv-search"></i>
                                        </button>
                                        <button class="btn btn-success grey-gallery btn-h-45" style="margin-right: 0" id='{{ $clearBtnId }}'>
                                            <i class="jv-icon jv-close"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-md-4">
                    <div class="form-group">
                        <button class="btn red-rubine btn-h-45 js-run-query-btn pull-right" type="button">Run query</button>
                    </div>
                </div> --}}
            </div>
        </form>
    </div>
</div>