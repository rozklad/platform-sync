@extends('layouts/default')

{{-- Page title --}}
@section('title')
@parent
{{ trans('sanatorium/sync::common.title') }}
@stop

{{-- Queue assets --}}
{{ Asset::queue('moment', 'moment/js/moment.js', 'jquery') }}
{{ Asset::queue('underscore', 'underscore/js/underscore.js', 'jquery') }}
{{-- Asset::queue('manage', 'sanatorium/sync::js/script.js', 'jquery') --}}

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

			</div>

		</nav>

	</header>

	<div class="panel-body">

		<div class="row">
			
			<div class="col-sm-12">
				
				<form method="POST" enctype="multipart/form-data" action="{{ route('admin.sanatorium.sync.upload') }}">
						
					<input type="hidden" name="_token" value="{{ csrf_token() }}">

					<table class="table">
					@foreach($structure as $key => $value)
						<tr>
							<th class="col-sm-4">{{ $key }}</th>
							<td class="col-sm-4">
								{{ $value }}
							</td>
							<td class="col-sm-4">
								<select name="type[{{ $key }}]" class="form-control">
									<option value="ignore">{{{ trans('sanatorium/sync::common.functions.ignore') }}}</option>
									@foreach( $functions as $function )
									<option value="{{{ $function }}}">{{{ $function }}}</option>
									@endforeach
								</select>
							</td>
						</tr>
					@endforeach
					</table>

				</form>

			</div>

		</div>
		
		

	</div>

</section>

@stop
