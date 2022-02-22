<?php

namespace Francerz\Render;

class HTML extends SuperContainer
{
    private static $layout;
    private static $sections = [];

    public static function layout(string $layout, array $data = [])
    {
        self::$layout = (object)[
            'layout' => static::getViewPath($layout),
            'data' => $data
        ];
        ob_start();
    }

    public static function include(string $view, array $data = [])
    {
        $view = static::getViewPath($view);
        (function () use ($view, $data) {
            extract($data);
            include $view;
        })();
    }

    public static function startSection(string $section)
    {
        ob_start(function (string $buffer) use ($section) {
            self::$sections[$section] =
                self::$sections[$section] ?? '' .
                $buffer;
            return $buffer;
        });
    }

    public static function endSection()
    {
        ob_end_clean();
    }

    public static function section(string $section)
    {
        echo self::$sections[$section] ?? '';
    }

    public static function render()
    {
        if (isset(self::$layout)) {
            ob_end_clean();
            (function () {
                extract(self::$layout->data);
                include self::$layout->layout;
            })();
            return;
        }
        echo ob_get_clean();
    }
}
