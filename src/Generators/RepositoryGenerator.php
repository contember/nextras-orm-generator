<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator\Generators;

use Contember\NextrasOrmGenerator\ClassFile;
use Contember\NextrasOrmGenerator\ClassFileFactory;
use Contember\NextrasOrmGenerator\ClassNamesProvider;
use Contember\NextrasOrmGenerator\DocDommentHelper;
use Nette\PhpGenerator\PhpNamespace;

class RepositoryGenerator
{
	public function __construct(
		private ClassNamesProvider $classNames,
		private ClassFileFactory $classFileFactory,
	)
	{
	}

	public function createRepository(\stdClass $entity): ClassFile
	{
		$className = $this->classNames->generateRepositoryClassName($entity->name);
		$baseClass = $this->classNames->getRepositoryBaseClass();

		$classFile = $this->classFileFactory->createClassFile($className);
		$classFile->namespace->addUse($baseClass);

		$class = $classFile->class;
		$class->setFinal();
		$class->setExtends($baseClass);
		$class->setComment($this->generateRepositoryDocComment($classFile->namespace, $entity));

		$entityClassName = $this->classNames->toShortName($classFile->namespace, $this->classNames->generateEntityClassName($entity->name));
		$getEntityClassNames = $class->addMethod('getEntityClassNames');
		$getEntityClassNames->setStatic();
		$getEntityClassNames->setReturnType('array');
		$getEntityClassNames->setBody(sprintf('return [%s::class];', $entityClassName));

		return $classFile;
	}


	protected function generateRepositoryDocComment(PhpNamespace $namespace, \stdClass $entity): string
	{
		$mapperClassname = $this->classNames->toShortName($namespace, $this->classNames->generateMapperClassName($entity->name));
		$entityClassName = $this->classNames->toShortName($namespace, $this->classNames->generateEntityClassName($entity->name));
		$collectionClassName = $this->classNames->toShortName($namespace, $this->classNames->generateCollectionClassName($entity->name));

		$lines = [
			['@method', "{$mapperClassname}", "getMapper()"],
			['@method', "{$entityClassName}|null", "hydrateEntity(array \$data)"],
			['@method', "void", "attach({$entityClassName} \$entity)"],
			['@method', "void", "detach({$entityClassName} \$entity)"],
			['@method', "{$entityClassName}|null", "getBy(array \$conds)"],
			['@method', "{$entityClassName}", "getByChecked(array \$conds)"],
			['@method', "{$entityClassName}|null", "getById(string \$primaryValue)"],
			['@method', "{$entityClassName}", "getByIdChecked(string \$primaryValue)"],
			['@method', "{$collectionClassName}", "findAll()"],
			['@method', "{$collectionClassName}", "findBy(array \$where)"],
			['@method', "{$collectionClassName}", "findByIds(string[] \$primaryValues)"],
			['@method', "{$entityClassName}", "persist({$entityClassName} \$entity, bool \$withCascade = true)"],
			['@method', "{$entityClassName}", "persistAndFlush({$entityClassName} \$entity, bool \$withCascade = true)"],
			['@method', "{$entityClassName}", "remove({$entityClassName} \$entity, bool \$withCascade = true)"],
			['@method', "{$entityClassName}", "removeAndFlush({$entityClassName} \$entity, bool \$withCascade = true)"],
		];

		return DocDommentHelper::generateDocComment($lines);
	}
}
