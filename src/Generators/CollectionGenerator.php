<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator\Generators;

use Contember\NextrasOrmGenerator\ClassFile;
use Contember\NextrasOrmGenerator\ClassFileFactory;
use Contember\NextrasOrmGenerator\ClassNamesProvider;
use Contember\NextrasOrmGenerator\DocDommentHelper;
use Nette\PhpGenerator\PhpNamespace;

class CollectionGenerator
{

	public function __construct(
		private ClassNamesProvider $classNames,
		private ClassFileFactory $classFileFactory,
	)
	{
	}


	public function createCollection(\stdClass $entity): ClassFile
	{
		$className = $this->classNames->generateCollectionClassName($entity->name);
		$collectionBaseClass = $this->classNames->getCollectionBaseClass();

		$classFile = $this->classFileFactory->createClassFile($className);
		$classFile->namespace->addUse($collectionBaseClass);

		$class = $classFile->class;
		$class->setFinal();
		$class->setExtends($collectionBaseClass);
		$class->setComment($this->generateCollectionDocComment($classFile->namespace, $entity));

		return $classFile;
	}

	protected function generateCollectionDocComment(PhpNamespace $namespace, \stdClass $entity): string
	{
		$entityClassName = $this->classNames->toShortName($namespace, $this->classNames->generateEntityClassName($entity->name));
		$traversableClassName = $this->classNames->toShortName($namespace, \Traversable::class);
		$parentClassName = $this->classNames->toShortName($namespace, $this->classNames->getCollectionBaseClass());

		$methodLines = [
			['@method', "{$entityClassName}|null", "getBy(array \$conds)"],
			['@method', "{$entityClassName}", "getByChecked(array \$conds)"],
			['@method', "{$entityClassName}|null", "getById(\$id)"],
			['@method', "{$entityClassName}", "getByIdChecked(\$id)"],
			['@method', 'self', "findBy(array \$conds)"],
			['@method', 'self', "orderBy(\$column, string \$direction = self::ASC)"],
			['@method', 'self', "resetOrderBy()"],
			['@method', 'self', "limitBy(int \$limit, ?int \$offset = null)"],
			['@method', "{$entityClassName}|null", "fetch()"],
			['@method', "{$entityClassName}[]", "fetchAll()"],
			['@method', "{$traversableClassName}|{$entityClassName}[]", "getIterator()"],
		];

		$otherLines = [
			['@phpstan-extends', "{$parentClassName}<{$entityClassName}>"],
		];

		return DocDommentHelper::generateDocComment($methodLines, [[]], $otherLines);
	}
}
