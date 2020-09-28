<?php

declare(strict_types=1);

namespace Longyan\Kafka\Protocol\Fetch;

use Longyan\Kafka\Protocol\AbstractStruct;
use Longyan\Kafka\Protocol\ProtocolField;

class FetchableTopic extends AbstractStruct
{
    /**
     * The name of the topic to fetch.
     *
     * @var string
     */
    protected $topicName;

    /**
     * The partitions to fetch.
     *
     * @var FetchPartition[]
     */
    protected $fetchPartitions = [];

    public function __construct()
    {
        if (!isset(self::$maps[self::class])) {
            self::$maps[self::class] = [
                new ProtocolField('topicName', 'string', false, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], [], [], [], null),
                new ProtocolField('fetchPartitions', FetchPartition::class, true, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], [], [], [], null),
            ];
            self::$taggedFieldses[self::class] = [
            ];
        }
    }

    public function getFlexibleVersions(): array
    {
        return [];
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function setTopicName(string $topicName): self
    {
        $this->topicName = $topicName;

        return $this;
    }

    /**
     * @return FetchPartition[]
     */
    public function getFetchPartitions(): array
    {
        return $this->fetchPartitions;
    }

    /**
     * @param FetchPartition[] $fetchPartitions
     */
    public function setFetchPartitions(array $fetchPartitions): self
    {
        $this->fetchPartitions = $fetchPartitions;

        return $this;
    }
}