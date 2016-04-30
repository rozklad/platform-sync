@extends('layouts/default')

{{-- Page title --}}
@section('title')
@parent
{{{ trans("action.{$mode}") }}} {{ trans('sanatorium/sync::dictionaryentries/common.title') }}
@stop

{{-- Queue assets --}}
{{ Asset::queue('validate', 'platform/js/validate.js', 'jquery') }}

{{-- Inline scripts --}}
@section('scripts')
@parent
@stop

{{-- Inline styles --}}
@section('styles')
@parent
@stop

{{-- Page content --}}
@section('page')

<section class="panel panel-default panel-tabs">

	{{-- Form --}}
	<form id="sync-form" action="{{ request()->fullUrl() }}" role="form" method="post" data-parsley-validate>

		{{-- Form: CSRF Token --}}
		<input type="hidden" name="_token" value="{{ csrf_token() }}">

		<header class="panel-heading">

			<nav class="navbar navbar-default navbar-actions">

				<div class="container-fluid">

					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#actions">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>

						<a class="btn btn-navbar-cancel navbar-btn pull-left tip" href="{{ route('admin.sanatorium.sync.dictionaryentries.all') }}" data-toggle="tooltip" data-original-title="{{{ trans('action.cancel') }}}">
							<i class="fa fa-reply"></i> <span class="visible-xs-inline">{{{ trans('action.cancel') }}}</span>
						</a>

						<span class="navbar-brand">{{{ trans("action.{$mode}") }}} <small>{{{ $dictionaryentries->exists ? $dictionaryentries->id : null }}}</small></span>
					</div>

					{{-- Form: Actions --}}
					<div class="collapse navbar-collapse" id="actions">

						<ul class="nav navbar-nav navbar-right">

							@if ($dictionaryentries->exists)
							<li>
								<a href="{{ route('admin.sanatorium.sync.dictionaryentries.delete', $dictionaryentries->id) }}" class="tip" data-action-delete data-toggle="tooltip" data-original-title="{{{ trans('action.delete') }}}" type="delete">
									<i class="fa fa-trash-o"></i> <span class="visible-xs-inline">{{{ trans('action.delete') }}}</span>
								</a>
							</li>
							@endif

							<li>
								<button class="btn btn-primary navbar-btn" data-toggle="tooltip" data-original-title="{{{ trans('action.save') }}}">
									<i class="fa fa-save"></i> <span class="visible-xs-inline">{{{ trans('action.save') }}}</span>
								</button>
							</li>

						</ul>

					</div>

				</div>

			</nav>

		</header>

		<div class="panel-body">

			<div role="tabpanel">

				{{-- Form: Tabs --}}
				<ul class="nav nav-tabs" role="tablist">
					<li class="active" role="presentation"><a href="#general-tab" aria-controls="general-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/sync::dictionaryentries/common.tabs.general') }}}</a></li>
					<li role="presentation"><a href="#attributes" aria-controls="attributes" role="tab" data-toggle="tab">{{{ trans('sanatorium/sync::dictionaryentries/common.tabs.attributes') }}}</a></li>
				</ul>

				<div class="tab-content">

					{{-- Tab: General --}}
					<div role="tabpanel" class="tab-pane fade in active" id="general-tab">

						<fieldset>

							<div class="row">

								<div class="form-group{{ Alert::onForm('dictionary_id', ' has-error') }}">

									<label for="dictionary_id" class="control-label">
										<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/sync::dictionaryentries/model.general.dictionary_id_help') }}}"></i>
										{{{ trans('sanatorium/sync::dictionaryentries/model.general.dictionary_id') }}}
									</label>

									<input type="text" class="form-control" name="dictionary_id" id="dictionary_id" placeholder="{{{ trans('sanatorium/sync::dictionaryentries/model.general.dictionary_id') }}}" value="{{{ input()->old('dictionary_id', $dictionaryentries->dictionary_id) }}}">

									<span class="help-block">{{{ Alert::onForm('dictionary_id') }}}</span>

								</div>

								<div class="form-group{{ Alert::onForm('slug', ' has-error') }}">

									<label for="slug" class="control-label">
										<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/sync::dictionaryentries/model.general.slug_help') }}}"></i>
										{{{ trans('sanatorium/sync::dictionaryentries/model.general.slug') }}}
									</label>

									<input type="text" class="form-control" name="slug" id="slug" placeholder="{{{ trans('sanatorium/sync::dictionaryentries/model.general.slug') }}}" value="{{{ input()->old('slug', $dictionaryentries->slug) }}}">

									<span class="help-block">{{{ Alert::onForm('slug') }}}</span>

								</div>

								<div class="form-group{{ Alert::onForm('options', ' has-error') }}">

									<label for="options" class="control-label">
										<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/sync::dictionaryentries/model.general.options_help') }}}"></i>
										{{{ trans('sanatorium/sync::dictionaryentries/model.general.options') }}}
									</label>

									<textarea class="form-control" name="options" id="options" placeholder="{{{ trans('sanatorium/sync::dictionaryentries/model.general.options') }}}">{{{ input()->old('options', $dictionaryentries->options) }}}</textarea>

									<span class="help-block">{{{ Alert::onForm('options') }}}</span>

								</div>


							</div>

						</fieldset>

					</div>

					{{-- Tab: Attributes --}}
					<div role="tabpanel" class="tab-pane fade" id="attributes">
						@attributes($dictionaryentries)
					</div>

				</div>

			</div>

		</div>

	</form>

</section>
@stop
