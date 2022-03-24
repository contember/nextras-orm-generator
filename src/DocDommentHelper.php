<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator;

class DocDommentHelper
{


	/**
	 * @param string[][] ...$lines
	 */
	public static function generateDocComment(array ...$lines): string
	{
		$doc = [];

		foreach ($lines as $group) {
			$columnCount = max(0, ...array_map('count', $group));
			$columnLengths = [];
			for ($i = 0; $i < $columnCount - 1; $i++) {
				$columnLengths[$i] = max(0, ...array_map('strlen', array_column($group, $i)));
			}

			foreach ($group as $lineNum => $cells) {
				foreach ($cells as $columnNum => $cell) {
					$group[$lineNum][$columnNum] = str_pad($cell, $columnLengths[$columnNum] ?? 0);
				}
			}

			$doc[] = implode("\n", array_map(fn(array $line) => implode(' ', $line), $group));
		}

		return implode("\n", $doc);
	}
}
