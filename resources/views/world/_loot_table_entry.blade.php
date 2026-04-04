<div class="row world-entry">
    <div class="col-12">
        <h3>{!! $name !!}</h3>
        @if($table->disclose_loots == 2)
            <p>This table's drop rates are hidden</p>
        @endif
        @if(!$table->disclose_loots)
            <p>This table's rewards are hidden</p>
        @else
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th width="70%">Loot</th>
                        @if($table->disclose_loots == 1)
                            <th width="30%">Drop Rate</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($table->loot as $loot)
                        <tr>
                            <td>
                                {!! $loot->displayName !!}
                            </td>
                            @if($table->disclose_loots == 1)
                                <td>{{ $loot->dropRate }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>