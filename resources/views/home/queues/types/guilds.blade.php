@php
    $editing = $editing ?? false;
    $data = $data ?? [];
@endphp

<div class="card mb-3">
    <div class="card-header h2">Guild Creation</div>
    <div class="card-body">
        @if ($editing)
            <div class="form-group">
                {!! Form::label('guild_name', 'Guild Name') !!} {!! add_help('The public name for the guild.') !!}
                {!! Form::text('guild_name', old('guild_name') ?: ($data['guild_name'] ?? null), ['class' => 'form-control', 'maxlength' => 100]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('guild_description', 'Description (Optional)') !!}
                {!! Form::textarea(
                    'guild_description',
                    old('guild_description') ?: ($data['guild_description'] ?? null),
                    ['class' => 'form-control wysiwyg'],
                ) !!}
            </div>

            <div class="form-group">
                {!! Form::label('logo', 'Guild Logo (Optional)') !!} {!! add_help('PNG/GIF, max 200KB.') !!}
                {!! Form::file('logo', ['class' => 'form-control']) !!}
                @if (isset($data['logo_url']) && $data['logo_url'])
                    <div class="mt-2">
                        <div class="text-muted small mb-1">Current uploaded logo preview:</div>
                        <img src="{{ $data['logo_url'] }}" alt="Guild Logo" style="max-height:120px;" loading="lazy" />
                    </div>
                @endif
            </div>
        @else
            <dl class="row mb-0">
                <dt class="col-sm-3">Guild Name</dt>
                <dd class="col-sm-9">{{ $data['guild_name'] ?? '—' }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">
                    @if (isset($data['parsed_guild_description']) && $data['parsed_guild_description'])
                        {!! $data['parsed_guild_description'] !!}
                    @else
                        {{ $data['guild_description'] ?? '—' }}
                    @endif
                </dd>
            </dl>

            @if (isset($data['logo_url']) && $data['logo_url'])
                <div class="mt-3">
                    <div class="text-muted small mb-1">Logo</div>
                    <img src="{{ $data['logo_url'] }}" alt="Guild Logo" style="max-height:180px;" loading="lazy" />
                </div>
            @endif

            @if (isset($data['guild_id']) && $data['guild_id'])
                <div class="alert alert-success mt-3 mb-0">
                    Guild created: <a href="{{ url(__('guilds.guilds').'/'.$data['guild_id']) }}">#{{ $data['guild_id'] }}</a>
                </div>
            @endif
        @endif
    </div>
</div>
