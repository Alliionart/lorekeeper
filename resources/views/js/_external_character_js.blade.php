<script>
    $(document).ready(function() {
        var $externalCharacters = $('#externalCharactersBody');
        var $extCharactersRow = $('#externalCharactersBody').find('.external-character-row');
        var $extCharacterSelect = $('#external-character').find('.external-character-row');

        attachRemoveListener($('#externalCharactersBody .remove-ext-character-button'));

        $('#addExternalCharacter').on('click', function(e) {
            e.preventDefault();
            var $clone = $extCharacterSelect.clone();
            $externalCharacters.append($clone);
            attachRemoveListener($clone.find('.remove-ext-character-button'));
        });

        function attachRemoveListener(node) {
            node.on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().remove();
            });
        }

    });
</script>
