<?php

namespace Francerz\Render;

class HTML extends SuperContainer
{
    public function include($view, array $data = [])
    {
        extract($data);
        include $this->getViewPath($view);
    }
}
