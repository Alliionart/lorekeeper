<?php

namespace App\Http\Controllers;

use db;

use App\Models\Prompt\Prompt;
use App\Models\Prompt\PromptCategory;
// use App\Http\Controllers\Controller;
use App\Models\Submission\Submission;
use App\Services\SubmissionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SitePage;

class QueueController extends Controller {
    /**
     * Shows the queue index page.
     *
     * @param string $status
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueueIndex(Request $request, $status = null) {
        $submissions = Submission::with('prompt')->where('status', $status ? ucfirst($status) : 'Pending')->whereNotNull('prompt_id');
        $data = $request->only(['prompt_category_id', 'sort']);
        if (isset($data['prompt_category_id']) && $data['prompt_category_id'] != 'none') {
            $submissions->whereHas('prompt', function ($query) use ($data) {
                $query->where('prompt_category_id', $data['prompt_category_id'])->whereNot('public', '=', 1);
            });
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'newest':
                    $submissions->sortNewest();
                    break;
                case 'oldest':
                    $submissions->sortOldest();
                    break;
            }
        } else {
            $submissions->sortOldest();
        }

        return view('queues.queues', [
            'submissions' => $submissions->paginate(30)->appends($request->query()),
            'categories'  => ['none' => 'Any Category'] + PromptCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'isClaims'    => false,
        ]);
    }

    public function getQueue() {
        return view('queues.queues');
    }
}
