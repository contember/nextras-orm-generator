<?php declare(strict_types = 1);

namespace Contember\NextrasOrmGenerator\Stubs;

use Nette\Utils\Json;
use Nextras\Orm\Entity\ImmutableValuePropertyWrapper;


final class JsonProperty extends ImmutableValuePropertyWrapper
{
	public function convertFromRawValue($value)
	{
		if ($value === null) {
			return null;
		}
		assert(is_string($value));
		return Json::decode($value, Json::FORCE_ARRAY);
	}


	public function convertToRawValue($value)
	{
		if ($value === null) {
			return null;
		}
		return Json::encode($value);
	}
}
