@extends('layouts/default')

{{-- Page title --}}
@section('title')
@parent
{{ trans('sanatorium/sync::common.title') }}
@stop

{{-- Queue assets --}}
{{ Asset::queue('moment', 'moment/js/moment.js', 'jquery') }}
{{ Asset::queue('underscore', 'underscore/js/underscore.js', 'jquery') }}
{{ Asset::queue('manage', 'sanatorium/sync::js/script.js', 'jquery') }}
{{-- Asset::queue('jquery-ui', 'sanatorium/sync::jquery-ui/jquery-ui.min.js', 'jquery') --}}
{{ Asset::queue('dynatree', 'sanatorium/sync::jquery-dynatree/skin/ui.dynatree.css', 'jquery') }}
{{-- Asset::queue('dynatree', 'sanatorium/sync::jquery-dynatree/jquery.dynatree.min.js', 'jquery') --}}

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

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.sanatorium.sync.upload') }}">

{{-- Grid --}}
<section class="panel panel-default panel-grid">

	{{-- Grid: Header --}}
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

					<span class="navbar-brand">{{{ trans('sanatorium/sync::common.title') }}}</span>

				</div>

				<div class="collapse navbar-collapse" id="actions">

					<ul class="nav navbar-nav navbar-right">

						<li>

							<button type="submit" class="btn btn-primary navbar-btn" data-toggle="tooltip" title="{{ trans('action.upload') }}">
								
								<i class="fa fa-save"></i>
								
								<span class="visible-xs-inline">{{ trans('action.upload') }}</span>

							</button>

						</li>

					</ul>

				</div>

			</div>

		</nav>

	</header>

	<div class="panel-body">

		<div class="col-sm-6">

			<br>

			<input type="hidden" name="setup_url" value="{{ route('admin.sanatorium.sync.setup') }}">

			<fieldset>

				<legend>{{{ trans('sanatorium/sync::common.import') }}}</legend>

				<input type="hidden" name="_token" value="{{ csrf_token() }}">

				<div class="form-group">

					<label class="control-label" for="import">

						{{{ trans('sanatorium/sync::common.import') }}}

					</label>

					<input type="file" name="import" id="import">

				</div>

				<div class="form-group">

					<label class="control-label" for="encoding">

						{{{ trans('sanatorium/sync::common.encoding') }}}

					</label>

					<select name="encoding" class="form-control">
						<option value="utf8">UTF-8</option>
					</select>

				</div>

				<div class="form-group">

					<label class="control-label" for="delimiter">

						{{{ trans('sanatorium/sync::common.delimiter') }}}

					</label>

					<input name="delimiter" value="," id="delimiter" class="form-control">

				</div>

				<div class="form-group">

					<label class="control-label" for="text_delimiter">

						{{{ trans('sanatorium/sync::common.text_delimiter') }}}

					</label>

					<select name="text_delimiter" class="form-control">
						<option value="quote">"</option>
					</select>

				</div>

				<div class="form-group">

					<label class="control-label" for="newline">

						{{{ trans('sanatorium/sync::common.newline') }}}

					</label>

					<select name="newline" class="form-control">
						<option value="\n">{{{ trans('sanatorium/sync::common.newline_options.breakline') }}}</option>
					</select>

				</div>

				<div class="form-group text-center hidden">

					<button type="submit" class="btn btn-success">{{ trans('action.upload') }}</button>

				</div>

			</fieldset>

		</div>

		<div class="col-sm-6">

			<br>

			<fieldset>
				
				<legend>{{{ trans('sanatorium/sync::common.preview') }}}</legend>
				
				<div class="form-group checkbox">
	
					<input type="checkbox" name="dictionary" checked="checked" id="dictionary">

					<label for="dictionary">
						{{ trans('sanatorium/sync::common.options.use_dictionary') }}
					</label>
	
				</div>

				<ul class="dynatree-container" id="results"></ul>

				<script type="text/template" id="tree" data-template="results">

					<% _.each(results.structure, function(r) { %>

						<li>
							<span class="dynatree-icon"></span>
							<%= r.title %>
							<select name="types[<%= r.title %>]">
								<option value="ignore">{{ trans('sanatorium/sync::common.functions.ignore') }}</option>
								<option value="create_attribute">{{ trans('sanatorium/sync::common.functions.create_attribute') }}</option>
								<% _.each(results.attributes, function(a) { %>
								<option value="attribute.<%= a.slug %>"><%= a.name %></option>
								<% }); %>
								<% _.each(results.functions, function(f) { %>
								<option value="functions.<%= f %>"><%= f %></option>
								<% }); %>
								<% _.each(results.relations, function(r) { %>
								<option value="relations.<%= r %>"><%= r %></option>
								<% }); %>
							</select>
							<% if (typeof r.children != 'undefined') { %>
								<ul>
								<% _.each(r.children, function(c) { %>
									<li>
										<span class="dynatree-icon"></span>
										<%= c.title %>
									</li>
								<% }); %>
								</ul>
							<% } %>
						</li>

					<% }); %>

				</script>

			</fieldset>
		
		</div>

	</div>

	<table class="table table-responsive table-striped" id="results-table"></table>

	<script type="text/template" id="table" data-template="results">

		<thead>
			<tr>
				<% _.each(results.structure, function(r) { %>
					<th>
						<%= r.title %>
					</th>
				<% }); %>
			</tr>
			<tr>
				<% _.each(results.structure, function(r) { %>
					<td style="background-color:#ddd;">
						<select name="types[<%= r.title %>]" style="width:100%;">
							<option value="ignore">{{ trans('sanatorium/sync::common.functions.ignore') }}</option>
							<option value="create_attribute">{{ trans('sanatorium/sync::common.functions.create_attribute') }}</option>
							<% _.each(results.attributes, function(a) { %>
								<% if (a.slug == r.title || a.name == r.title) { %>
									<option value="attribute.<%= a.slug %>" selected><%= a.name %></option>
								<% } else { %>
									<option value="attribute.<%= a.slug %>"><%= a.name %></option>
								<% } %>
							<% }); %>
							<% _.each(results.functions, function(f) { %>
							<option value="functions.<%= f %>"><%= f %></option>
							<% }); %>
							<% _.each(results.relations, function(r) { %>
							<option value="relations.<%= r %>"><%= r %></option>
							<% }); %>
						</select>
					</td>
				<% }); %>
			</tr>
		</thead>
		<tbody>
			<% _.each(results.data, function(r) { %>
				<tr>
					<% _.each(results.structure, function(s) { %>
						<% _.each(r, function(c, k) { %>
							<% if ( k == s.title ) { %>
							<td>
								<%= c %>
							</td>
							<% } %>
						<% }); %>
					<% }); %>

				</tr>
			<% }); %>
		</tbody>


	</script>

