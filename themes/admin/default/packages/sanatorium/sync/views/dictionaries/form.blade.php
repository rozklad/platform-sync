@extends('layouts/default')

{{-- Page title --}}
@section('title')
@parent
{{{ trans("action.{$mode}") }}} {{ trans('sanatorium/sync::dictionaries/common.title') }}
@stop

{{-- Queue assets --}}
{{ Asset::queue('validate', 'platform/js/validate.js', 'jquery') }}

{{-- Inline scripts --}}
@section('scripts')
@parent
	<script type="text/javascript">

		window.rowindex = {{ $dictionary->entries()->count() }};

		function getHtmlTemplate(index) {

			var raw = $('.row-template')[0].outerHTML;
			raw = raw.replace('ROW_POSITION', index);
			raw = raw.replace('ROW_POSITION', index);
			raw = raw.replace('hidden', '');
			raw = raw.replace('row-template', '');
			return raw;

		}

		function getHtmlOption($group) {

			var raw = $group.find('.row-option:first')[0].outerHTML;
			return raw;

		}

		function activateRowColumnActions() {

			$('.add-row').unbind('click').click(function(event){
				event.preventDefault();

				var templateHtml = getHtmlTemplate(window.rowindex);
				$('#entries').append(templateHtml);

				window.rowindex++;

				activateRowColumnActions();

			});

			$('.add-option').unbind('click').click(function(event){
				event.preventDefault();

				var $optionGroup = $(this).parents('.options-group').first(),
					optionHtml = getHtmlOption($optionGroup);

				$optionGroup.append(optionHtml);

				activateRowColumnActions();

			});

		}

		$(function(){
			activateRowColumnActions();
		});
	</script>
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

						<a class="btn btn-navbar-cancel navbar-btn pull-left tip" href="{{ route('admin.sanatorium.sync.dictionaries.all') }}" data-toggle="tooltip" data-original-title="{{{ trans('action.cancel') }}}">
							<i class="fa fa-reply"></i> <span class="visible-xs-inline">{{{ trans('action.cancel') }}}</span>
						</a>

						<span class="navbar-brand">{{{ trans("action.{$mode}") }}} <small>{{{ $dictionary->exists ? $dictionary->id : null }}}</small></span>
					</div>

					{{-- Form: Actions --}}
					<div class="collapse navbar-collapse" id="actions">

						<ul class="nav navbar-nav navbar-right">

							@if ($dictionary->exists)
							<li>
								<a href="{{ route('admin.sanatorium.sync.dictionaries.delete', $dictionary->id) }}" class="tip" data-action-delete data-toggle="tooltip" data-original-title="{{{ trans('action.delete') }}}" type="delete">
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
					<li class="active" role="presentation"><a href="#general-tab" aria-controls="general-tab" role="tab" data-toggle="tab">{{{ trans('sanatorium/sync::dictionaries/common.tabs.general') }}}</a></li>
					<li role="presentation"><a href="#attributes" aria-controls="attributes" role="tab" data-toggle="tab">{{{ trans('sanatorium/sync::dictionaries/common.tabs.attributes') }}}</a></li>
				</ul>

				<div class="tab-content">

					{{-- Tab: General --}}
					<div role="tabpanel" class="tab-pane fade in active" id="general-tab">

						<fieldset>

							<div class="row">

								<div class="col-sm-6">
									<div class="form-group{{ Alert::onForm('name', ' has-error') }}">

										<label for="name" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/sync::dictionaries/model.general.name_help') }}}"></i>
											{{{ trans('sanatorium/sync::dictionaries/model.general.name') }}}
										</label>

										<input type="text" class="form-control" name="name" id="name" placeholder="{{{ trans('sanatorium/sync::dictionaries/model.general.name') }}}" value="{{{ input()->old('name', $dictionary->name) }}}">

										<span class="help-block">{{{ Alert::onForm('name') }}}</span>

									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group{{ Alert::onForm('slug', ' has-error') }}">

										<label for="slug" class="control-label">
											<i class="fa fa-info-circle" data-toggle="popover" data-content="{{{ trans('sanatorium/sync::dictionaries/model.general.slug_help') }}}"></i>
											{{{ trans('sanatorium/sync::dictionaries/model.general.slug') }}}
										</label>

										<input type="text" class="form-control" name="slug" id="slug" placeholder="{{{ trans('sanatorium/sync::dictionaries/model.general.slug') }}}" value="{{{ input()->old('slug', $dictionary->slug) }}}">

										<span class="help-block">{{{ Alert::onForm('slug') }}}</span>

									</div>
								</div>

							</div>

						</fieldset>

						<fieldset id="entries">

							@foreach( $dictionary->entries()->get() as $key => $entry )

								<div class="row" style="padding-bottom:10px;">
									<div class="col-sm-6">
										<select name="entries[{{ $key }}][slug]" class="form-control">
											@foreach( $attributes as $optgroup => $group )

												<optgroup label="{{ trans('sanatorium/sync::common.cols.'. str_slug($optgroup)) }}">
													@foreach( $group as $attribute )
														<option value="{{ $attribute['slug'] }}" {{ $attribute['slug'] == $entry->slug ? 'selected' : '' }}>
															{{ trans('sanatorium/sync::common.cols.'. str_slug($attribute['namespace'])) }}
															{{ $attribute['name'] }}
														</option>
													@endforeach
												</optgroup>

											@endforeach
										</select>
									</div>
									<div class="col-sm-5 options-group">

										<?php $options = json_decode($entry['options']) ?>

										@foreach( $options as $subkey => $option )

											<div class="row row-option" style="padding-bottom:10px;">
												<div class="col-sm-10">
													<input type="text" name="entries[{{ $key }}][options][]" class="form-control" value="{{ $option }}">
												</div>
												<div class="col-sm-2">
													<button class="btn btn-default add-option">
														<i class="fa fa-plus"></i>
													</button>
												</div>
											</div>

											@endforeach

									</div>
									<div class="col-sm-1 text-right">
										<button class="btn btn-primary add-row">
											<i class="fa fa-plus"></i>
										</button>
									</div>
								</div>

							@endforeach

							<div class="row hidden row-template" style="padding-bottom:10px;">
								<div class="col-sm-6">
									<select name="entries[ROW_POSITION][slug]" class="form-control">
										@foreach( $attributes as $optgroup => $group )

											<optgroup label="{{ trans('sanatorium/sync::common.cols.'. str_slug($optgroup)) }}">
												@foreach( $group as $attribute )
													<option value="{{ $attribute['slug'] }}">
														{{ trans('sanatorium/sync::common.cols.'. str_slug($attribute['namespace'])) }}
														{{ $attribute['name'] }}
													</option>
												@endforeach
											</optgroup>

										@endforeach
									</select>
								</div>
								<div class="col-sm-5 options-group">

									<div class="row row-option" style="padding-bottom:10px;">
										<div class="col-sm-10">
											<input type="text" name="entries[ROW_POSITION][options][]" class="form-control">
										</div>
										<div class="col-sm-2">
											<button class="btn btn-default add-option">
												<i class="fa fa-plus"></i>
											</button>
										</div>
									</div>

								</div>
								<div class="col-sm-1 text-right">
									<button class="btn btn-primary add-row">
										<i class="fa fa-plus"></i>
									</button>
								</div>
							</div>

						</fieldset>

						<fieldset>
							<div class="row">
								<div class="col-sm-12 text-center">
									<button class="btn btn-primary add-row">
										<i class="fa fa-plus"></i>
									</button>
								</div>
							</div>
						</fieldset>

					</div>

					{{-- Tab: Attributes --}}
					<div role="tabpanel" class="tab-pane fade" id="attributes">
						@attributes($dictionary)
					</div>

				</div>

			</div>

		</div>

	</form>

</section>
@stop
