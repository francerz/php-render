<?php

namespace Francerz\Render;

use Francerz\FileSystem\Utils\Path;
use Francerz\PowerData\Strings;
use LogicException;

/**
 * @internal
 */
abstract class SuperContainer
{
    private static $sharedViewsPath = null;

    protected static function setSharedViewsPath(?string $viewsPath)
    {
        static::$sharedViewsPath = $viewsPath;
    }

    protected static function getSharedViewsPath(): ?string
    {
        return static::$sharedViewsPath;
    }

    protected function getViewPath(string $view)
    {
        if (!empty(static::$sharedViewsPath)) {
            $view = Path::join(static::$sharedViewsPath, $view);
        }
        if (!file_exists($view) && !Strings::endsWith($view, '.php')) {
            $view .= '.php';
        }
        if (!file_exists($view)) {
            throw new LogicException("File '{$view}' doest not exists.");
        }
        return $view;
    }
}
