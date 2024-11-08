@extends('theme::layouts.main')

@section('content')
  <!-- HEADER SECTION -->
  <div class="container">
    <header class="page-header">
      <div class="row">
        <div class="col-md-12">
          <ol class="breadcrumb nav-breadcrumb">
            @include('theme::headers.lists.home')

            @if (Request::has('ingrp'))
              <li class="active">{{ $category->name }}</li>
            @elseif(Request::has('insubgrp') && Request::get('insubgrp') != 'all')
              <li>
                <a class="link-filter-opt" data-name="ingrp" data-value="{{ $category->group->slug }}">
                  {{ $category->group->name }}
                </a>
              </li>

              <li class="active">{{ $category->name }}</li>
            @elseif(Request::has('in'))
              <li>
                <a class="link-filter-opt" data-name="ingrp" data-value="{{ $category->subGroup->group->slug }}">
                  {{ $category->subGroup->group->name }}
                </a>
              </li>

              <li>
                <a class="link-filter-opt" data-name="insubgrp" data-value="{{ $category->subGroup->slug }}">
                  {{ $category->subGroup->name }}
                </a>
              </li>
              <li class="active">{{ $category->name }}</li>
            @endif

            <li class="active">
              "<strong class="text-primary">{{ Request::get('q') }}</strong>"
              <span class="ml-1">({{ trans('app.search_result_found', ['count' => $products->total()]) }})</span>
            </li>
          </ol>
        </div> <!-- /.col -->
      </div> <!-- /.row -->
    </header>
  </div> <!-- /.container -->

  <!-- CONTENT SECTION -->
  <section>
    <div class="container category-single-page">

      @include('theme::contents.product_list', ['colum' => 3])

    </div> <!-- /.container -->
  </section>

  <!-- BROWSING ITEMS -->
  @include('theme::sections.recent_views')
@endsection
