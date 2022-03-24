<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator\Generators;

use Contember\NextrasOrmGenerator\ClassFile;
use Contember\NextrasOrmGenerator\ClassFileFactory;
use Contember\NextrasOrmGenerator\ClassNamesProvider;
use Contember\NextrasOrmGenerator\DocDommentHelper;
use Nette\PhpGenerator\PhpNamespace;
use Nextras\Orm\Collection\ICollection;

class MapperGenerator
{
	public function __construct(
		private ClassNamesProvider $classNames,
		private ClassFileFactory $classFileFactory,
	)
	{
	}

	public function createMapper(\stdClass $entity): ClassFile
	{
		$className = $this->classNames->generateMapperClassName($entity->name);
		$mapperBaseClass = $this->classNames->getMapperBaseClass();

		$classFile = $this->classFileFactory->createClassFile($className);
		$classFile->namespace->addUse($mapperBaseClass);
		$classFile->namespace->addUse(ICollection::class);


		$class = $classFile->class;
		$class->setFinal();
		$class->setExtends($mapperBaseClass);
		$class->setComment($this->generateMapperDocComment($classFile->namespace, $entity));

		$collectionClassName = $this->classNames->toShortName($classFile->namespace, $this->classNames->generateCollectionClassName($entity->name));
		$wrapCollection = $class->addMethod('wrapCollection');
		$wrapCollection->setPublic();
		$wrapCollection->addParameter('collection')->setType(ICollection::class);
		$wrapCollection->setReturnType($this->classNames->generateCollectionClassName($entity->name));
		$wrapCollection->setBody(sprintf('return new %s($collection);', $collectionClassName));

		return $classFile;
	}

	protected function generateMapperDocComment(PhpNamespace $namespace, \stdClass $entity): string
	{
		$collectionClassName = $this->classNames->toShortName($namespace, $this->classNames->generateCollectionClassName($entity->name));

		$lines = [
			['@method', "{$collectionClassName}", "toCollection(\$data)"],
		];

		return DocDommentHelper::generateDocComment($lines);
	}
}
