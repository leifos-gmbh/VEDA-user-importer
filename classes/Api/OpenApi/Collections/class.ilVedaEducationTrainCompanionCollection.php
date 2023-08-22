<?php

use OpenAPI\Client\Model\AusbildungszugLernbegleiter;

class ilVedaEducationTrainCompanionCollection implements ilVedaEducationTrainCompanionCollectionInterface
{
    /**
     * @var AusbildungszugLernbegleiter[]
     */
    protected array $education_train_companions;
    protected int $index;

    /**
     * @param AusbildungszugLernbegleiter $education_train_companions
     */
    public function __construct(array $education_train_companions)
    {
        $this->education_train_companions = $education_train_companions;
        $this->index = 0;
    }

    public function logContent(ilLogger $logger): void
    {
        $logger->dump($this->education_train_companions, ilLogLevel::DEBUG);
    }

    public function current(): AusbildungszugLernbegleiter
    {
        return $this->education_train_companions[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return 0 <= $this->index && $this->index < count($this->education_train_companions);
    }

    public function count(): int
    {
        return count($this->education_train_companions);
    }
}