<?php

namespace BestLang\core\view;

class BLView
{
    private static $twig;

    public static function render($template, $data = [])
    {
        if (strpos($template, '.') === false) {
            $template .= '.html';
        }
        if (!isset(self::$twig)) {
            self::$twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem(APP_VIEW_DIR), [
                    'cache' => APP_CACHE_DIR,
                    'auto_reload' => true
                ]
            );
        }
        return self::$twig->render($template, $data);
    }
}