<?php

declare(strict_types=1);

/*
 * This file has been auto generated by Jane,
 *
 * Do no edit it directly.
 */

namespace Docker\API\Model;

class HostConfigLogConfig
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string[]
     */
    protected $config;

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getConfig(): ?\ArrayObject
    {
        return $this->config;
    }

    /**
     * @param string[] $config
     *
     * @return self
     */
    public function setConfig(?\ArrayObject $config): self
    {
        $this->config = $config;

        return $this;
    }
}
