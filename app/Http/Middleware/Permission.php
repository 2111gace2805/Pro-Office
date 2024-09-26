<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Response;

class Permission {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user      = Auth::user();
        $user_type = $user->user_type;

        if ($user_type == 'admin' || $user_type == 'user' ) {
            $route_name = \Request::route()->getName();

            /** If User Type = User **/
            if ($route_name != '' && $user_type == 'user') {

                if (explode(".", $route_name)[1] == "update") {
                    $route_name = explode(".", $route_name)[0] . ".edit";
                } else if (explode(".", $route_name)[1] == "store") {
                    $route_name = explode(".", $route_name)[0] . ".create";
                }
                if (!has_permission($route_name)) {
                    if (!$request->ajax()) {
                        return back()->with('error', _lang('Permission denied !'));
                    } else {
                        return new Response('<h4 class="text-center">' . _lang('Permission denied !') . '</h4>');
                    }
                }
            }
        } else {
            if (!$request->ajax()) {
                return back()->with('error', _lang('Permission denied !'));
            } else {
                return new Response('<h5 class="text-center">' . _lang('Permission denied !') . '</h5>');
            }
        }

        return $next($request);
    }
}
