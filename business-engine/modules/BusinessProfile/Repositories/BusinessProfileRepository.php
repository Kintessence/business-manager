<?php
declare(strict_types=1);

namespace BusinessEngine\BusinessProfile\Repositories;

use BusinessEngine\BusinessProfile\DTOs\BusinessProfileDTO;
use BusinessEngine\BusinessProfile\Services\ProfileCalculator;

final class BusinessProfileRepository
{
    private const OPTION_KEY = 'be_business_profile';

    public function get(): BusinessProfileDTO
    {
        $saved = get_option(self::OPTION_KEY, null);
        if (!is_array($saved)) {
            $defaultData = ProfileCalculator::calculate([]);
            return BusinessProfileDTO::fromArray($defaultData);
        }
        return BusinessProfileDTO::fromArray($saved);
    }

    public function save(array $rawData): BusinessProfileDTO
    {
        $calculated = ProfileCalculator::calculate($rawData);
        update_option(self::OPTION_KEY, $calculated);
        return BusinessProfileDTO::fromArray($calculated);
    }
}