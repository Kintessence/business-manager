<?php
declare(strict_types=1);

namespace BusinessEngine\BusinessProfile;

use BusinessEngine\ModuleInterface;
use BusinessEngine\BusinessProfile\Admin\BusinessProfileController;

final class BusinessProfileModule implements ModuleInterface
{
    public function init(): void
    {
        $controller = new BusinessProfileController();
        $controller->register();
    }
}