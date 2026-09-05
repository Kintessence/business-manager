<?php
declare(strict_types=1);

namespace BusinessEngine\Ingredients;

use BusinessEngine\ModuleInterface;
use BusinessEngine\Ingredients\Admin\IngredientController;

final class IngredientsModule implements ModuleInterface
{
    public function init(): void
    {
        $controller = new IngredientController();
        $controller->register();
    }
}