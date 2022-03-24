<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class ClassFile
{
	public function __construct(
		public string $className,
		public PhpFile $file,
		public PhpNamespace $namespace,
		public ClassType $class,
	) {}
}
