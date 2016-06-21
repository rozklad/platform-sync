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

            console.log(number);

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

                $('#results').append('<div class="well">' + msg.number + '/' + msg.total + '</div>');

                if ( msg.done == true ) {
                    alert('Finised');
                } else {
                    number = parseInt(msg.number) + 1;
                    importItem(number);
                }


            }).error(function(msg){
                alert(msg);
                console.log(msg);
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

    <div id="results">

    </div>

@stop

