<?php

namespace Francerz\Render;

class HTML extends SuperContainer
{
    public static function include(string $view, array $data = [])
    {
        $view = static::getViewPath($view);
        (function () use ($view, $data) {
            extract($data);
            include $view;
        })();
    }

    public function startSection(string $section)
    {
        $__this = $this;
        ob_start(function (string $buffer) use ($__this, $section) {
            $__this->sections[$section] = $buffer;
            return $buffer;
        });
    }

    public function endSection()
    {
        ob_get_clean();
    }
}
