<?php
namespace Octus\App\Utils;


class View
{
    const VIEW_PATH = __DIR__.'/../../views/';

    /**
     * Method rescue content HTML of file view
     *
     * @param string $view
     * @return string
     */
    private static function getContentView(string $view): string{
        $file = self::VIEW_PATH.$view.'.html';
        $rollback = self::VIEW_PATH.'pages/404.html';
        return file_exists($file) ? file_get_contents($file) : file_get_contents($rollback);
    }

    /**
     * Method read file view and feed with param markdown
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public static function renderView(string $view, $data = []): string{
        $content = self::getContentView($view);
        $keys    = array_keys($data);
        $keys    = array_map(function($item){
            return '{{'.$item.'}}';
        }, $keys);

        return str_replace($keys, $data, $content);
    }

}