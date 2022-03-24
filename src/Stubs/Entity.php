<?php declare(strict_types=1);

namespace Contember\NextrasOrmGenerator\Stubs;

use Nextras;
use Nextras\Orm\Relationships\IRelationshipCollection;


abstract class Entity extends Nextras\Orm\Entity\Entity
{
	public function __construct()
	{
		parent::__construct();
		$this->setReadOnlyValue('id', self::uuidv4());
	}


	public function &__get(string $name): mixed
	{
		$value = $this->getValue($name);

		if ($value instanceof IRelationshipCollection) {
			$value = $value->toCollection();
		}

		return $value;
	}


	protected function getRelationship(string $name): IRelationshipCollection
	{
		$value = $this->getValue($name);
		assert($value instanceof IRelationshipCollection);

		return $value;
	}


	private static function uuidv4(): string
	{
		$data = random_bytes(16);
		assert(strlen($data) == 16);

		// Set version to 0100
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		// Set bits 6-7 to 10
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		// Output the 36 character UUID.
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}
