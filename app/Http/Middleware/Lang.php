<?php

namespace App\Http\Middleware;

use Closure;

class Lang
{
    /**
     * Изменение языка ответа в зависимости от указанного параметра.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($lang = $request->get('lang')) {
            if (in_array($lang, config('app.available_localizations'), false)) {
                app('translator')->setLocale($lang);
                unset($request['lang']);
            }
        }

        return $next($request);
    }
}
