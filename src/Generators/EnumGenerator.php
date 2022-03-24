<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator\Generators;

use Contember\NextrasOrmGenerator\ClassFile;
use Contember\NextrasOrmGenerator\ClassFileFactory;
use Contember\NextrasOrmGenerator\ClassNamesProvider;

class EnumGenerator
{
	public function __construct(
		private ClassNamesProvider $classNames,
		private ClassFileFactory $classFileFactory,
	)
	{
	}

	public function createEnum(\stdClass $enum): ClassFile
	{
		$className = $this->classNames->generateEnumClassName($enum->name);
		$baseEnum = $this->classNames->generateEnumBaseClassName();

		$classFile = $this->classFileFactory->createClassFile($className);
		$classFile->namespace->addUse($baseEnum);

		$class = $classFile->class;
		$class->setFinal();
		$class->setExtends($baseEnum);
		$class->setComment(null);
		$class->addComment('[READ ONLY] - Generated file');
		foreach ($enum->values as $value) {
			$underscored = preg_replace('~([a-z])([A-Z])~', '$1_$2', $value);
			assert(is_string($underscored));
			$ucName = strtoupper($underscored);
			$class->addConstant($ucName, $value)->setPublic();
			$class->addComment(sprintf('@method static static %s()', $ucName));
		}

		return $classFile;
	}
}
