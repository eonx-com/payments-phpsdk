<?php
declare(strict_types=1);

namespace Tests\EoneoPay\PhpSdk\Validation;

use EoneoPay\Utils\AnnotationReader;
use LoyaltyCorp\SdkBlueprint\Sdk\Interfaces\EntityInterface;
use ReflectionClass;
use Symfony\Component\Serializer\Annotation\Groups;
use Tests\EoneoPay\PhpSdk\Helpers\InterfaceFinder;
use Tests\EoneoPay\PhpSdk\TestCase;

class EntityGroupsTest extends TestCase
{
    /**
     * Test that all properties of 'Entities' have Groups annotations.
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     * @throws \ReflectionException
     */
    public function testGroups(): void
    {
        $reader = new AnnotationReader();

        $finder = new InterfaceFinder(dirname(__DIR__, 2) . '/src/Endpoints');
        $classes = $finder->find(EntityInterface::class);

        $groups = [];
        foreach ($classes as $class) {
            $groups[$class] = $reader->getClassPropertyAnnotation($class, Groups::class);
        }

        foreach ($groups as $class => $groupValues) {
            $reflectionProperties = (new ReflectionClass($class))->getProperties();
            $classProperties = \array_map(static function ($prop) { return $prop->name;}, $reflectionProperties);
            $groupProperties = \array_keys($groupValues);
            // get list of properties attached to $class
            self::assertEquals($classProperties, $groupProperties, \sprintf('Missing @Groups on %s', $class));
        }
    }
}