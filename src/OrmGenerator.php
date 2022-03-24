<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

use Contember\NextrasOrmGenerator\Generators\CollectionGenerator;
use Contember\NextrasOrmGenerator\Generators\EntityGenerator;
use Contember\NextrasOrmGenerator\Generators\EnumGenerator;
use Contember\NextrasOrmGenerator\Generators\MapperGenerator;
use Contember\NextrasOrmGenerator\Generators\ModelGenerator;
use Contember\NextrasOrmGenerator\Generators\RepositoryGenerator;
use Nette\PhpGenerator\Printer;


final class OrmGenerator
{
	public function __construct(
		private CollectionGenerator $collectionGenerator,
		private EntityGenerator $entityGenerator,
		private EnumGenerator $enumGenerator,
		private MapperGenerator $mapperGenerator,
		private ModelGenerator $modelGenerator,
		private RepositoryGenerator $repositoryGenerator,
		private CoreFilesCopier $coreFilesCopier,
		private FileWriter $fileWriter,
	)
	{
	}



	public function generate(\stdClass $schema): void
	{
		$files = [];
		foreach ($schema->entities as $entity) {

			$files[] = $this->entityGenerator->createEntity($entity, $schema->entities);
			$files[] = $this->repositoryGenerator->createRepository($entity);
			$files[] = $this->mapperGenerator->createMapper($entity);
			$files[] = $this->collectionGenerator->createCollection($entity);
		}

		foreach ($schema->enums as $enum) {
			$files[] = $this->enumGenerator->createEnum( $enum);
		}

		$files[] = $this->modelGenerator->createModel($schema->entities);

		$printer = new Printer();
		foreach ($files as $file) {
			$this->fileWriter->writeFile($printer, $file->file, $file->className);
		}
		$this->coreFilesCopier->copyCoreFiles();
	}
}
