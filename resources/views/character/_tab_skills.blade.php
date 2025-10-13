@if ($skills)
    <div class="row">
        @foreach ($skills as $skill)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ $skill->skill->name }}</h5>
                    </div>
                    <div class="card-body">
                        {!! $skill->skill->parsed_description !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p>This character has no skills.</p>
@endif

<script>
    $(function() {
        $('.children-skill ul').hide();
        $('.children-skill>ul').show();
        $('.children-skill ul.active').show();
        $('.children-skill li').on('click', function(e) {
            var children = $(this).find('> ul');
            if (children.is(":visible")) children.hide('fast').removeClass('active');
            else children.show('fast').addClass('active');
            e.stopPropagation();
        });
    });
</script>