</section>

	@foreach($formatters as $key => $formatter)
		<div class="panel">

			<header class="panel-heading">

				<div class="panel-title">{{{ trans('sanatorium/sync::common.export') }}}</div>

			</header>

			<div class="panel-body">
			
				<div class="row">
			
					<div class="col-xs-1 text-center">
			
						<a href="{{ $formatter['url'] }}" target="_blank" style="font-size:48px;">
			
							<i class="{{ $formatter['icon'] }}"></i>
			
						</a>
			
					</div>
			
					<div class="col-xs-10">
			
						<h4 class="media-heading">
			
							<a href="{{ $formatter['url'] }}" target="_blank">
			
								{{ $formatter['title'] }}
			
							</a>
			
						</h4>
			
						<p>{{ $formatter['description'] }}</p>
			
						<p>{{ trans('sanatorium/sync::model.created') }} {{ $formatter['created'] }}</p>
			
					</div>

					<div class="col-xs-1 text-center">
						
						<div class="btn-group-vertical" role="group">
						
							<a href="{{ $formatter['url'] }}" target="_blank" class="btn btn-primary">
			
								{{ trans('action.download') }}
			
							</a>

							<a href="{{ $formatter['refresh_url'] }}" class="btn btn-default">
			
								{{ trans('sanatorium/sync::model.action.refresh') }}
			
							</a>

						</div>

					</div>
			
				</div>
			
			</div>
		
		</div>
	@endforeach

	@if (count($formatters) > 0)
	<div class="panel">

		<header class="panel-heading">

			<div class="panel-title">{{{ trans('sanatorium/sync::common.help.refresh.title') }}}</div>

		</header>

		<div class="panel-body">
			
			<div class="row">

				<div class="col-sm-6">

					<p>

						{{{ trans('sanatorium/sync::common.help.refresh.description') }}}

					</p>

				</div>

				<div class="col-sm-6">

					<p>

						{{{ trans('sanatorium/sync::common.help.refresh.cron') }}}

						<pre>{{ route('sanatorium.sync.export.refresh', ['type' => 'all']) }}</pre>

					</p>

				</div>

			</div>

		</div>

	</div>
	@endif

</form>

@stop

