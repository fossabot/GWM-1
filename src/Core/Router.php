<?php

namespace GWM\Core;

use GWM\Commerce\Controllers\Paysera;
use GWM\Core\Utils\Debug;

use Pux\Mux;
use Pux\RouteExecutor;

/**
 * Undocumented class
 */
class Router
{
    protected static array $routes = [];
    private static array $req = [];
    private $url;

    /**
     * @magic
     */
    function __construct()
    {
        self::$req = [
            'url' => rtrim($_SERVER['REQUEST_URI'], '/'),
            'method' => $_SERVER['REQUEST_METHOD']
        ];

        $this->url = filter_var(rtrim($_SERVER['REQUEST_URI'], '/'), FILTER_SANITIZE_URL);

        //$json = \file_get_contents('routes.json');
        //$data = \json_decode($json);

        // $this->routes = $data;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function Route()
    {
        foreach (self::routes as $route) 
        {
            $pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route['url'])) . "$@D";
            $matches = [];

            if(self::$req['method'] == $route['method'] && preg_match($pattern, self::$req['url'], $matches))
            {
                array_shift($matches);
                return call_user_func_array($route['callback'], $matches);
            }
        }
    }

    function Resolve(Response $response)
    {
        $mux = new Mux;

        $this->Match('/', function() {
            $home = new \GWM\Core\Controllers\Home();
            $home->index();
            $this->Profiler();
            exit;
        });

        $this->Match('/store', function() {
            $home = new \GWM\Commerce\Controllers\Store();
            $home->index();
            $this->Profiler();
            exit;
        });
        
        $this->Match('/dashboard', function() {
            $request = new Request();
            $dash = new Controllers\Dashboard();
            $dash->index($request);
            $this->Profiler();
            exit;
        });

        $this->Match('/auth', function() {
            $auth = new Controllers\Auth();
            $auth->index();
            $this->Profiler();
            exit;
        });

        $this->Match('/out', function() {
            $auth = new Controllers\Auth();
            $auth->logout();
            $this->Profiler();
            exit;
        });

        $this->Match('/api/articles', function() {
            $dash = new Controllers\Dashboard();
            $dash->articles();
            exit;
        });

        $this->Match('/dashboard/models', function() use ($response) {
            $dash = new Controllers\Dashboard();
            $dash->models($response);
            exit;
        });

        $this->Match('/dashboard/build', function() {
            die(new Distributor('admin'));
        });

        $this->Match('/dashboard/media', function() {
            $dash = new Controllers\Dashboard();
            $dash->media();
            $this->Profiler();
            exit;
        });

        $mux->get('/dashboard/files',
            [
                Controllers\Dashboard::class,
                'files'
            ]);

        $mux->get('/pay',
        [
            Paysera::class,
            'pay'
        ]);

        $mux->get('/dashboard/articles',
        [
            Controllers\Dashboard::class,
            'articles'
        ]);


        $route = $mux->dispatch($this->url);
        $result = RouteExecutor::execute($route);
    }

    public static function Profiler()
    {
        $time = round(microtime(true) - GWM['START_TIME'], 2);

        $exceptions = Debug::$log;

        echo '<div style="position: fixed; z-index: 99; bottom: 45px; left: 60vw; right:0 ; height: 45px; background: #cacaca;">';
        foreach($exceptions as $exception) {
            echo '<p style="margin:0;">'.$exception .'</p>';
        }
        echo '</div>';

        echo <<<EOL

        <div style="position: fixed; z-index: 99; bottom: 0; left: 60vw; right:0 ; height: 45px; background: #a3a3a3;">
            <div style="color: white; float: left;">
                <div>GWM Profiler | <a href="#" class="btn" style="color: whitesmoke;">Backtrace</a>
                </div>
            </div>
            
            <div style="color: white; float: right;">Time pased: $time ms.</div>
        </div>

EOL;}

    function Match($url, $function)
    {
        if ($_SERVER['REQUEST_URI'] == $url) {
            $function->__invoke();
        }
    }
}