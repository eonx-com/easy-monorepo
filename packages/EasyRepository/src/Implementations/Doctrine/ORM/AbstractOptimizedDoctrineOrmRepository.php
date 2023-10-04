<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Implementations\Doctrine\ORM;

use Closure;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Paginators\DoctrineOrmLengthAwarePaginator;
use EonX\EasyRepository\Exceptions\EasyPaginationNotInstalledException;
use EonX\EasyRepository\Interfaces\DatabaseRepositoryInterface;
use EonX\EasyRepository\Interfaces\PaginatedObjectRepositoryInterface as PaginatedObjRepoInterface;
use Throwable;

abstract class AbstractOptimizedDoctrineOrmRepository implements DatabaseRepositoryInterface, PaginatedObjRepoInterface
{
    private ?EntityManagerInterface $manager = null;

    private ?PaginationInterface $pagination = null;

    private ?EntityRepository $repository = null;

    public function __construct(
        protected ManagerRegistry $registry,
    ) {
    }

    /**
     * @return object[]
     */
    public function all(): array
    {
        return $this->getRepository()
            ->findAll();
    }

    public function beginTransaction(): void
    {
        $this->getManager()
            ->beginTransaction();
    }

    public function commit(): void
    {
        $this->getManager()
            ->commit();
    }

    /**
     * @param object|object[] $object
     */
    public function delete(object|array $object): void
    {
        $this->callManagerMethodForObjects('remove', $object);
    }

    public function find(int|string $identifier): ?object
    {
        return $this->getRepository()
            ->find($identifier);
    }

    public function flush(): void
    {
        $this->getManager()
            ->flush();
    }

    public function paginate(?PaginationInterface $pagination = null): LengthAwarePaginatorInterface
    {
        return $this->createLengthAwarePaginator(null, null, $pagination);
    }

    public function rollback(): void
    {
        $this->getManager()
            ->rollback();
    }

    /**
     * @param object|object[] $object
     */
    public function save(object|array $object): void
    {
        $this->callManagerMethodForObjects('persist', $object);
    }

    public function setPagination(PaginationInterface $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * @throws \Throwable
     */
    public function transactional(Closure $func): mixed
    {
        $this->beginTransaction();

        try {
            $return = \call_user_func($func);

            $this->commit();

            return $return ?? true;
        } catch (Throwable $exception) {
            if ($exception instanceof ORMException || $exception instanceof Exception) {
                $this->getManager()
                    ->close();
            }

            $this->rollback();

            throw $exception;
        }
    }

    /**
     * @phpstan-return class-string
     */
    abstract protected function getEntityClass(): string;

    protected function createLengthAwarePaginator(
        ?string $from = null,
        ?string $fromAlias = null,
        ?PaginationInterface $pagination = null,
    ): DoctrineOrmLengthAwarePaginator {
        return new DoctrineOrmLengthAwarePaginator(
            $pagination ?? $this->getPagination(),
            $this->getManager(),
            $from ?? $this->getEntityClass(),
            $fromAlias ?? $this->getEntityAlias()
        );
    }

    protected function createQueryBuilder(?string $alias = null, ?string $indexBy = null): QueryBuilder
    {
        return $this->getRepository()
            ->createQueryBuilder($alias ?? $this->getEntityAlias(), $indexBy);
    }

    protected function getClassMetadata(): ClassMetadata
    {
        return $this->getManager()
            ->getClassMetadata($this->getRepository()->getClassName());
    }

    protected function getEntityAlias(): string
    {
        $exploded = \explode('\\', $this->getRepository()->getClassName());

        return \strtolower(\substr($exploded[\count($exploded) - 1], 0, 1));
    }

    protected function getManager(): EntityManagerInterface
    {
        return $this->manager ??= $this->registry->getManagerForClass($this->getEntityClass());
    }

    protected function getPagination(): PaginationInterface
    {
        if ($this->pagination !== null) {
            return $this->pagination;
        }

        throw new EasyPaginationNotInstalledException(
            'To use pagination within your repository, you must install the eonx-com/easy-pagination package'
        );
    }

    protected function getRepository(): EntityRepository
    {
        return $this->repository ??= $this->getManager()
            ->getRepository($this->getEntityClass());
    }

    /**
     * @param object|object[] $objects
     */
    private function callManagerMethodForObjects(string $method, array|object $objects): void
    {
        if (\is_array($objects) === false) {
            $objects = [$objects];
        }

        foreach ($objects as $object) {
            $this->getManager()
                ->{$method}($object);
        }
    }
}
