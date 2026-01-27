<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller {
    /**
     * Shows the news index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.news.news', [
            'newses' => News::orderBy('updated_at', 'DESC')->paginate(20),
        ]);
    }

    /**
     * Shows the create news page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateNews() {
        return view('admin.news.create_edit_news', [
            'news' => new News,
            'categories' => NewsCategory::orderBy('sort', 'ASC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit news page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditNews($id) {
        $news = News::find($id);
        if (!$news) {
            abort(404);
        }

        return view('admin.news.create_edit_news', [
            'news' => $news,
            'categories' => NewsCategory::orderBy('sort', 'ASC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a news page.
     *
     * @param App\Services\NewsService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditNews(Request $request, NewsService $service, $id = null) {
        $id ? $request->validate(News::$updateRules) : $request->validate(News::$createRules);
        $data = $request->only([
            'title', 'text', 'post_at', 'is_visible', 'bump', 'category_id',
        ]);
        if ($id && $service->updateNews(News::find($id), $data, Auth::user())) {
            flash('News updated successfully.')->success();
        } elseif (!$id && $news = $service->createNews($data, Auth::user())) {
            flash('News created successfully.')->success();

            return redirect()->to('admin/news/edit/'.$news->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the news deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteNews($id) {
        $news = News::find($id);

        return view('admin.news._delete_news', [
            'news' => $news,
        ]);
    }

    /**
     * Deletes a news page.
     *
     * @param App\Services\NewsService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteNews(Request $request, NewsService $service, $id) {
        if ($id && $service->deleteNews(News::find($id))) {
            flash('News deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/news');
    }

    /* -------------------------------------------------------------------------------------- */
    /* NEWS CATEGORIES
    /* -------------------------------------------------------------------------------------- */

    /**
     * Shows the news categories index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCategoryIndex() {
        return view('admin.news.categories.index', [
            'categories' => NewsCategory::orderBy('sort', 'ASC')->paginate(20),
        ]);
    }

    /**
     * Shows the news categories index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateNewsCategory() {
        return view('admin.news.categories.create_edit', [
            'category' => new NewsCategory,
        ]);
    }

    /**
     * Shows the news categories index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditNewsCategory($id) {

        $category = NewsCategory::find($id);
        if (!$category) {
            abort(404);
        }

        return view('admin.news.categories.create_edit', [
            'category' => $category,
        ]);
    }

    /**
     * Creates or edits a news category.
     *
     * @param App\Services\NewsService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditNewsCategory(Request $request, NewsService $service, $id = null) {
        $id ? $request->validate(NewsCategory::$updateRules) : $request->validate(NewsCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'discord_webhook', 'sort',
        ]);
        if ($id && $service->updateNewsCategory(NewsCategory::find($id), $data, Auth::user())) {
            flash('News category updated successfully.')->success();
        } elseif (!$id && $category = $service->createNewsCategory($data, Auth::user())) {
            flash('News category created successfully.')->success();

            return redirect()->to('admin/news/categories');
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the news category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteNewsCategory($id) {
        $category = NewsCategory::find($id);

        return view('admin.news.categories._delete_news_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes a news category.
     *
     * @param App\Services\NewsService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteNewsCategory(Request $request, NewsService $service, $id) {
        if ($id && $service->deleteNewsCategory(NewsCategory::find($id))) {
            flash('News category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/news/categories');
    }

    /**
     * Sorts news categories.
     *
     * @param App\Services\NewsService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortNewsCategory(Request $request, NewsService $service) {
        $data = $request->only(['sort']);
        if ($service->sortNewsCategories($data['sort'])) {
            flash('News categories sorted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/news/categories');
    }

}
