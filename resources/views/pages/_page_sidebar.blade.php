<ul>
    <li class="sidebar-header">Pages</li>


    <li class="sidebar-section">
        <div class="sidebar-item"><a href="{{ url('world/info/design-hub') }}" class="{{ set_active('world/info/design-hub') }}">Design Hub</a></div>
        <div class="sidebar-item"><a href="{{ url('world/subtypes') }}" class="{{ set_active('world/subtypes*') }}">Breeding Rates</a></div>
        <div class="sidebar-item"><a href="{{ url('world/rarities') }}" class="{{ set_active('world/rarities*') }}">New Players</a></div>
        <div class="sidebar-item"><a href="{{ url('world/trait-categories') }}" class="{{ set_active('world/trait-categories*') }}">Point System</a></div>
        <div class="sidebar-item"><a href="{{ url('world/traits') }}" class="{{ set_active('world/traits*') }}">Rules</a></div>
        <div class="sidebar-item"><a href="{{ url('world/character-categories') }}" class="{{ set_active('world/character-categories*') }}">Suggestion Hub</a></div>
    </li>

    <li class="sidebar-header">Current Page</li>
    <li class="sidebar-section" id="currentPage">

    </li>
    <hr />
</ul>

<script>
    $(document).ready(function() {
        let tocArray = $(".site-page-content").find("h1, h2, h3, h4, h5, h6");
        for (let i = 0; i < tocArray.length; i++) {
            const element = tocArray[i];
            const anchor = element.textContent.toLowerCase().replaceAll(' ', '-');

            $(element).attr("id", anchor);
            $('#currentPage').append('<div class="sidebar-item"><a href="#' + anchor + '">' + element.textContent + '</a></div>');
        }
    });
</script>
