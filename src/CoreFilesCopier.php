<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

use Contember\NextrasOrmGenerator\Stubs\Collection;
use Contember\NextrasOrmGenerator\Stubs\Entity;
use Contember\NextrasOrmGenerator\Stubs\Enum;
use Contember\NextrasOrmGenerator\Stubs\EnumProperty;
use Contember\NextrasOrmGenerator\Stubs\JsonProperty;
use Contember\NextrasOrmGenerator\Stubs\Mapper;
use Contember\NextrasOrmGenerator\Stubs\Repository;

class CoreFilesCopier
{
	private const SOURCE_NS = 'Contember\\NextrasOrmGenerator\\Stubs';

	public function __construct(
		private Config $config,
		private FileWriter $fileWriter,
	)
	{
	}

	public function copyCoreFiles()
	{
		$this->maybeCopyCoreFile($this->config->entityBaseClass, Entity::class);
		$this->maybeCopyCoreFile($this->config->collectionBaseClass, Collection::class);
		$this->maybeCopyCoreFile($this->config->mapperBaseClass, Mapper::class);
		$this->maybeCopyCoreFile($this->config->repositoryBaseClass, Repository::class);
		$this->maybeCopyCoreFile($this->config->enumBaseClass, Enum::class);
		$this->maybeCopyCoreFile($this->config->enumWrapperClass, EnumProperty::class);
		$this->maybeCopyCoreFile($this->config->jsonWrapperClass, JsonProperty::class);
	}

	private function maybeCopyCoreFile(?string $customClass, string $templateClass): void
	{
		if ($customClass !== null) {
			return;
		}
		$rc = new \ReflectionClass($templateClass);
		$code = file_get_contents($rc->getFileName());
		$code = preg_replace('~' . preg_quote('namespace ' . self::SOURCE_NS . ';') . '~', 'namespace ' . $this->config->coreNamespace . ';', $code, 1);
		$newClassName = $this->config->coreNamespace . substr($templateClass, strlen(self::SOURCE_NS));
		$this->fileWriter->writeFileContent($code, $newClassName);
	}
}
