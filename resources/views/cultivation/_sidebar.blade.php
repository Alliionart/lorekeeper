<ul>
    <li class="sidebar-header"><a href="{{ url(__('cultivation.cultivation')) }}" class="card-link">{{ucfirst(__('cultivation.cultivation'))}}</a></li>

    <li class="sidebar-section">
        <div class="sidebar-item">
                <a href="{{ url(__('cultivation.cultivation').'/guide') }}" class="{{ set_active(__('cultivation.cultivation').'/guide') }} btn text-left">Cultivation Guide</a> 
        </div>
    </li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">{{ucfirst(__('cultivation.cultivation'))}} Areas</div>
        @foreach($areas as $area)
            <div class="sidebar-item">
                @if(isset($user) && in_array($area->id, $user->areas->pluck('id')->toArray()))
                <a href="{{ $area->idUrl }}" class="{{ set_active(__('cultivation.cultivation').'/'.$area->id) }} btn text-left">{{ $area->name }} <i class="fa fa-unlock mr-2"></i></a> 
                @else
                <a href="{{ $area->idUrl }}" class="{{ set_active(__('cultivation.cultivation').'/'.$area->id) }} btn disabled text-left">{{ $area->name }} <i class="fa fa-lock mr-2"></i></a> 
                @endif
            
            </div>
        @endforeach
    </li>

</ul>
