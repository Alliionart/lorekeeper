<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class NewsController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | News Controller
    |--------------------------------------------------------------------------
    |
    | Displays news posts and updates the user's news read status.
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct() {
        parent::__construct();
        View::share('recentnews', News::visible()->orderBy('updated_at', 'DESC')->take(10)->get());
    }

    /**
     * Shows the news index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request) {
        if (Auth::check() && Auth::user()->is_news_unread) {
            Auth::user()->update(['is_news_unread' => 0]);
        }

        $query = News::visible()->orderBy('updated_at', 'DESC');

        $data = $request->only(['category_id']);

        if (isset($data['category_id']) && $data['category_id'] != 'none') {
            if ($data['category_id'] == 'withoutOption') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $data['category_id']);
            }
        }

        return view('news.index', [
            'newses'        => $query->visible()->orderBy('updated_at', 'DESC')->paginate(10)->appends($request->query()),
            'categories'    => NewsCategory::orderBy('sort', 'ASC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows a news post.
     *
     * @param int         $id
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getNews($id, $slug = null) {
        $news = News::where('id', $id)->where('is_visible', 1)->first();
        if (!$news) {
            abort(404);
        }

        return view('news.news', ['news' => $news]);
    }
}
