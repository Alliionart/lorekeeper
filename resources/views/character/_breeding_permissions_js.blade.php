@if (isset($character) && Auth::check() && (Auth::user()->id == $character->user_id || Auth::user()->isStaff))
    <script>
        $(document).ready(function() {
            $('.create-breeding-permission').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('character/' . $character->slug . '/breeding-permissions/new') }}", 'Create Breeding Permission');
            });
            $('.refresh-permissions').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('character/' . $character->slug . '/breeding-permissions/refresh') }}", 'Refresh Breeding Permissions');
            });
        });
    </script>
@endif
@if (Auth::check())
    <script>
        $(document).ready(function() {
            $('.transfer-breeding-permission').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('character') }}/" + $(this).data('slug') + "/breeding-permissions/" + $(this).data('id') + "/transfer", 'Transfer Breeding Permission');
            });
        });
    </script>
@endif
@if (Auth::check() && Auth::user()->hasPower('manage_characters'))
    <script>
        $(document).ready(function() {
            $('.use-breeding-permission').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/character') }}/" + $(this).data('slug') + "/breeding-permissions/" + $(this).data('id') + "/use", 'Mark Breeding Permission as Used');
            });
        });
    </script>
@endif
