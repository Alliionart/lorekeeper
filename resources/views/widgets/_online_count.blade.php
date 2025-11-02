<div class="text-right small m-0">
    USERS ONLINE: {{ App\Models\User\User::where('last_seen', '>=', Carbon\Carbon::now()->subMinutes(5))->count() }}
</div>
