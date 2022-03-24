<?php declare(strict_types = 1);

namespace Contember\NextrasOrmGenerator\Stubs;

abstract class Enum implements \JsonSerializable
{
	/** @var array<string, mixed> */
	private static $cache = [];

	/** @var array<string, array<string, static>> */
	private static $instances = [];

	/** @var mixed */
	protected $value;


	final protected function __construct(mixed $value)
	{
		$this->value = $value;
	}


	public function getValue(): mixed
	{
		return $this->value;
	}


	public function __toString(): string
	{
		return (string) $this->value;
	}


	public function jsonSerialize(): mixed
	{
		return $this->value;
	}


	/**
	 * @return array<string, string>
	 */
	public static function getConstList(): array
	{
		$cls = get_called_class();
		if (!isset(self::$cache[$cls])) {
			$rc = new \ReflectionClass($cls);
			self::$cache[$cls] = $rc->getConstants();
			unset(self::$cache[$cls]['__default']);
		}

		return self::$cache[$cls];
	}


	/**
	 * @return static[]
	 */
	public static function getAll(): array
	{
		$result = [];
		foreach (static::getConstList() as $const => $value) {
			$result[] = static::fromConstant($const);
		}

		return $result;
	}


	/**
	 * @param mixed[]  $args
	 */
	public static function __callStatic(string $name, array $args = []): static
	{
		return static::fromConstant($name);
	}


	public static function fromConstant(string $name): static
	{
		$cls = get_called_class();
		if (!isset(self::$instances[$cls][$name])) {
			if (!defined("static::$name")) {
				throw \App\Service\Contember\UnexpectedEnumValueException::outOfRange($name, static::getConstList());
			}
			self::$instances[$cls][$name] = new static(constant("static::$name"));
		}

		return self::$instances[$cls][$name];
	}


	public static function fromValue(string $value): static
	{
		$constants = static::getConstList();
		if (($constant = array_search($value, $constants, true)) === false) {
			throw \App\Service\Contember\UnexpectedEnumValueException::outOfRange($value, $constants);
		}

		return static::fromConstant($constant);
	}
}
