@extends('layouts.app')

@section('title')
    {{ ucwords(__('guilds.guilds')) }}
@endsection

@section('sidebar')
    @include('guilds._sidebar')
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds'), $guild->name => __('guilds.guilds') . '/view/' . $guild->id, 'Bank' => 'bank']) !!}

    <h1>{{ $guild->name }}'s Bank</h1>

    <h3>
        @if (Auth::check() && Auth::user()->hasPower('edit_inventories'))
            <a href="#" class="float-right btn btn-outline-info btn-sm" id="grantButton" data-toggle="modal" data-target="#grantModal"><i class="fas fa-cog"></i> Admin</a>
        @endif
        Currencies
    </h3>

    @if (count($currencies))
        <div class="card mb-4">
            <ul class="list-group list-group-flush">

                @foreach ($currencies as $currency)
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-lg-2 col-md-3 col-6 text-right">
                                <strong>
                                    <a href="{{ $currency->url }}">
                                        {{ $currency->name }}
                                        @if ($currency->abbreviation)
                                            ({{ $currency->abbreviation }})
                                        @endif
                                    </a>
                                </strong>
                            </div>
                            <div class="col-lg-10 col-md-9 col-6">
                                {{ $currency->quantity }} @if ($currency->has_icon)
                                    {!! $currency->displayIcon !!}
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="card mb-4">
            <div class="card-body">
                No currencies owned.
            </div>
        </div>
    @endif

    @if (Auth::check() && Auth::user()->id === $guild->owner_id && isset($takeCurrencyOptions) && isset($giveCurrencyOptions))
        <h3>
            Take/Give Currency
        </h3>
        {!! Form::open(['url' => 'guilds/' . $guild->id . '/bank/transfer']) !!}
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label>{{ Form::radio('action', 'take', true, ['class' => 'take-button']) }} Take from Guild</label>
                </div>
                <div class="col-md-6">
                    <label>{{ Form::radio('action', 'give', false, ['class' => 'give-button']) }} Give to Guild</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    {!! Form::label('quantity', 'Quantity') !!}
                    {!! Form::text('quantity', null, ['class' => 'form-control']) !!}
                </div>
                <div class="col-md-6 take">
                    {!! Form::label('currency_id', 'Currency') !!}
                    {!! Form::select('take_currency_id', $takeCurrencyOptions, null, ['class' => 'form-control', 'placeholder' => 'Select Currency']) !!}
                </div>
                <div class="col-md-6 give hide">
                    {!! Form::label('currency_id', 'Currency') !!}
                    {!! Form::select('give_currency_id', $giveCurrencyOptions, null, ['class' => 'form-control', 'placeholder' => 'Select Currency']) !!}
                </div>
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Transfer', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @endif

@endsection
