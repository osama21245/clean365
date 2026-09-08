@extends('adminmodule::layouts.master')

@section('title', translate('Add Article'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="page-title mb-3">{{ translate('Add Article') }}</h2>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @include('blogmodule::admin.articles._form')
                        <div class="col-12">
                            <button class="btn btn--primary" type="submit">{{ translate('Submit') }}</button>
                            <a href="{{ route('admin.blog.list') }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
