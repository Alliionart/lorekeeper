@extends('layouts.app')


@section('title')
    Submit a Breeding
@endsection


@section('content')
    {!! breadcrumbs(['Breeding' => 'breeding']) !!}
    <h1>Submit a Breeding</h1>

    <div class="site-page-content parsed-text">
        <p>To submit your breeding select both characters, and any items you'd like to use. Make sure you review before you submit!</p>
        {!! Form::open(['url' => 'submit-breeding']) !!}

        <div class="alert warning alert-warning p-2 border border-warning hide">
            <strong>Warning:</strong> You cannot select the same slot for both parents. Please choose different slots.
        </div>
        <div class="alert inbreeding alert-danger p-2 border border-danger hide">
            <strong>Inbreeding:</strong> There is inbreeding through <span></span>, ensure you are ok with breeding this pair.
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>Parent #1</h4>
                    </div>
                    <div class="card-body">

                        <pre class="hide" style="background-color:#ccc">
                            {{ print_r($permissions, true) }}
                        </pre>

                        {!! Form::label('Select Slot') !!} {!! add_help('Select a slot from your breeding permissions.') !!}
                        {!! Form::select('permission_1', $permissions, null, ['class' => 'form-control characterSelect', 'id' => 'permission_1', 'slot_id' => '1']) !!}

                        <div id="character_1_display" class="mt-3 rounded p-3 border border-secondary hide">
                            <div class="row">
                                <div class="col-md-3">
                                    <img src="" alt="" class="img-fluid" />
                                </div>
                                <div class="col-md-9 d-flex flex-column justify-content-center">
                                    <h4>Character Name</h4>
                                    <ul class="p-0 list-unstyled">
                                        <li><strong>Species: </strong><span class="species">Species</span></li>
                                        <li><strong>Subtype: </strong><span class="subtype">Subtype</span></li>
                                        <li><strong>Markings: </strong><span class="markings">Markings</span></li>
                                        <li><strong>Traits: </strong>
                                            <ul class="traits"></ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4>Parent #2</h4>
                    </div>
                    <div class="card-body">

                        <pre class="hide" style="background-color:#ccc">
                            {{ print_r($permissions, true) }}
                        </pre>

                        {!! Form::label('Select Slot') !!} {!! add_help('Select a slot from your breeding permissions.') !!}
                        {!! Form::select('permission_2', $permissions, null, ['class' => 'form-control characterSelect', 'id' => 'permission_2', 'slot_id' => '2']) !!}

                        <div id="character_2_display" class="mt-3 rounded p-3 border border-secondary hide">
                            <div class="row">
                                <div class="col-md-3">
                                    <img src="" alt="" class="img-fluid" />
                                </div>
                                <div class="col-md-9 d-flex flex-column justify-content-center">
                                    <h4>Character Name</h4>
                                    <ul class="p-0 list-unstyled">
                                        <li><strong>Species: </strong><span class="species">Species</span></li>
                                        <li><strong>Subtype: </strong><span class="subtype">Subtype</span></li>
                                        <li><strong>Markings: </strong><span class="markings">Markings</span></li>
                                        <li><strong>Traits: </strong>
                                            <ul class="traits"></ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Breeding Modifiers</h4>
            </div>
            <div class="card-body">
                <p>Select any modifiers for your breeding. Some may require item usage.</p>
            </div>
        </div>


        <div class="text-right">
            {!! Form::submit('Submit Breeding', ['class' => 'btn btn-primary mt-4']) !!}
        </div>

        {!! Form::close() !!}
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('select').selectize({
                multiple: false,
            });
        });

        $('.characterSelect').change(function() {
            getCharacter(this);
            checkSlots();
        });

        var lineage = [];
        var inbreeding = false;

        function getCharacter($selector) {
            var permId = $($selector).val();
            var slotId = $($selector).attr('slot_id');
            var displayDiv = $('#character_' + slotId + '_display');
            $.ajax({
                type: "GET",
                url: "/breeding/permission/?permission_id=" + permId + "&slot_id=" + slotId,
                dataType: "json",
            }).done(function(data) {
                console.log(data);
                displayDiv.find('img').attr('src', data.character.image);
                displayDiv.find('h4').text(data.character.name);
                displayDiv.find('.species').html(data.character.species);
                displayDiv.find('.subtype').html(data.character.subtype);
                displayDiv.find('.markings').text(data.character.markings);
                handleCharacterLineage(data.character.lineage);
                $.each(data.character.traits, function(i, val) {
                    displayDiv.find('.traits').append('<li><strong>' + i + ': </strong>' + val + '</li>');
                });
                displayDiv.removeClass('hide');
                //displayDiv.after('<pre style="background-color:#eee">' + JSON.stringify(data, null, 2) + '</pre>')
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        }

        function handleCharacterLineage(obj) {
            Object.keys(obj).forEach(key => {
                if (lineage.includes(key)) {
                    inbreeding = true;
                    $('.alert.inbreeding span').html(obj[key]);
                    $('.alert.inbreeding').removeClass('hide');
                } else {
                    lineage.push(key);
                }
            });
        }

        function checkSlots() {
            var slot1 = $('#permission_1').val();
            var slot2 = $('#permission_2').val();
            if (slot1 != 0 && slot2 != 0 && slot1 != slot2) {
                $(':input[type="submit"]').prop('disabled', false);
                $('.alert.warning').addClass('hide');
            } else {
                $(':input[type="submit"]').prop('disabled', true);
                $('.alert.warning').removeClass('hide');
            }
        }
    </script>
@endsection
