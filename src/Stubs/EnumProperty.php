<?php declare(strict_types = 1);

namespace Contember\NextrasOrmGenerator\Stubs;

use Nextras\Orm\Entity\ImmutableValuePropertyWrapper;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;


final class EnumProperty extends ImmutableValuePropertyWrapper
{
	/** @var class-string<Enum> */
	private string $enumClass;


	public function __construct(PropertyMetadata $propertyMetadata)
	{
		parent::__construct($propertyMetadata);

		assert(count($propertyMetadata->types) === 1);
		$enumClass = array_key_first($propertyMetadata->types);

		assert(is_string($enumClass));
		assert(class_exists($enumClass));
		$this->enumClass = $enumClass;
	}


	public function convertFromRawValue($value): ?Enum
	{
		return $value !== null ? ($this->enumClass)::fromValue($value) : null;
	}


	public function convertToRawValue($value)
	{
		if ($value === null) {
			return null;
		}
		assert($value instanceof Enum);
		return $value->getValue();
	}
}
