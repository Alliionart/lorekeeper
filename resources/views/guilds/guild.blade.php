@extends('layouts.app')

@section('title')
    {{ ucwords(__('guilds.guilds')) }}
@endsection

@section('sidebar')
    @include('guilds._sidebar')
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds'), $guild->name => $guild->name]) !!}

    <div class="jumbotron guild-banner jumbotron-fluid text-left pb-0 rounded bg-dark mb-5" {!! $guild->has_banner ? 'style="background-image: url(\'' . $guild->bannerUrl . '\');"' : '' !!}>
        @if ($guild->is_disbanded)
            <span class="badge badge-danger text-uppercase" style="position:absolute;top:20px;left:20px;font-size:18px;">Disbanded</span>
        @endif

        <div class="jumbo-inner py-3 px-5 d-flex align-items-center justify-content-between" style="transform:translateY(45px)">
            <div class="d-flex align-items-center">
                @if ($guild->has_logo)
                    <img class="guild-logo mr-4" src="{{ $guild->getLogoUrlAttribute() }}" />
                @endif
                <h1 class="display-5 text-white">{{ $guild->name }}</h1>
            </div>

            <?php
            $user = Auth::user();
            $inGuild = $user
                ? $guild
                    ->members()
                    ->where('user_id', $user->id)
                    ->exists()
                : false;
            $isStaff = $user && $user->isStaff;
            $isOwner = $user && (int) $guild->owner_id === (int) $user->id;
            $canManage = $isStaff || ($user && $guild->getGuildEditPermissions($user));
            ?>

            @if ($inGuild || $isStaff)
                <div class="btn-group" id="actionsMenu">
                    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div class="dropdown-menu">
                        @if ($isStaff)
                            <h6 class="dropdown-header text-uppercase">Admin Actions</h6>
                            <a class="dropdown-item text-danger" href="#" id="delete-guild">Delete Guild</a>
                        @endif
                        @if ($isOwner || $isStaff)
                            @if ($isStaff)
                                <div class="dropdown-divider"></div>
                            @endif
                            <a class="dropdown-item" href="#" id="disband-guild">Disband Guild</a>
                        @endif
                        @if ($canManage)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" id="add-character">Add Character</a>
                            <a class="dropdown-item" href="#" id="remove-character">Remove Character</a>
                        @endif
                        @if ($inGuild && !$isOwner)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#" id="leave-guild">Leave Guild</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($isOwner || $isStaff)
        {!! Form::open(['url' => $guild->viewUrl . '/disband', 'id' => 'disbandGuildForm', 'class' => 'd-none']) !!}
        {!! Form::close() !!}
    @endif
    @if ($isStaff)
        {!! Form::open(['url' => $guild->viewUrl . '/delete', 'id' => 'deleteGuildForm', 'class' => 'd-none']) !!}
        {!! Form::close() !!}
    @endif
    @if ($inGuild && !$isOwner)
        <div class="modal fade" id="leaveGuildModal" tabindex="-1" role="dialog" aria-labelledby="leaveGuildModalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveGuildModalTitle">Leave {{ $guild->name }}?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to leave this guild? Your characters will be removed from it.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        {!! Form::open(['url' => $guild->viewUrl . '/leave', 'class' => 'd-inline']) !!}
                        {!! Form::submit('Leave Guild', ['class' => 'btn btn-danger']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Details -->
    <div class="row mt-5">
        <div class="col-md col-md-8">
            <h3>About {{ $guild->name }}</h3>
            {!! $guild->parsed_description !!}
        </div>

        <div class="col-md col-md-4">
            <div class="card">
                <h4 class="card-header">Details</h4>
                <div class="card-body">
                    @if ($guild->status === 'active')
                        <span class="h6 p-1 rounded bg-success text-white">Active</span>
                    @else
                        <span class="h6 p-1 rounded bg-secondary text-white">Disbanded</span>
                    @endif
                    <span class="h6 p-1 rounded border ml-2 {{ $guild->open_new_users ? 'border-success text-success' : 'border-danger text-danger' }}">{{ $guild->open_new_users ? 'Open Applications' : 'Closed Applications' }}</span>
                    @if (Auth::check() && $guild->open_new_users)
                        <br>
                        <a id="apply-to-join" href="#" class="btn btn-primary mt-3">Apply to Join</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!--basic info -->
    <div class="row">
        <div class="col-md col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Members</h3>
                    <a href="{{ $guild->getViewUrlAttribute() . '/members' }}" class="btn btn-outline-primary">View All Members</a>
                </div>
                <div class="card-body">
                    @if ($guild->members)
                        @include('guilds._member_table', ['members' => $guild->members->take(10)])
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Characters</h3>
                    <a href="{{ $guild->getViewUrlAttribute() . '/characters' }}" class="btn btn-outline-primary">View All Characters</a>
                </div>
                <div class="card-body">
                    @if ($guild->characters)
                        @include('guilds._characters', ['characters' => $guild->characters->take(6)])
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .guild-banner {
            position: relative;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .guild-banner::after {
            content: "";
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.7) 20%, rgba(0, 0, 0, 0) 100%);
            mix-blend-mode: multiply;
            z-index: 1;
            border-radius: 5px;
        }

        .guild-banner .jumbo-inner {
            position: relative;
            z-index: 2;
        }

        .guild-logo {
            filter: drop-shadow(2px 4px 6px rgba(0, 0, 0, 0.25));
        }
    </style>
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('#add-character').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url($guild->viewUrl) }}/characters/add", 'Add Characters');
            });

            $('#remove-character').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url($guild->viewUrl) }}/characters/remove", 'Remove Characters');
            });

            $('#disband-guild').on('click', function(e) {
                e.preventDefault();
                if (confirm('Disband this guild? Members and characters will be removed.')) {
                    $('#disbandGuildForm').trigger('submit');
                }
            });

            $('#delete-guild').on('click', function(e) {
                e.preventDefault();
                if (confirm('Permanently delete this guild? This action cannot be undone.')) {
                    $('#deleteGuildForm').trigger('submit');
                }
            });

            $('#leave-guild').on('click', function(e) {
                e.preventDefault();
                $('#leaveGuildModal').modal('show');
            });

            $('#apply-to-join').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url($guild->viewUrl) }}/apply", 'Apply to Join');
            });
        });
    </script>
@endsection
