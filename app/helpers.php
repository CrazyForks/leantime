<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Application;
use Leantime\Core\Bootloader;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Support\Build;
use Leantime\Core\Support\Cast;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Core\Support\Format;
use Leantime\Core\Support\FromFormat;
use Leantime\Core\Support\Mix;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

if (! function_exists('app')) {
    /**
     * Returns the application instance.
     *
     *
     * @return mixed|Application
     *
     * @throws BindingResolutionException
     */
    function app(string $abstract = '', array $parameters = []): mixed
    {
        $app = Application::getInstance();

        return ! empty($abstract) ? $app->make($abstract, $parameters) : $app;
    }
}

// if (! function_exists('dd')) {
//     /**
//      * Dump the passed variables and end the script.
//      *
//      * @param mixed $args
//      * @return never
//      */
//     function dd(...$args): never
//     {
//         echo sprintf('<pre>%s</pre>', var_export($args, true));
//
//         if (
//             app()->bound(IncomingRequest::class)
//             && (
//                 /** @var IncomingRequest $request */
//                 ($request = app()->make(IncomingRequest::class)) instanceof HtmxRequest
//                 || $request->isXmlHttpRequest()
//             )
//         ) {
//             report('this fires');
//
//             exit(0);
//         }
//
//         report(var_export([app()->bound(IncomingRequest::class), $request = app()->make(IncomingRequest::class), $request->isXmlHttpRequest()], true));
//
//         exit();
//     }
// }

if (! function_exists('bootstrap_minimal_app')) {
    /**
     * Bootstrap a new IoC container instance.
     *
     *
     * @throws BindingResolutionException
     */
    function bootstrap_minimal_app(): Application
    {
        $app = app()::setInstance(new Application)::setHasBeenBootstrapped();
        $app_inst = Bootloader::getInstance($app)->getApplication();
        $app_inst->make(AppSettings::class)->loadSettings();

        return $app_inst;
    }
}

