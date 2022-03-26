<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

class ClassFileFactory
{
	public function __construct(
	) {
	}

	public function createClassFile(string $className): ClassFile
	{
		$file = $this->generateFile();
		$namespace = $this->generateNamespace($file, $className);
		$class = $this->generateClass($namespace, $className);

		return new ClassFile($className, $file, $namespace, $class);
	}

	protected function generateFile(): PhpFile
	{
		return (new PhpFile())->setStrictTypes();
	}

	protected function generateNamespace(PhpFile $file, string $className): PhpNamespace
	{
		return $file->addNamespace(substr($className, 0, strrpos($className, '\\') ?: null));
	}

	protected function generateClass(PhpNamespace $namespace, string $className): ClassType
	{
		if (class_exists($className)) {
			$file = (new \ReflectionClass($className))->getFileName();
			assert($file !== false);

			$content = FileSystem::read($file);
			$lines = explode("\n", $content);

			$class = ClassType::from($className);
			$namespace->add($class);

			foreach ($class->getMethods() as $method) {
				$methodReflection = new \ReflectionMethod($className, $method->getName());
				$methodLines = array_slice($lines, $methodReflection->getStartLine() - 0, $methodReflection->getEndLine() - $methodReflection->getStartLine() - 1);
				$methodBody = Strings::replace(Strings::after(implode("\n", $methodLines), '{') ?? '', '#^\t\t#m', '');
				$method->setBody($methodBody);

				foreach ($method->getParameters() as $parameter) {
					$type = $parameter->getType();

					if ($type !== null && ctype_upper($type[0])) {
						$namespace->addUse($type);
					}

					if ($parameter->hasDefaultValue() && $parameter->getDefaultValue() === null) {
						$parameter->setNullable(true);
					}
				}
			}

			foreach (Strings::matchAll($content, '#^use ([\\w\\\\]++);#m') as $match) {
				$namespace->addUse($match[1]);
			}

		} else {
			$class = $namespace->addClass($namespace->simplifyName($className));
		}

		return $class;
	}
}
