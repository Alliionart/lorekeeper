@if ($breeding->status !== 'Pending' && $breeding->status !== 'Draft')
    <p>This breeding has been processed by {!! $breeding->staff->displayName ?? 'Unknown Staff Member' !!}.</p>
@endif
@if ($breeding->split_user_id && $breeding->split_status === 'Pending' && (Auth::user()->id == $breeding->split_user_id || Auth::user()->id == $breeding->user_id))
    <div class="card mb-3 border border-warning">
        <h3 class="card-header">Split Request to {!! $breeding->split_user->displayName !!}</h3>
        <div class="card-body">
            @if (Auth::user()->id == $breeding->user_id)
                <p class="mb-0">This split breeding must be approved by {!! $breeding->split_user->displayName !!} before it may be added to the admin queue.</p>
            @elseif (Auth::user()->id == $breeding->split_user_id)
                @switch($breeding->split_status)
                    @case('Approved')
                        <p>{!! $breeding->split_user->displayName !!} has approved the split.</p>
                    @break

                    @case('Pending')

                        @default
                            <p class="mb-0">{!! $breeding->user->displayName !!} has requested to split this breeding with you. Please accept or reject the request.</p>
                            <div class="text-right">
                                <a id="rejectSplit" href="#" class="btn btn-danger">Reject Split</a>
                                <a id="approveSplit" href="#" class="btn btn-success">Approve Split</a>
                            </div>
                        @break

                        @case('Rejected')
                            <p>{!! $breeding->split_user->displayName !!} has rejected the split.</p>
                        @break
                    @endswitch
                @endif
            </div>
        </div>
    @else
        @if ($breeding->split_status && $breeding->split_user_id)
            <div class="card mb-3 border border-{{ $breeding->split_status === 'Approved' || $breeding->split_status === 'Confirmed' ? 'success' : 'danger' }}">
                <h3 class="card-header">Split Request to {!! $breeding->split_user->displayName !!}</h3>
                <div class="card-body">
                    @switch($breeding->split_status)
                        @case('Approved')
                            <p>{!! $breeding->split_user->displayName !!} has approved the split.</p>
                        @break

                        @case('Rejected')
                            <p>{!! $breeding->split_user->displayName !!} has rejected the split.</p>
                        @break

                        @case('Confirmed')
                            <p>{!! $breeding->split_user->displayName !!} has confirmed the split.</p>
                        @break
                    @endswitch
                </div>
            </div>
        @endif
    @endif
    <div class="row">
        @for ($i = 1; $i <= 2; $i++)
            @php
                $parentId = $breeding->{'parent_' . $i};
                $parent = $breeding->get_parent($parentId, $i);
            @endphp
            <div class="col-md-6">
                <div class="card">
                    <h3 class="card-header">Parent #{{ $i }}</h3>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ $parent->character->url }}"><img class="img-fluid rounded border border-secondary" alt="{{ $parent->name }}" src="{{ $parent->character->image->thumbnailUrl }}" /></a>
                            </div>
                            <div class="col-md-8">
                                <h5>{!! $parent->character->displayName !!}</h5>
                                <hr />
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Sex</h6>
                                    </div>
                                    <div class="col-md-8">
                                        {!! $parent->character->sex !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Species</h6>
                                    </div>
                                    <div class="col-md-8">
                                        {!! $parent->species !!}
                                    </div>
                                </div>
                                @if ($parent->subtype)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>Subtype</h6>
                                        </div>
                                        <div class="col-md-8">
                                            {!! $parent->subtype !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-md-12">
                                @if ($parent->phenotype)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h6>Phenotype</h6>
                                        </div>
                                        <div class="col-md-9">
                                            {!! $parent->phenotype !!}
                                        </div>
                                    </div>
                                @endif
                                @if ($parent->genotype)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h6>Genotype</h6>
                                        </div>
                                        <div class="col-md-9">
                                            {!! $parent->genotype !!}
                                        </div>
                                    </div>
                                @endif
                                @if ($parent->traits)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h6>Traits</h6>
                                        </div>
                                        <div class="col-md-9">
                                            <ul>
                                                @foreach ($parent->traits as $trait)
                                                    <li>{!! $trait !!}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                                <hr />
                                @if ($parent->permission)
                                    <h5>Breeding Permision #{!! $parent->permission->id !!} ( {{ $parent->permission->type }} )</h5>
                                    <p>{{ $parent->permission->quantity }} / {{ $parent->permission->full_quantity }} Remaining {!! add_help('One permission will be marked as used when rolling this breeding.') !!}</p>
                                    @if ($parent->permission->description)
                                        <strong>Notes:</strong>
                                        <p>
                                            {!! nl2br(htmlentities($parent->permission->description)) !!}
                                        </p>
                                    @else
                                        <p><i>No notes provided.</i></p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
    @if ($breeding->checkInbreeding())
        <div class="alert mt-3 alert-danger">
            These characters share a common ancestor. Inbreeding will be rolled.
        </div>
    @endif
    </hr class="my-3">
    <div class="card mt-3">
        <h3 class="card-header">Breeding Information</h3>
        <div class="card-body">
            <?php $breeding_items = $breeding->userItems; ?>
            @if ($breeding_items)
                <h5>Items Attached</h5>
                <div class="row">
                    @foreach ($breeding_items as $stack_id => $uItem)
                        <div class="col-md-4">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img class="img-fluid" src="{{ $uItem->userItem->item->imageUrl }}" />
                                </div>
                                <div class="col-md-9">
                                    <h5 class="mb-0">{!! $uItem->userItem->item->displayName !!} x{{ $uItem->quantity }}</h5>
                                    @if (isset($uItem->value) && $uItem->value)
                                        <p class="mb-0"><strong>Selected:</strong> {{ $uItem->value->name }}</p>
                                    @endif
                                    @if (Auth::check() && Auth::user()->isStaff && $show_fields)
                                        <div class="form-group">
                                            {!! Form::checkbox('remove_stack[' . $stack_id . ']', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                                            {!! Form::label('remove_stack[' . $stack_id . ']', 'Remove Item', ['class' => 'form-check-label ml-3']) !!} {!! add_help('By selecting remove item, this item will be returned to its owner instead of being used in the breeding.') !!}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p>No items were used in this breeding.</p>
            @endif
        </div>
    </div>
    <?php $litter = $breeding->getLitterResults(); ?>
    @if ($breeding->status === 'Approved')
        <div class="card mt-3">
            <h3 class="card-header">Litter</h3>
            <div class="card-body">
                @if ($litter && count($litter))
                    <?php
                    $userCanTakeAction = false;
                    $splitNeedsConfirmation = false;
                    
                    if (!$breeding->split_user_id && $breeding->status === 'Approved') {
                        $userCanTakeAction = Auth::check() && Auth::user()->id === (int) $breeding->user_id;
                    }
                    if ($breeding->split_user_id && $breeding->status === 'Approved' && $breeding->split_status === 'Approved') {
                        $userCanTakeAction = false;
                        $splitNeedsConfirmation = true;
                    }
                    if ($breeding->split_user_id && $breeding->status === 'Approved' && $breeding->split_status === 'Confirmed' && (Auth::check() && (Auth::user()->id === (int) $breeding->user_id || Auth::user()->id === (int) $breeding->split_user_id))) {
                        $userCanTakeAction = true;
                    }
                    ?>

                    @if ($splitNeedsConfirmation)
                        <div class="alert alert-info d-flex align-items-center justify-content-between">
                            <p class="mb-0"><strong>Split Needs Confirmation</strong> The split has been approved and needs
                                @if (Auth::check() && Auth::user()->id === $breeding->split_user_id)
                                    your confirmation before proceeding.
                            </p>
                            <a href="#" class="btn mt-0 ml-1 btn-outline-info" id="confirmSplit">Confirm Results</a>
                        @else
                            the confirmation of the other party before proceeding.</p>
                    @endif
            </div>
    @endif
    <div class="d-flex flex-wrap litterRow">
        @foreach ($litter as $i => $pup)
            @include('breeding._pup', ['pup' => $pup, 'i' => $i, 'showActions' => $userCanTakeAction])
        @endforeach
    </div>
    <div class="row mt-4 pt-2">
        <div class="col-md-4">
            <div class="alert alert-danger">
                <strong>Releasing a Pup</strong> Means you will release it to the wild for a flat 1,000 krones. A pup cannot be recovered after this action is taken.
            </div>
        </div>
        @if (config('lorekeeper.breeding.surrender_extension_exists'))
            <div class="col-md-4">
                <div class="alert alert-primary">
                    <strong>Surrender a Pup</strong> Means you will surrender this pup to the adoption center. Only pups with four or more markings may be surrendered. You will receive your choice of either 1 Trinket or the full Krone value.
                </div>
            </div>
        @endif
        <div class="col-md-4">
            <div class="alert alert-success">
                <strong>Convert a Pup</strong> The following pup will be converted into a Genotype to be ready to submit to Design Approval. Please only use this option once you are ready!
            </div>
        </div>
    </div>
@else
    <p>Produced an empty litter.</p>
    @endif
    </div>
    </div>
    <style>
        .litterRow {
            gap: 20px;
        }

        .litterRow>.card {
            min-width: calc(50% - 20px);
        }

        .litterRow>.card>.card-body a {
            color: unset;
            text-decoration: underline dotted;
            transition: 250ms all;
        }

        .litterRow>.card>.card-body a:hover {
            color: var(--blue);
        }
    </style>
    @endif
