<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator\Generators;

use Contember\NextrasOrmGenerator\ClassFile;
use Contember\NextrasOrmGenerator\ClassFileFactory;
use Contember\NextrasOrmGenerator\ClassNamesProvider;
use Contember\NextrasOrmGenerator\DocDommentHelper;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\Json;
use stdClass;

class EntityGenerator
{
	public function __construct(
		private ClassNamesProvider $classNames,
		private ClassFileFactory $classFileFactory,
	)
	{
	}


	/**
	 * @param stdClass[] $otherEntities
	 */
	public function createEntity(stdClass $entity, array $otherEntities): ClassFile
	{
		$className = $this->classNames->generateEntityClassName($entity->name);
		$entityBaseClass = $this->classNames->getEntityBaseClass();

		$classFile = $this->classFileFactory->createClassFile($className);
		$classFile->namespace->addUse($entityBaseClass);

		$class = $classFile->class;
		$class->setFinal();
		$class->setExtends($entityBaseClass);
		$class->setComment($this->generateEntityDocComment($classFile->namespace, $entity, $otherEntities));
		$this->generateEntityMethods($entity, $otherEntities, $classFile->namespace, $class);

		return $classFile;
	}


	/**
	 * @param stdClass[] $otherEntities
	 */
	protected function generateEntityDocComment(PhpNamespace $namespace, stdClass $entity, array $otherEntities): string
	{
		$lines = [];
		foreach ($entity->fields as $field) {
			$lines[] = $this->generatePropertyLine($namespace, $field, $otherEntities);
		}

		return DocDommentHelper::generateDocComment($lines);
	}


	/**
	 * @param stdClass[] $otherEntities
	 * @return string[]
	 */
	protected function generatePropertyLine(PhpNamespace $namespace, stdClass $field, array $otherEntities): array
	{
		return [
			'@property-read',
			$this->generatePropertyType($namespace, $field),
			"\${$field->name}",
			implode(' ', $this->generatePropertyModifiers($namespace, $field, $otherEntities)),
		];
	}


	protected function generatePropertyType(PhpNamespace $namespace, stdClass $field): string
	{
		$baseType = match ($field->type) {
			'Bool' => 'bool',
			'Integer' => 'int',
			'Double' => 'float',
			'String', 'Uuid' => 'string',
			'Json' => 'mixed',
			'Date', 'DateTime' => $this->classNames->toShortName($namespace, \DateTimeImmutable::class),
			'Enum' => $this->classNames->toShortName($namespace, $this->classNames->generateEnumClassName($field->enumName)),
			'OneHasOne', 'ManyHasOne' => $this->classNames->toShortName($namespace, $this->classNames->generateEntityClassName($field->targetEntity)),
			'OneHasMany', 'ManyHasMany' => $this->classNames->toShortName($namespace, $this->classNames->generateCollectionClassName($field->targetEntity)),
			default => throw new \LogicException(),
		};

		$nullableSuffix = $field->nullable ? '|null' : '';
		return $baseType . $nullableSuffix;
	}

