<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

class NamespaceTreeHelper
{
	/**
	 * @param mixed[] $tree
	 * @param string[] $path
	 * @return array<string, string>
	 */
	public static function generateNamespaceSuffixMap(array $tree, array $path = []): array
	{
		$map = [];
		foreach ($tree as $name => $node) {
			if (is_string($node)) {
				$map[$node] = implode('\\', $path);

			} elseif (is_array($node)) {
				$map += self::generateNamespaceSuffixMap($node, [...$path, $name]);

			} else {
				throw new \LogicException();
			}
		}
		return $map;
	}
}
