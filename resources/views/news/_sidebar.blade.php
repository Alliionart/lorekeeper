<ul>
    <li class="sidebar-header"><a href="{{ url('news') }}" class="card-link">News</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Categories</div>
        @foreach ($categories as $category_id => $category_name)
            <div class="sidebar-item"><a href="{{ url('news/?category_id='.$category_id) }}" class="{{ set_active('news/?category_id='.$category_id) }}">{{ $category_name }}</a></div>
        @endforeach
    </li>
    @if (isset($newses))
        <li class="sidebar-section">
            <div class="sidebar-section-header">On This Page</div>
            @foreach ($newses as $news)
                @php $newslink = 'news/'.$news->slug; @endphp
                <div class="sidebar-item"><a href="{{ $news->url }}" class="{{ set_active($newslink) }}">{{ $news->title }}</a></div>
            @endforeach
        </li>
    @else
        <li class="sidebar-section">
            <div class="sidebar-section-header">Recent News</div>
            @foreach ($recentnews as $news)
                @php $newslink = 'news/'.$news->slug; @endphp
                <div class="sidebar-item"><a href="{{ $news->url }}" class="{{ set_active($newslink) }}">{{ $news->title }}</a></div>
            @endforeach
        </li>
    @endif
</ul>
