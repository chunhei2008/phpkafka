<?php

declare(strict_types=1);

namespace Longyan\Kafka\Protocol\DeleteAcls;

use Longyan\Kafka\Protocol\AbstractStruct;
use Longyan\Kafka\Protocol\ProtocolField;

class DeleteAclsFilter extends AbstractStruct
{
    /**
     * The resource type.
     *
     * @var int
     */
    protected $resourceTypeFilter;

    /**
     * The resource name.
     *
     * @var string|null
     */
    protected $resourceNameFilter;

    /**
     * The pattern type.
     *
     * @var int
     */
    protected $patternTypeFilter = 3;

    /**
     * The principal filter, or null to accept all principals.
     *
     * @var string|null
     */
    protected $principalFilter;

    /**
     * The host filter, or null to accept all hosts.
     *
     * @var string|null
     */
    protected $hostFilter;

    /**
     * The ACL operation.
     *
     * @var int
     */
    protected $operation;

    /**
     * The permission type.
     *
     * @var int
     */
    protected $permissionType;

    public function __construct()
    {
        if (!isset(self::$maps[self::class])) {
            self::$maps[self::class] = [
                new ProtocolField('resourceTypeFilter', 'int8', false, [0, 1, 2], [2], [], [], null),
                new ProtocolField('resourceNameFilter', 'string', false, [0, 1, 2], [2], [0, 1, 2], [], null),
                new ProtocolField('patternTypeFilter', 'int8', false, [1, 2], [2], [], [], null),
                new ProtocolField('principalFilter', 'string', false, [0, 1, 2], [2], [0, 1, 2], [], null),
                new ProtocolField('hostFilter', 'string', false, [0, 1, 2], [2], [0, 1, 2], [], null),
                new ProtocolField('operation', 'int8', false, [0, 1, 2], [2], [], [], null),
                new ProtocolField('permissionType', 'int8', false, [0, 1, 2], [2], [], [], null),
            ];
            self::$taggedFieldses[self::class] = [
            ];
        }
    }

    public function getFlexibleVersions(): array
    {
        return [2];
    }

    public function getResourceTypeFilter(): int
    {
        return $this->resourceTypeFilter;
    }

    public function setResourceTypeFilter(int $resourceTypeFilter): self
    {
        $this->resourceTypeFilter = $resourceTypeFilter;

        return $this;
    }

    public function getResourceNameFilter(): ?string
    {
        return $this->resourceNameFilter;
    }

    public function setResourceNameFilter(?string $resourceNameFilter): self
    {
        $this->resourceNameFilter = $resourceNameFilter;

        return $this;
    }

    public function getPatternTypeFilter(): int
    {
        return $this->patternTypeFilter;
    }

    public function setPatternTypeFilter(int $patternTypeFilter): self
    {
        $this->patternTypeFilter = $patternTypeFilter;

        return $this;
    }

    public function getPrincipalFilter(): ?string
    {
        return $this->principalFilter;
    }

    public function setPrincipalFilter(?string $principalFilter): self
    {
        $this->principalFilter = $principalFilter;

        return $this;
    }

    public function getHostFilter(): ?string
    {
        return $this->hostFilter;
    }

    public function setHostFilter(?string $hostFilter): self
    {
        $this->hostFilter = $hostFilter;

        return $this;
    }

    public function getOperation(): int
    {
        return $this->operation;
    }

    public function setOperation(int $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getPermissionType(): int
    {
        return $this->permissionType;
    }

    public function setPermissionType(int $permissionType): self
    {
        $this->permissionType = $permissionType;

        return $this;
    }
}