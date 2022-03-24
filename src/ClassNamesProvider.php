<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

use Nette\PhpGenerator\PhpNamespace;

class ClassNamesProvider
{
	/** @var null|string[] index by [entityName] => suffix */
	private ?array $namespaceSuffixMap;


	public function __construct(
		private Config $config,
	)
	{
		$this->namespaceSuffixMap = $this->config->namespaceTree !== null
			? NamespaceTreeHelper::generateNamespaceSuffixMap($this->config->namespaceTree)
			: null;
	}


	public function toShortName(PhpNamespace $namespace, string $fullName): string
	{
		$namespace->addUse($fullName);
		return $namespace->simplifyName($fullName); // todo: verify
	}

	public function generateEntityClassName(string $entityName): string
	{
		return "{$this->config->entityNamespace}{$this->getNamespaceSuffix($entityName)}\\{$entityName}";
	}


	public function generateRepositoryClassName(string $entityName): string
	{
		return "{$this->config->repositoryNamespace}{$this->getNamespaceSuffix($entityName)}\\{$entityName}Repository";
	}


	public function generateMapperClassName(string $entityName): string
	{
		return "{$this->config->mapperNamespace}{$this->getNamespaceSuffix($entityName)}\\{$entityName}Mapper";
	}


	public function generateCollectionClassName(string $entityName): string
	{
		return "{$this->config->collectionNamespace}{$this->getNamespaceSuffix($entityName)}\\{$entityName}Collection";
	}


	public function generateModelClassName(): string
	{
		return $this->config->modelClassName;
	}

	public function generateEnumClassName(string $enumName): string
	{
		return "{$this->config->enumNamespace}\\{$enumName}";
	}


	public function generateEnumBaseClassName(): string
	{
		return $this->config->enumBaseClass ?? $this->config->coreNamespace . '\\Enum';
	}


	public function getCollectionBaseClass(): string
	{
		return $this->config->collectionBaseClass ?? $this->config->coreNamespace . '\\Collection';
	}

	public function getEntityBaseClass(): string
	{
		return $this->config->entityBaseClass ?? $this->config->coreNamespace . '\\Entity';
	}

	public function getMapperBaseClass(): string
	{
		return $this->config->mapperBaseClass ?? $this->config->coreNamespace . '\\Mapper';
	}

	public function getRepositoryBaseClass(): string
	{
		return $this->config->repositoryBaseClass ?? $this->config->coreNamespace . '\\Repository';
	}

	public function getEnumWrapperClass(): string
	{
		return $this->config->enumWrapperClass ?? $this->config->coreNamespace . '\\EnumProperty';
	}

	public function getJsonWrapperClass(): string
	{
		return $this->config->jsonWrapperClass ?? $this->config->coreNamespace . '\\JsonProperty';
	}

	public function getModelBaseClass(): string
	{
		return 'Nextras\\Orm\\Model\\Model';
	}


	private function getNamespaceSuffix(string $entityName): string
	{
		if ($this->namespaceSuffixMap === null) {
			return '';
		}
		if (!isset($this->namespaceSuffixMap[$entityName])) {
			throw new \Exception("Namespace not found for $entityName");
		}
		return "\\{$this->namespaceSuffixMap[$entityName]}";
	}
}
