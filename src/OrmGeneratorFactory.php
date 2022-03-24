<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

use Contember\NextrasOrmGenerator\Generators\CollectionGenerator;
use Contember\NextrasOrmGenerator\Generators\EntityGenerator;
use Contember\NextrasOrmGenerator\Generators\EnumGenerator;
use Contember\NextrasOrmGenerator\Generators\MapperGenerator;
use Contember\NextrasOrmGenerator\Generators\ModelGenerator;
use Contember\NextrasOrmGenerator\Generators\RepositoryGenerator;

class OrmGeneratorFactory
{
	public function create(Config $config): OrmGenerator
	{
		$classNamesProvider = new ClassNamesProvider($config);
		$classFileFactory = new ClassFileFactory();
		$fileWriter = new FileWriter($config->dir, $config->baseNamespace);
		return new OrmGenerator(
			new CollectionGenerator($classNamesProvider, $classFileFactory),
			new EntityGenerator($classNamesProvider, $classFileFactory),
			new EnumGenerator($classNamesProvider, $classFileFactory),
			new MapperGenerator($classNamesProvider, $classFileFactory),
			new ModelGenerator($classNamesProvider, $classFileFactory),
			new RepositoryGenerator($classNamesProvider, $classFileFactory),
			new CoreFilesCopier($config, $fileWriter),
			$fileWriter,
		);
	}
}
