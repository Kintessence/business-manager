<?php
declare(strict_types=1);

namespace BusinessEngine\Recipes;

use BusinessEngine\ModuleInterface;
use BusinessEngine\Recipes\Admin\RecipeController;

final class RecipesModule implements ModuleInterface
{
    public function init(): void
    {
        $controller = new RecipeController();
        $controller->register();
    }
}