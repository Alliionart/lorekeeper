<script>
    $(document).ready(function() {
        var $addGuild = $('#addGuild');
        var $components = $('#guildComponents');
        var $rewards = $('#rewards');
        var $guilds = $('#guilds');
        var count = 0;

        $('#guilds .submission-guild').each(function(index) {
            attachListeners($(this));
        });

        $addGuild.on('click', function(e) {
            e.preventDefault();
            $clone = $components.find('.submission-guild').clone();
            attachListeners($clone);
            attachRewardTypeListener($clone.find('.guild-rewardable-type'));
            $guilds.append($clone);
            $clone.find('.guild-code').selectize();
            count++;
        });

        function attachListeners(node) {
            node.find('.guild-code').on('change', function(e) {
                var $parent = $(this).parent().parent().parent().parent();
                $parent.find('.guild-image-loaded').load('{{ url('submissions/new/guild') }}/' + $(this).val(), function(response, status, xhr) {
                    $parent.find('.guild-image-blank').addClass('hide');
                    $parent.find('.guild-image-loaded').removeClass('hide');
                    $parent.find('.guild-rewards').removeClass('hide');
                    updateRewardNames(node, node.find('.guild-info').data('id'));
                });
            });
            node.find('.remove-guild').on('click', function(e) {
                e.preventDefault();
                $(this).parent().parent().parent().remove();
            });
            node.find('.add-reward').on('click', function(e) {
                e.preventDefault();
                $clone = $components.find('.guild-reward-row').clone();
                $clone.find('.remove-reward').on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
                updateRewardNames($clone, node.find('.guild-info').data('id'));
                $(this).parent().parent().find('.guild-rewards').append($clone);
                attachRewardTypeListener(node.find('.guild-rewardable-type'));
            });
            attachRewardTypeListener(node.find('.guild-rewardable-type'));
        }

        function attachRewardTypeListener(node) {
            node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.lootDivs');

                $cell.children().addClass('hide');
                $cell.children().children().val(null);

                if (val == 'Item') {
                    $cell.children('.guild-items').addClass('show');
                    $cell.children('.guild-items').removeClass('hide');
                    $cell.children('.guild-items');
                } else if (val == 'Currency') {
                    $cell.children('.guild-currencies').addClass('show');
                    $cell.children('.guild-currencies').removeClass('hide');
                } else if (val == 'LootTable') {
                    $cell.children('.guild-tables').addClass('show');
                    $cell.children('.guild-tables').addClass('show');
                    $cell.children('.guild-tables').removeClass('hide');
                }
            });
        }

        function updateRewardNames(node, id) {
            node.find('.guild-rewardable-type').attr('name', 'guild_rewardable_type[' + id + '][]');
            node.find('.guild-rewardable-quantity').attr('name', 'guild_rewardable_quantity[' + id + '][]');
            node.find('.guild-currency-id').attr('name', 'guild_rewardable_id[' + id + '][]');
            node.find('.guild-item-id').attr('name', 'guild_rewardable_id[' + id + '][]');
            node.find('.guild-table-id').attr('name', 'guild_rewardable_id[' + id + '][]');
        }

    });
</script>
