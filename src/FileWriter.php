<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Printer;
use Nette\Utils\FileSystem;

class FileWriter
{
	public function __construct(
		private string $baseDir,
		private string $baseNamespace,
	)
	{
	}

	public function writeFile(Printer $printer, PhpFile $file, string $className): void
	{
		$content = $printer->printFile($file);
		$content = str_replace("<?php\n\ndeclare(strict_types=1)", "<?php declare(strict_types = 1)", $content);
		$content = str_replace(";\n\n/**\n", ";\n\n\n/**\n", $content);
		$content = str_replace(";\n\nfinal class", ";\n\n\nfinal class", $content);
		$this->writeFileContent($content, $className);
	}

	public function writeFileContent(string $content, string $className): void
	{
		$path = rtrim($this->baseDir, '/') . '/' . strtr(substr($className, strlen(rtrim($this->baseNamespace, '\\')) + 1), '\\', '/') . '.php';
		FileSystem::write($path, $content);
	}
}
