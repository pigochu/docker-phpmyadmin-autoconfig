<?php

declare(strict_types=1);

/*
 * This file has been auto generated by Jane,
 *
 * Do no edit it directly.
 */

namespace Docker\API\Model;

class ContainerSummaryItemNetworkSettings
{
    /**
     * @var EndpointSettings[]
     */
    protected $networks;

    /**
     * @return EndpointSettings[]
     */
    public function getNetworks(): ?\ArrayObject
    {
        return $this->networks;
    }

    /**
     * @param EndpointSettings[] $networks
     *
     * @return self
     */
    public function setNetworks(?\ArrayObject $networks): self
    {
        $this->networks = $networks;

        return $this;
    }
}
