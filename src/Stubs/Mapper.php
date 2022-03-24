<?php declare(strict_types = 1);

namespace Contember\NextrasOrmGenerator\Stubs;

use Nextras;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;


abstract class Mapper extends Nextras\Orm\Mapper\Mapper
{
	public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper): array
	{
		$conventions = $this->getConventions();
		$sourceTable = $conventions->getStorageTable()->name;
		$targetTable = $targetMapper->getConventions()->getStorageTable()->name;
		$storageName = $sourceTable . '_' . $conventions->convertEntityToStorageKey($sourceProperty->name);

		return [
			$storageName,
			["{$sourceTable}_id", "{$targetTable}_id"],
		];
	}


	public function findAll(): ICollection
	{
		return $this->wrapCollection(parent::findAll());
	}


	public function toCollection($data): ICollection
	{
		return $this->wrapCollection(parent::toCollection($data));
	}


	abstract public function wrapCollection(ICollection $collection): ICollection;
}
