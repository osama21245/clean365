@extends('adminmodule::layouts.master')

@section('title', translate('product_categories'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('product_categories')}}</h2>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">{{translate('add_new_category')}}</h4>
                            <form action="{{route('admin.product-category.store')}}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="mb-30">
                                    <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('name')}} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required
                                        placeholder="{{translate('e.g. Kitchen')}}" value="{{ old('name') }}">
                                </div>
                                <div class="mb-30">
                                    @include('adminmodule::admin.partials._single-image-upload', [
                                        'name' => 'image',
                                        'id' => 'categoryImage',
                                        'title' => translate('category_image'),
                                        'subtitle' => translate('Optional'),
                                        'required' => false,
                                        'image' => null,
                                        'ratio' => 'ratio-1-1',
                                        'height' => 'h-120',
                                        'instructionRatio' => '1:1',
                                        'showView' => false,
                                        'showEdit' => true,
                                        'showDelete' => true
                                    ])
                                </div>
                                <button type="submit" class="btn btn--primary w-100">{{translate('Submit')}}</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two" method="GET">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                            value="{{$search}}" name="search" placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('Sl')}}</th>
                                            <th>{{translate('image')}}</th>
                                            <th>{{translate('name')}}</th>
                                            <th>{{translate('products')}}</th>
                                            <th>{{translate('status')}}</th>
                                            <th>{{translate('action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($categories as $key => $category)
                                            <tr>
                                                <td>{{$categories->firstItem() + $key}}</td>
                                                <td>
                                                    <img width="45" class="rounded" src="{{$category->image_full_path}}"
                                                        alt="{{$category->name}}">
                                                </td>
                                                <td>{{$category->name}}</td>
                                                <td>{{$category->products_count}}</td>
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input status-change" type="checkbox"
                                                            {{$category->is_active ? 'checked' : ''}}
                                                            data-url="{{route('admin.product-category.status-update', [$category->id])}}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editCategory-{{$category->id}}">
                                                            <span class="material-icons">edit</span>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm form-alert"
                                                            data-id="category-{{$category->id}}"
                                                            data-message="{{translate('want_to_delete_this_category')}}?">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                        <form
                                                            action="{{route('admin.product-category.delete', [$category->id])}}"
                                                            method="post" id="category-{{$category->id}}" class="hidden">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </div>

                                                    <div class="modal fade" id="editCategory-{{$category->id}}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form
                                                                    action="{{route('admin.product-category.update', [$category->id])}}"
                                                                    method="post" enctype="multipart/form-data">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">{{translate('edit_category')}}
                                                                        </h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="mb-30">
                                                                            <label
                                                                                class="mb-2 lh-1 fs-14 fw-medium">{{translate('name')}}
                                                                                <span class="text-danger">*</span></label>
                                                                            <input type="text" name="name" class="form-control"
                                                                                required
                                                                                value="{{$category->getRawOriginal('name')}}">
                                                                        </div>
                                                                        <div class="mb-30">
                                                                            @include('adminmodule::admin.partials._single-image-upload', [
                                                                                'name' => 'image',
                                                                                'id' => 'categoryImage' . $category->id,
                                                                                'title' => translate('category_image'),
                                                                                'subtitle' => translate('Optional'),
                                                                                'required' => false,
                                                                                'image' => $category->image_full_path,
                                                                                'ratio' => 'ratio-1-1',
                                                                                'height' => 'h-120',
                                                                                'instructionRatio' => '1:1',
                                                                                'showView' => true,
                                                                                'showEdit' => true,
                                                                                'showDelete' => true
                                                                            ])
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn--secondary"
                                                                            data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                                                                        <button type="submit"
                                                                            class="btn btn--primary">{{translate('Update')}}</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    @include('adminmodule::layouts.partials.components._empty-state', ['text' => 'no_data_found'])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                {!! $categories->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection