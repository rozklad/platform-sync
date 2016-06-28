@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @parent
    {{ trans('sanatorium/sync::common.title') }}
@stop

{{-- Queue assets --}}
{{ Asset::queue('moment', 'moment/js/moment.js', 'jquery') }}
{{ Asset::queue('underscore', 'underscore/js/underscore.js', 'jquery') }}

{{-- Inline scripts --}}
@section('scripts')
    <script type="text/javascript">

        function importItem(number) {

            var dictionary = {{ $dictionary }},
                types = {
                    @foreach( $types as $key => $value )
                        "{{ $key }}" : "{{ $value }}",
                    @endforeach
                };

            $.ajax({
                type: 'POST',
                url: '{{ route('sanatorium.sync.export.step') }}',
                data: {
                    number: number,
                    types: types,
                    dictionary: dictionary,
                    filename: "{{ $filename }}"
                }
            }).success(function(msg){

                $('#results').prepend('<div class="well well-sm"><img src="' + msg.img + '"> <p>' + msg.number + '/' + msg.total + '</p></div>');

                if ( msg.done == true ) {
                    alert('Finised');
                } else {
                    number = parseInt(msg.number) + 1;
                    importItem(number);
                }

                var percent = (msg.number/msg.total) * 100;

                $('#overall-progress .progress-bar').css('width', percent + '%');

            }).error(function(msg){

                alert('Import failed, see console for more information');
                console.log(msg);

                $('#results').prepend('<div class="well" data-number="'+number+'"><button type="button" class="btn btn-default" data-sync="retry">Retry</button> <button type="button" class="btn btn-default" data-sync="continue">Continue</button></div>');

                $('[data-sync]').not('.activated').click(function(event){

                    var operation = $(this).data('sync'),
                        number = $(this).parents('.well:first').data('number');

                    switch( operation ) {
                        case 'retry':
                            importItem( number );
                        break;

                        case 'continue':
                            importItem( number + 1 );
                        break;
                    }

                }).addClass('activated');


            });
        }

        $(function(){
            importItem(0);
        });
    </script>
    @parent
@stop

{{-- Inline styles --}}
@section('styles')
    @parent
@stop

{{-- Page content --}}
@section('page')

    <div class="panel panel-default">
        <header class="panel-heading">

        </header>
        <div class="panel-body">
            <div class="progress" id="overall-progress">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                    <span class="sr-only">0% Complete</span>
                </div>
            </div>

            <div id="results">

            </div>
        </div>
    </div>



@stop

