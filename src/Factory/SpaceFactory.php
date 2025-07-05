<?php

namespace App\Factory;

use App\Entity\Space;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Space>
 */
final class SpaceFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Space::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'capacity' => self::faker()->numberBetween(1, 100),
            'description' => self::faker()->paragraph(2),
            'name' => self::faker()->sentence(3),
            'photos' => self::generarPhotosJson(),
            'schedules' => self::generarScheduleJson(),
            'typeSpace' => TypeSpaceFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Space $space): void {})
        ;
    }

    function generarScheduleJson(int $cantidad = 3): string
    {
        $schedules = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $timestamp = strtotime('+' . rand(0, 10) . ' days');
            $date = date(DATE_ATOM, $timestamp); // Formato ISO 8601


            $startHour = str_pad((string) rand(6, 20), 2, '0', STR_PAD_LEFT);
            $startMin = str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT);

            $endHour = str_pad((string) rand((int)$startHour + 1, 22), 2, '0', STR_PAD_LEFT);
            $endMin = str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT);

            $schedules[] = [
                'date' => $date,
                'startTime' => "$startHour:$startMin",
                'endTime' => "$endHour:$endMin",
                'available' => false,
            ];
        }
        return json_encode($schedules);
    }

    function generarPhotosJson(int $cantidad = 3): string
    {
        $urls = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $id = rand(1, 1000);
            $urls[] = "https://picsum.photos/id/$id/640/480";
        }

        return json_encode($urls);
    }
}
