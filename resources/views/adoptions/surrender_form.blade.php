@extends('adoptions.layout')

@section('title') Surrender Character @endsection

@section('content')
{!! breadcrumbs([$adoption->name => 'adoptions', 'Surrender' => 'surrender']) !!}
<h1>
    Surrender Character
</h1>
@if(!Settings::get('is_surrenders_open'))
<div class="alert alert-danger">Surrenders are currently closed</div>
@else
<div class="alert alert-warning">Please note that by surrendering your characters you acknowledge they will be sold for onsite currency and retrieval after the form has been approved may not be possible</div>

{!! Form::open(['url' => 'surrenders/new/post']) !!}

<div class="card mb-3 stock">
    <div class="card-body">
        <div class="form-group">
            {!! Form::label('character_id', 'Character or Genotype') !!}
            {!! Form::select('character_id', $characters, null, ['class' => 'form-control stock-field selectize', 'data-name' => 'character_id']) !!}
        </div>
        <div class="card mb-3">
            <h4 class="card-header">Surrender Calculator</h4>
            <div class="card-body">
                <div id="surrender-calculator">
                    <p>Select a character or genotype to see their estimated worth.</p>
                </div>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('notes', 'Additional Notes (optional)') !!}
            {!! Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => 'Include any extra neccessary details, as well as any character pages such as Toyhouse etc.']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('worth', 'Current Worth') !!} {!! add_help('Suggested worth does not influence amount of currency given, but gives admins insight to see if the grant has malfunctioned.') !!}
            {!! Form::number('worth', null, ['class' => 'form-control', 'readonly' => true]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('currency_id', 'Currency') !!} {!! add_help('If you do not want to input a worth, just leave this field as default.') !!}
            {!! Form::select('currency_id', $currencies, $primaryCurrency ?? null , ['class' => 'form-control', 'placeholder' => 'Select Currency type']) !!}
        </div>
        
        <div class="text-right">
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
</div>
{!! Form::close() !!}
@endif
@endsection
@section('scripts')
    @parent
    <script>
    $(document).ready(function() {

        $('.selectize').selectize();

        if({{ $primaryCurrency ? 'true' : 'false' }}) {
            $('#currency_id option:not(:selected)').prop('disabled', true);
        }

        function updateSurrenderCalculator() {
            var characterId = $('[data-name="character_id"]').val();
            if(characterId) {
                $.ajax({
                    url: '/surrender/get-value/' + characterId,
                    method: 'GET',
                    success: function(data) {
                        formatCalculatorBreakdown(data.breakdown, data.estimated_worth);
                    },
                    error: function() {
                        $('#surrender-calculator').html('<p class="text-danger">Error retrieving surrender calculation. Please try again later.</p>');
                    }
                });
            } else {
                $('#surrender-calculator').html('<p>Select a character or genotype to see their estimated worth.</p>');
            }
        }

        $('[data-name="character_id"]').change(function() {
            updateSurrenderCalculator();
        });

        function formatCalculatorBreakdown($breakdown, $total) {
            var html = '<ul>';
            Object.entries($breakdown).forEach(([category, value]) => {
                html += '<h5 class="mt-3">'+ category +'</h5><ul>';
                Object.entries(value).forEach(([item, itemValue]) => {
                    if(category !== 'markings' && category !== 'skills'){
                        html += '<li><strong>' + item + ':</strong> ' + itemValue + '</li>';
                    } else {
                        html += '<li><strong>' + item + ' (x'+ itemValue.count +'):</strong> ' + itemValue.cost + '</li>';
                    }
                });
                html += '</ul>';
            });
            html += '</ul><hr><h5>Estimated Worth: ' + $total + '</h5>';
            $('#surrender-calculator').html(html);
            $('[name="worth"]').val($total);
        }
    });
    </script>
@endsection
