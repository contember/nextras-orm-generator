<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator\Generators;

use Contember\NextrasOrmGenerator\ClassFile;
use Contember\NextrasOrmGenerator\ClassFileFactory;
use Contember\NextrasOrmGenerator\ClassNamesProvider;
use Contember\NextrasOrmGenerator\DocDommentHelper;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\Strings;
use Nextras\Orm\Model\Model;

class ModelGenerator
{
	public function __construct(
		private ClassNamesProvider $classNames,
		private ClassFileFactory $classFileFactory,
	)
	{
	}

	/**
	 * @param \stdClass[] $entities
	 */
	public function createModel(array $entities): ClassFile
	{
		$className = $this->classNames->generateModelClassName();
		$baseClass = $this->classNames->getModelBaseClass();

		$classFile = $this->classFileFactory->createClassFile($className);
		$classFile->namespace->addUse(Model::class);

		$class = $classFile->class;
		$class->setFinal();
		$class->setExtends($baseClass);
		$class->setComment($this->generateModelDocComment($classFile->namespace, $entities));

		return $classFile;
	}


	/**
	 * @param \stdClass[] $entities
	 */
	protected function generateModelDocComment(PhpNamespace $namespace, array $entities): string
	{
		usort($entities, fn(\stdClass $a, \stdClass $b) => $a->name <=> $b->name);

		$lines = [];
		$ns = null;
		$namespace->addUse('App');

		foreach ($entities as $entity) {
			$entityNs = Strings::before($entity->name, '\\', -1);
			if ($ns !== null && $entityNs !== $ns) {
				$lines[] = [];
			}

			$ns = $entityNs;
			$repositoryClassName = $this->classNames->generateRepositoryClassName($entity->name);
			$propertyName = lcfirst($entity->name);
			$lines[] = ['@property-read', "$repositoryClassName", "\${$propertyName}"];
		}

		return DocDommentHelper::generateDocComment($lines);
	}
}