if (! function_exists('__')) {
    /**
     * Translate a string.
     *
     * @throws BindingResolutionException
     */
    function __(string $index, string $default = ''): string
    {
        return app()->make(Language::class)->__(index: $index, default: $default);
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|null  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return ($view is null ? \Illuminate\Contracts\View\Factory : \Illuminate\Contracts\View\View)
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app(\Illuminate\View\Factory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('array_sort')) {
    /**
     * sort array of arrqays by value
     *
     * @param  string  $sortyBy
     */
    function array_sort(array $array, mixed $sortyBy): array
    {

        if (is_string($sortyBy)) {
            $collection = collect($array);

            $sorted = $collection->sortBy($sortyBy, SORT_NATURAL);

            return $sorted->values()->all();
        } else {
            return \Illuminate\Support\Collection::make($array)->sortBy($sortyBy)->all();
        }
    }
}

if (! function_exists('do_once')) {
    /**
     * Execute a callback only once.
     */
    function do_once(string $key, Closure $callback, bool $across_requests = false): void
    {
        $key = "do_once_{$key}";

        if ($across_requests) {
            if (session()->exists('do_once') === false) {
                session(['do_once' => []]);
            }

            if (session('do_once.'.$key) ?? false) {
                return;
            }

            session(['do_once.'.$key => true]);
        } else {
            static $do_once;
            $do_once ??= [];

            if ($do_once[$key] ?? false) {
                return;
            }

            $do_once[$key] = true;
        }

        $callback();
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     *
     * @return mixed|Application
     *
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function config(array|string|null $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('build')) {
    /**
     * Turns any object into a builder object
     *
     **/
    function build(object $object): Build
    {
        return new Build($object);
    }
}

if (! function_exists('format')) {
    /**
     * Returns a format object to format string values
     *
     * @param  string|int|float|DateTime|Carbon|null  $value
     * @param  string|int|float|DateTime|null  $value2
     */
    function format(string|int|float|null|\DateTime|\Carbon\CarbonInterface $value, string|int|float|null|\DateTime|\Carbon\CarbonInterface $value2 = null, ?FromFormat $fromFormat = FromFormat::DbDate): Format|string
    {
        return new Format($value, $value2, $fromFormat);
    }
}

if (! function_exists('cast')) {
    /**
     * Casts a variable to a different type if possible.
     *
     * @param  mixed  $obj  The object to be cast.
     * @param  string  $to_class  The class to which the object should be cast.
     * @param  array  $construct_params  Optional parameters to pass to the constructor.
     * @param  array  $mappings  Make sure certain sub properties are casted to specific types.
     * @return mixed The casted object, or throws an exception on failure.
     *
     * @throws \InvalidArgumentException If the class does not exist.
     * @throws \RuntimeException|ReflectionException On serialization errors.
     */
    function cast(mixed $source, string $classOrType, array $constructParams = [], array $mappings = []): mixed
    {
        if (in_array($classOrType, ['int', 'integer', 'float', 'string', 'str', 'bool', 'boolean', 'object', 'stdClass', 'array'])) {
            return Cast::castSimple($source, $classOrType);
        }

        if (enum_exists($classOrType)) {
            return Cast::castEnum($source, $classOrType);
        }

        // Convert string to date if required.
        if (is_string($source) && is_a($classOrType, CarbonInterface::class, true)) {
            return Cast::castDateTime($source);
        }

        return (new Cast($source))->castTo($classOrType, $constructParams, $mappings);
    }
}

if (! function_exists('mix')) {
    /**
     * Get the path to a versioned Mix file. Customized for Leantime.
     *
     *
     * @throws BindingResolutionException
     */
    function mix(string $path = '', string $manifestDirectory = ''): Mix|string
    {
        if (! ($app = app())->bound(Mix::class)) {
            $app->instance(Mix::class, new Mix);
        }

        $mix = $app->make(Mix::class);

        if (empty($path)) {
            return $mix;
        }

        return $mix($path, $manifestDirectory);
    }
}

if (! function_exists('dtHelper')) {
    /**
     * Get a singleton instance of the DateTimeHelper class.
     *
     *
     * @throws BindingResolutionException
     */
    function dtHelper(): ?DateTimeHelper
    {
        if (! app()->bound(DateTimeHelper::class)) {
            app()->singleton(DateTimeHelper::class);
        }

        return app()->make(DateTimeHelper::class);
    }
}

if (! function_exists('session')) {
    /**
     * Get the path to a versioned Mix file. Customized for Leantime.
     *
     * @param  string  $path
     * @param  string  $manifestDirectory
     * @return Mix|string
     *
     * @throws BindingResolutionException
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->put($key);
        }

        return app('session')->get($key, $default);
    }

}

if (! function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }

}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  list<string>|string|null  $key
     * @param  mixed  $default
     * @return ($key is null ? \Illuminate\Http\Request : ($key is string ? mixed : array<string, mixed>))
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app()->make(IncomingRequest::class);
        }

        if (is_array($key)) {
            return app()->make(IncomingRequest::class)->only($key);
        }

        $value = app()->make(IncomingRequest::class)->__get($key);

        return is_null($value) ? value($default) : $value;
    }
}

if (! function_exists('report')) {
    /**
     * Report an exception.
     *
     * @param  \Throwable|string  $exception
     * @return void
     */
    function report($exception)
    {
        Log::critical($exception);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (! function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $url
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    function redirect($url = null, $http_response_code = 302, $headers = [], $secure = null)
    {
        return new RedirectResponse(
            trim(preg_replace('/\s\s+/', '', strip_tags($url))),
            $http_response_code
        );
    }
}

if (! function_exists('currentRoute')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $to
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     */

    function currentRoute()
    {
        return app('request')->getCurrentRoute();
    }
}

if (! function_exists('get_domain_key')) {
    /**
     * Gets a unique instance key determined by domain
     *
     */
    function get_domain_key()
    {
        //Now that we know where the instance is bing called from
        //Let's add a domain level cache.
        $domainCacheName = 'localhost';
        if (! app()->runningInConsole()) {
            $domainCacheName = md5(Str::slug(app('request')->host().app('request')['key']));
        }

        return $domainCacheName;
    }
}

if (! function_exists('phar_glob')) {
    /**
     * Glob function for phar files
     * @param string $pattern Pattern must start with 'phar://'
     * @return array Array of matching paths (both files and directories)
     * @throws \Exception When path doesn't start with 'phar://'
     */
    function phar_glob(string $pattern): array
    {
        if (!str_starts_with($pattern, 'phar://')) {
            throw new \Exception('phar_glob only works with phar:// paths');
        }

        // Remove phar:// prefix for processing
        $pharPath = substr($pattern, 7);

        // Split into phar archive path and internal path
        $pharFile = explode('.phar', $pharPath, 2)[0] . '.phar';
        if (!file_exists($pharFile)) {
            return [];
        }

        // Normalize slashes
        $pattern = str_replace('\\', '/', $pharPath);

        // Split pattern into path segments
        $segments = explode('/', $pattern);
        $filename = array_pop($segments);

        // Find the base path (up to first wildcard)
        $baseSegments = [];
        foreach ($segments as $segment) {
            if (strpbrk($segment, '*?[') !== false) {
                break;
            }
            $baseSegments[] = $segment;
        }

        $basePath = 'phar://' . implode('/', $baseSegments);
        $segments = array_slice($segments, count($baseSegments));

        $convertToRegex = fn ($string) => strtr(preg_quote($string, '/'), [
            '\*' => '[^/]*',
            '\?' => '[^/]',
            '\[' => '[',
            '\]' => ']',
        ]);

        // Convert remaining path segments and filename to regex patterns
        $pathRegexes = array_map($convertToRegex, $segments);
        $fileRegex = $convertToRegex($filename);

        $results = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                // Get the path relative to the base
                $fullPath = str_replace('\\', '/', $file->getPathname());
                $relativePath = substr($fullPath, strlen($basePath) + 1);
                if ($relativePath === false) {
                    continue;
                }

                $relativeSegments = explode('/', $relativePath);

                // Check if path segments match
                if (count($relativeSegments) !== count($segments) + 1) {
                    continue;
                }

                // Check each directory in the path
                $match = true;
                for ($i = 0; $i < count($segments); $i++) {
                    if (!preg_match('/^' . $pathRegexes[$i] . '$/', $relativeSegments[$i])) {
                        $match = false;
                        break;
                    }
                }

                // Check final segment
                if ($match && preg_match('/^' . $fileRegex . '$/', end($relativeSegments))) {
                    $results[] = $file->getPathname();
                }
            }
        } catch (UnexpectedValueException) {
            return [];
        }

        return $results;
    }
}

if (! function_exists('safe_add_nested')) {
    function safe_add_nested(
        array &$array,
        string $key,
        mixed $value,
        bool $merge = false
    ): void {
        if (empty($key) || str_contains($key, '..')) {
            throw new InvalidArgumentException('Invalid key format');
        }

        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            $current = &$array;

            foreach ($parts as $part) {
                $current[$part] ??= [];
                if (!is_array($current[$part])) {
                    throw new RuntimeException("Cannot add to non-array value at key '$part'");
                }
                $current = &$current[$part];
            }

            if (is_array($value) && $merge) {
                $current = array_merge($current, $value);
            } else {
                $current = $value;
            }

            return;
        }

        if (is_array($value) && $merge) {
            $array[$key] ??= [];
            if (!is_array($array[$key])) {
                throw new RuntimeException("Cannot merge with non-array value at key '$key'");
            }
            $array[$key] = array_merge($array[$key], $value);
            return;
        }

        $array[$key] = $value;
    }
}
