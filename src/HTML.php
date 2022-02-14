<?php

namespace Francerz\Render;

class HTML extends SuperContainer
{
    public function include($view, array $data = [])
    {
        extract($data);
        include $this->getViewPath($view);
    }

    public function layout(string $layout, array $data = [])
    {
    }

    public function section(string $section, array $data = [])
    {
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
