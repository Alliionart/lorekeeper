<script>
    $(document).on('ready', function() {
        $('#markings').selectize();
    });

    $('#add-marking').on('click', function(e) {
        e.preventDefault();
        addMarkingRow();
    });
    $('.remove-marking').on('click', function(e) {
        e.preventDefault();
        removeMarkingeRow($(this));
    })

    function addMarkingRow() {
        var $clone = $('.marking-row.hide').clone();
        $('#markingList').append($clone);
        $clone.removeClass('hide');
        $clone.addClass('d-flex');
        $clone.find('.remove-marking').on('click', function(e) {
            e.preventDefault();
            removeMarkingRow($(this));
        })
        @if (config('lorekeeper.extensions.organised_traits_dropdown'))
            $clone.find('.marking-select').selectize({
                render: {
                    item: featureSelectedRender
                }
            });
        @else
            $clone.find('.marking-select').selectize();
        @endif
    }

    function removeMarkingRow($trigger) {
        $trigger.parent().remove();
    }

    function markingSelectedRender(item, escape) {
        return '<div><span>' + escape(item["text"].trim()) + ' (' + escape(item["optgroup"].trim()) + ')' + '</span></div>';
    }

    //If Chimera
    $('#is_chimera').on('change', function(e) {
        isChimera = $('#is_chimera').is(':checked');

        if (isChimera) {
            $('[connect="is_chimera"]').show();
        } else {
            $('[connect="is_chimera"]').hide();
        }
    });
</script>
