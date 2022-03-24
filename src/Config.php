<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

class Config
{
	public function __construct(
		public string $dir,
		public string $baseNamespace = 'App',
		public string $coreNamespace = 'App\\Core\\Nextras',
		public string $repositoryNamespace = 'App\\Orm\\Repository',
		public string $entityNamespace = 'App\\Orm\\Entity',
		public string $mapperNamespace = 'App\\Orm\\Mapper',
		public string $collectionNamespace = 'App\\Orm\\Mapper',
		public string $enumNamespace = 'App\\Orm\\Enum',
		public string $modelClassName = 'App\\Orm\\Model',
		public array|null $namespaceTree = null,
		public ?string $collectionBaseClass = null,
		public ?string $entityBaseClass = null,
		public ?string $mapperBaseClass = null,
		public ?string $repositoryBaseClass = null,
		public ?string $enumBaseClass = null,
		public ?string $enumWrapperClass = null,
		public ?string $jsonWrapperClass = null,
	)
	{
	}
}