	/**
	 * @param stdClass[] $otherEntities
	 * @return string[]
	 */
	protected function generatePropertyModifiers(PhpNamespace $namespace, stdClass $field, array $otherEntities): array
	{
		static $relationshipMap = [
			'OneHasOne' => '1:1',
			'OneHasMany' => '1:m',
			'ManyHasOne' => 'm:1',
			'ManyHasMany' => 'm:m',
		];

		$modifiers = [];

		if ($field->name === 'id') {
			$modifiers[] = '{primary}';
		}

		if (isset($relationshipMap[$field->type])) {
			$type = $relationshipMap[$field->type];
			$targetEntity = $this->classNames->toShortName($namespace, $this->classNames->generateEntityClassName($field->targetEntity));

			$otherSide = $field->inversedBy ?? $field->ownedBy;
			$otherSide = $otherSide ? "::\${$otherSide}" : null;

			$args = ["{$targetEntity}{$otherSide}"];

			if ($field->side === 'owning' && $type !== 'm:1') {
				$args[] = 'isMain=true';
			}

			if ($otherSide === null) {
				$args[] = 'oneSided=true';
			}

			if ($field->inversedBy ?? $field->ownedBy) {
				$otherEntity = array_column($otherEntities, null, 'name')[$targetEntity];
				$otherField = array_column($otherEntity->fields, null, 'name')[$field->inversedBy ?? $field->ownedBy];

				if ($otherField->onDelete === 'cascade') {
					$args[] = 'cascade=[persist, remove]';
				}
			}

			if ($field->orderBy) {
				$segments = [];
				foreach ($field->orderBy as $by) {
					$segments[] = sprintf('%s=%s', implode('->', $by->path), strtoupper($by->direction));
				}
				$args[] = sprintf('orderBy=[%s]', implode(', ', $segments));
			}

			$modifiers[] = sprintf('{%s %s}', $type, implode(', ', $args));

		} elseif ($field->type === 'Enum') {
			$namespace->addUse('App');
			$wrapper = $this->classNames->getEnumWrapperClass(); // intentionally not shorten to avoid the use being removed by PhpStorm
			$modifiers[] = "{wrapper $wrapper}";

		} elseif ($field->type === 'Json') {
			$namespace->addUse('App');
			$wrapper = $this->classNames->getJsonWrapperClass(); // intentionally not shorten to avoid the use being removed by PhpStorm
			$modifiers[] = "{wrapper $wrapper}";
		}

		if (isset($field->defaultValue)) {
			$default = Json::encode($field->defaultValue);
			$modifiers[] = "{default $default}";
		}

		return $modifiers;
	}


	protected function generateEntityMethods(stdClass $entity, array $otherEntities, PhpNamespace $namespace, ClassType $class): void
	{
		foreach ($entity->fields as $field) {
			if ($field->type !== 'OneHasMany') {
				continue;
			}
			$targets = array_values(array_filter($otherEntities, fn(\stdClass $entity) => $entity->name === $field->targetEntity));
			assert(count($targets) === 1);
			$target = $targets[0];
			$targetEntity = $this->classNames->generateEntityClassName($target->name);
			foreach ($target->unique as $unique) {
				if (count($unique->fields) !== 2) {
					continue;
				}
				$otherFields = array_values(array_filter($unique->fields, fn(string $fieldName) => $fieldName !== $field->ownedBy));
				if (count($otherFields) !== 1) {
					continue;
				}
				$otherFields = array_values(array_filter($target->fields, fn(\stdClass $field) => $field->name === $otherFields[0]));
				assert(count($otherFields) === 1);
				$otherField = clone $otherFields[0];
				$otherField->nullable = false;
				$propertyType = $this->generatePropertyType($namespace, $otherField);
				$propertyType = $namespace->getUses()[$propertyType] ?? $propertyType;

				$methodName = sprintf('get%sBy%s', ucfirst($field->name), ucfirst($otherField->name));
				if (!$class->hasMethod($methodName)) {
					$method = $class->addMethod($methodName);
					$method->setReturnType($targetEntity);
					$method->setReturnNullable();
					$method->addParameter($otherField->name)->setType($propertyType);
					$method->setBody(sprintf('return $this->%s->getBy([? => $%s]);', $field->name, $otherField->name), [$otherField->name]);
				}

				$methodName2 = $methodName . 'Checked';
				if (!$class->hasMethod($methodName2)) {
					$method = $class->addMethod($methodName2);
					$method->setReturnType($targetEntity);
					$method->addParameter($otherField->name)->setType($propertyType);
					$method->setBody(sprintf('return $this->%s->getByChecked([? => $%s]);', $field->name, $otherField->name), [$otherField->name]);
				}
			}
		}
	}

}
