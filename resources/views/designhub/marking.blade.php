@extends('layouts.app')


@section('title')
    {!! $marking->name !!}
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'design-hub', $marking->name => $marking->url]) !!}
    <x-admin-edit title="Marking" :object="$marking" />
    <h1>{{ $marking->name }} 
        @if($marking->recessive)
            <span style="text-transform:none;">({{ $marking->recessive }}/{{ $marking->dominant }})</span>
        @endif
    </h1>
    <p>{{ $marking->short_description }}</p>

    <div class="site-page-content parsed-text">
        {!! $marking->description !!}
    </div>

    @if (count($carriers) > 0)
        <div class="site-page-content parsed-text">
            <div class="card mb-4 rounded mt-4">
                <h5 class="card-header">Carriers</h5>
                <div class="card-body">
                    @foreach ($carriers as $carrier)
                        <div class="card carrier-card mb-4 rounded">
                            <h5 class="card-header">{{ $carrier->name }}</h5>
                            <div class="card-body">
                                {!! $carrier->description !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('img.pop').each(function(i, e) {
                if (!$(this).parent().is('a')) {
                    $(this).wrap('<a href="' + $(this).attr('src') + '" data-lightbox="entry" ></a>');
                } else {
                    $a = $(this).parent('a');
                    $a.addClass('btn-secondary');
                    $(this).unwrap();
                    $(this).wrap('<div class="lightbox-wrapper"></div>');
                    $(this).parent('.lightbox-wrapper').append($a);
                    $a.text('Open Link');
                }
            });

            var currentHostname = window.location.hostname;

            $('a').each(function() {
                var host = this.hostname;

                if (this.href && host !== currentHostname) {
                    $(this).attr('target', '_blank');
                    $(this).attr('rel', 'noopener noreferrer');
                }
            });
        });
    </script>
@endsection
