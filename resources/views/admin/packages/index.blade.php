@extends('admin.layouts.master')

@section('content')
  @if (config('app.demo') == true)
    <div class="alert alert-info">
      <h4><i class="fa fa-info"></i> {{ trans('app.info') }}</h4>
      {!! trans('messages.not_accessible_on_demo') !!}
      <a href="https://incevio.com/plugins" class="indent10" target="_blank">You can get all available plagins here. </a>
    </div>
  @else
    <div class="alert alert-danger">
      <h4><i class="fa fa-exclamation-triangle"></i> {{ trans('app.alert') }}</h4>
      {!! trans('messages.be_careful_sensitive_area') !!}
    </div>
  @endif

  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">{{ trans('app.available_packages') }}</h3>
      <div class="box-tools pull-right">
        {{-- <a href="javascript:void(0)" data-link="{{ route('admin.package.upload') }}" class="ajax-modal-btn btn btn-new btn-flat">
          <i class="fa fa-upload"></i>
          {{ trans('app.upload_package') }}
        </a> --}}
      </div>
    </div> <!-- /.box-header -->
    <div class="box-body responsive-table">
      <table class="table">
        <thead>
          <tr>
            <th width="30%">{{ trans('app.package') }}</th>
            <th>&nbsp;</th>
            <th>{{ trans('app.description') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($installables as $package)
            @php
              $dependencies = $package['dependency'];
              $can_load = !(bool) $dependencies;
              $registered = $installedPackages->where('slug', $package['slug'])->first();

              if (!$can_load) {
                  $arr = explode(',', $dependencies);
                  $can_load = is_incevio_package_loaded($arr);
                  $dependencies = count($arr) > 1 ? strrev(implode(strrev(', ' . trans('app.and') . ' '), explode(strrev(','), strrev($dependencies), 2))) : $dependencies;
              }

              // Forcefully deactivate the dependent packages
              if ($registered && $registered->active && !$can_load) {
                  $registered->deactivate();
              }
            @endphp

            <tr>
              <td>
                <h4 class="text-{{ $registered ? 'primary' : 'muted' }}">
                  <i class="fa fa-fw fa-{{ $package['icon'] ?? 'puzzle-piece' }} text-muted"></i>&nbsp;
                  {{ $package['name'] }}
                </h4>

                @unless ($can_load)
                  <small class="text-danger">
                    <i class="fa fa-ban"></i>
                    {{ trans('help.package_dependency_not_loaded', ['dependency' => $dependencies]) }}
                  </small>
                @endunless

                @if ($registered)
                  @unless ($registered->active && $package['active'] == false)
                    @if (config('app.demo') == true)
                      <span class="text-muted indent15" title="{!! trans('messages.demo_restriction') !!}" data-toggle="tooltip"><i class=" fa fa-trash-o"></i> {{ trans('app.uninstall') }}</span>
                    @else
                      {!! Form::open(['route' => ['admin.package.uninstall', $package['slug']]]) !!}
                      <button type="submit" class="confirm btn btn-sm btn-link indent15" data-confirm="{!! trans('help.confirm_uninstall_package', ['package' => $package['name']]) !!}">
                        <i class=" fa fa-trash-o"></i> {{ trans('app.uninstall') }}
                      </button>
                      {!! Form::close() !!}
                    @endif
                  @endunless
                @elseif($can_load)
                  @if (config('app.demo') == true)
                    <span class="text-muted" title="{!! trans('messages.demo_restriction') !!}" data-toggle="tooltip"><i class=" fa fa-wrench"></i> {{ trans('app.install') }}</span>
                    <a href="https://incevio.com/plugins" class="text-bold small indent15" target="_blank">Check it here </a>
                  @else
                    <a href="javascript:void(0)" data-link="{{ route('admin.package.initiate', $package['slug']) }}" type="button" class="btn btn-md btn-secondary indent15 ajax-modal-btn">
                      <i class=" fa fa-wrench"></i> {{ trans('app.install') }}
                    </a>
                  @endif
                @endif
              </td>
              <td>
                @if ($registered)
                  @if ($package['active'] == true)
                    <div class="text-center">
                      <small class="text-muted badge">{{ trans('app.activated') }}</small>
                    </div>
                  @else
                    <div class="handle horizontal">
                      <a href="javascript:void(0)" data-link="{{ route('admin.package.switch', $package['slug']) }}" type="button" class="btn btn-md btn-secondary btn-toggle {{ $registered && $registered->active ? 'active' : '' }}" data-doafter="reload" data-toggle="button" aria-pressed="{{ $registered && $registered->active ? 'true' : 'false' }}" autocomplete="off" {{ $can_load ? '' : 'disabled' }}>
                        <div class="btn-handle"></div>
                      </a>
                    </div>
                  @endif
                @endif
              </td>
              <td>
                <p>{{ $package['description'] }}</p>
                @unless (empty($package['warning']))
                  <p class="text-danger small">
                    <i class="fa fa-warning"></i>
                    {!! $package['warning'] !!}
                  </p>
                @endunless

                <span class="text-muted small">
                  {{ trans('app.version') . ' ' . $package['version'] }} &bull;
                  {{ trans('app.slug') . ': ' . $package['slug'] }} &bull;

                  @if ($registered)
                    {{-- <div class="alert alert-danger">
                        <h4><i class="fa fa-exclamation-triangle"></i> {{ trans('app.misconfigured') }}</h4>
                        {!! trans('messages.misconfigured_plugin', ['package' => $package['name']]) !!}
                      </div> --}}

                    {{ trans('app.installed_at') . ' ' . $registered->created_at }} &bull;
                    {{ trans('app.updated_at') . ' ' . $registered->updated_at }} &bull;
                  @endif

                  {{ trans('app.zcart_compatiblity') . ' ' . $package['compatible'] }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3">
                <h3>
                  You didn't have any package yet, <a href="https://incevio.com/plugins" class="indent10" target="_blank">You can get all available plagins here. </a>
                </h3>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div> <!-- /.box-body -->
  </div> <!-- /.box -->

  <div class="row">
    <div class="col-md-6">
      <div class="panel panel-success">
        <div class="panel-heading">
          <i class="fa fa-rocket"></i>
          More Packages Available!
        </div>
        <div class="panel-body">
          We're developing more and more packages with useful functionality extensions.
          <br /><br />
          <a href="https://incevio.com/plugins" class="btn btn-primary" target="_blank">
            All Available Packages
            <i class="fa fa-external-link"></i>
          </a>
        </div>
      </div>
    </div> <!-- /.col-md-6 -->

    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <i class="fa fa-rocket"></i>
          Looking for a custom packages?
        </div>
        <div class="panel-body">
          Send us an email for any kind of modification or custom work as we know the code better than everyone.
          <br /><br />
          <a href="https://incevio.com/contact" class="btn btn-default" target="_blank">
            Contact Us
            <i class="fa fa-external-link"></i>
          </a>
        </div>
      </div>
    </div> <!-- /.col-md-6 -->
  </div> <!-- /.row -->
@endsection
