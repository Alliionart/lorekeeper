<?php

namespace Plugins\LoginAsUser\src\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectLoginAsBar
{
    protected static bool $hasInjectedBar = false;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession() && $request->session()->has('impersonator_id')) {
            Event::listen('composing:*', function ($eventName, $data) {
                if (self::$hasInjectedBar) return;
                $view = reset($data);
                if (method_exists($view, 'getFactory')) {
                    self::$hasInjectedBar = true;
                    $view->getFactory()->startPush('body-end');
                    echo view('AdminLoginController::_logged_in_as_bar')->render();
                    $view->getFactory()->stopPush();
                }
            });
        }

        $response = $next($request);
        if (method_exists($response, 'getContent') && Auth::check()) {
            // Add a button to the user's page
            if ($request->is('user/*')) {
                $content = $response->getContent();

                $username = $request->route('name') ?? basename($request->url());
                $viewUser = \App\Models\User\User::where('name', $username)->first();

                if ($viewUser && Auth::id() !== $viewUser->id) {
                    
                //ehh too lazy here to make a blade and link it but you get the picture - inserting this next the breadcrumbs
                    $buttonHtml = '
                        <div class="plugin-login-action text-right">
                            <form action="/quick-login" method="POST">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <input type="hidden" name="user_id" value="' . $viewUser->id . '">
                                <button type="submit" class="btn btn-outline-primary">
                                    Login As ' . e($viewUser->name) . '
                                </button>
                            </form>
                        </div>
                    ';

                    $targetLandmark = '</ol>'; 
                    
                    if (!str_contains($content, $targetLandmark)) {
                        $targetLandmark = '</ul>';
                    }

                    if (str_contains($content, $targetLandmark)) {
                        $content = preg_replace('/' . preg_quote($targetLandmark, '/') . '/', $targetLandmark . "\n" . $buttonHtml, $content, 1);
                        $response->setContent($content);
                    }
                }
            }
        }

        return $response;
    }
}
