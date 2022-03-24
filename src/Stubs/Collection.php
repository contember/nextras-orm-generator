<?php declare(strict_types = 1);

namespace Contember\NextrasOrmGenerator\Stubs;

use Iterator;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Mapper\IRelationshipMapper;


/**
 * @phpstan-template T of IEntity
 */
abstract class Collection implements ICollection
{
	final public function __construct(protected ICollection $inner)
	{
	}


	public function getBy(array $conds): ?IEntity
	{
		return $this->inner->getBy($conds);
	}


	public function getByChecked(array $conds): IEntity
	{
		return $this->inner->getByChecked($conds);
	}


	public function getById($id): ?IEntity
	{
		return $this->inner->getById($id);
	}


	public function getByIdChecked($id): IEntity
	{
		return $this->inner->getByIdChecked($id);
	}


	public function findBy(array $conds): static
	{
		return new static($this->inner->findBy($conds));
	}


	public function orderBy($column, string $direction = self::ASC): static
	{
		return new static($this->inner->orderBy($column, $direction));
	}


	public function resetOrderBy(): static
	{
		return new static($this->inner->resetOrderBy());
	}


	public function limitBy(int $limit, ?int $offset = null): static
	{
		return new static($this->inner->limitBy($limit, $offset));
	}


	public function fetch(): ?IEntity
	{
		return $this->inner->fetch();
	}


	public function fetchAll(): array
	{
		return $this->inner->fetchAll();
	}


	public function fetchPairs(?string $key = null, ?string $value = null): array
	{
		return $this->inner->fetchPairs($key, $value);
	}


	public function setRelationshipMapper(?IRelationshipMapper $mapper = null): static
	{
		$this->inner->setRelationshipMapper($mapper);
		return $this;
	}


	public function getRelationshipMapper(): ?IRelationshipMapper
	{
		return $this->inner->getRelationshipMapper();
	}


	public function setRelationshipParent(IEntity $parent): static
	{
		return new static($this->inner->setRelationshipParent($parent));
	}


	public function countStored(): int
	{
		return $this->inner->countStored();
	}


	public function subscribeOnEntityFetch(callable $callback): void
	{
		$this->inner->subscribeOnEntityFetch($callback);
	}


	/**
	 * @phpstan-return Iterator<int, T>
	 */
	public function getIterator(): Iterator
	{
		/** @phpstan-var Iterator<int, T> */
		return $this->inner->getIterator();
	}


	public function count(): int
	{
		return $this->inner->count();
	}
}
