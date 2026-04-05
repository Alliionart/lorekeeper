{!! Form::open(['url' => 'characters/class/edit/' . $character->id]) !!}
<div class="repeater-wrapper">
    <div class="row repeater-row template">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Class Type') !!}
                {!! Form::select('class_type_id[]', $class_types, null, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group class-refesh">
                {!! Form::label('Class') !!}
                {!! Form::select('class_id[]', [], null, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group ability-refesh">
                {!! Form::label('Ability') !!}
                {!! Form::select('chosen_ability[]', [], null, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
    </div>
    <div class="row repeater-row">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Class Type') !!}
                {!! Form::select('class_type_id[]', $class_types, null, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group class-refesh">
                {!! Form::label('Class') !!}
                {!! Form::select('class_id[]', [], $character->class_id, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group ability-refesh">
                {!! Form::label('Ability') !!}
                {!! Form::select('chosen_ability[]', [], null, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
    </div>
</div>
<button type="button" class="btn btn-secondary" id="addRow">Add Row</button>

<div class="text-right">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        var $rowTemplate = $('.repeater-row.template').clone().removeClass('template');
        $('.repeater-row.template').remove();
        $(".selectize").selectize();

        $(document).on('change', 'select[name="class_type_id[]"]', function() {
            var $row = $(this).closest('.repeater-row');
            refreshClasses($row, $(this), $(this).val());
        });

        $('#addRow').click(function() {
            var $newRow = $rowTemplate.clone();
            $newRow.find('.selectize').selectize();
            $('.repeater-wrapper').append($newRow);
        });

        function refreshClasses($row, $container, class_type_id) {
            var $class_id_container = $row.find('.class-refesh');
            var id = {{ $character->id }};

            $.ajax({
                type: "GET",
                url: "{{ url('character/' . $character->slug . '/get-class-options') }}?id=" + id + '&class_type_id=' + class_type_id,
                dataType: "text"
            }).done(function(res) {
                $class_id_container.html(res);
                $class_id_container.find('.selectize').selectize();
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        };

    });
</script>
