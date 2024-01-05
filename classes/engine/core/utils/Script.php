<?php

namespace JaxWilko\Game\Classes\Engine\Core\Utils;

use JaxWilko\Game\Classes\Engine\Engine;

class Script
{
    /**
     * Wraps custom php code in a callable and returns the callable, allows for custom string args to be passed.
     *
     * @param string $code
     * @param array $args
     * @return callable
     */
    public static function compile(string $code, array $args = []): callable
    {
        $hash = md5($code);

        $file = storage_path(Engine::SCRIPT_CACHE_DIR);

        if (!is_dir($file)) {
            mkdir($file, 0755);
        }

        $file .= '/' . $hash . '.php';

        if (!file_exists($file)) {
            $content = implode(PHP_EOL, array_map(
                fn (string $line) => str_repeat(' ', 4) . $line,
                explode(PHP_EOL, $code)
            ));

            $arguments = implode(', ', $args);

            file_put_contents(
                $file,
                <<<PHP
                    <?php

                    return function ($arguments) {
                    $content
                    };

                    PHP
            );
        }

        return require $file;
    }
}
