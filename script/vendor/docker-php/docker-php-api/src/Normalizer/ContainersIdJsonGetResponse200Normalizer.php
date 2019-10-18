<?php

declare(strict_types=1);

/*
 * This file has been auto generated by Jane,
 *
 * Do no edit it directly.
 */

namespace Docker\API\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContainersIdJsonGetResponse200Normalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'Docker\\API\\Model\\ContainersIdJsonGetResponse200';
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \Docker\API\Model\ContainersIdJsonGetResponse200;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!is_object($data)) {
            return null;
        }
        $object = new \Docker\API\Model\ContainersIdJsonGetResponse200();
        if (property_exists($data, 'Id') && $data->{'Id'} !== null) {
            $object->setId($data->{'Id'});
        }
        if (property_exists($data, 'Created') && $data->{'Created'} !== null) {
            $object->setCreated($data->{'Created'});
        }
        if (property_exists($data, 'Path') && $data->{'Path'} !== null) {
            $object->setPath($data->{'Path'});
        }
        if (property_exists($data, 'Args') && $data->{'Args'} !== null) {
            $values = [];
            foreach ($data->{'Args'} as $value) {
                $values[] = $value;
            }
            $object->setArgs($values);
        }
        if (property_exists($data, 'State') && $data->{'State'} !== null) {
            $object->setState($this->denormalizer->denormalize($data->{'State'}, 'Docker\\API\\Model\\ContainersIdJsonGetResponse200State', 'json', $context));
        }
        if (property_exists($data, 'Image') && $data->{'Image'} !== null) {
            $object->setImage($data->{'Image'});
        }
        if (property_exists($data, 'ResolvConfPath') && $data->{'ResolvConfPath'} !== null) {
            $object->setResolvConfPath($data->{'ResolvConfPath'});
        }
        if (property_exists($data, 'HostnamePath') && $data->{'HostnamePath'} !== null) {
            $object->setHostnamePath($data->{'HostnamePath'});
        }
        if (property_exists($data, 'HostsPath') && $data->{'HostsPath'} !== null) {
            $object->setHostsPath($data->{'HostsPath'});
        }
        if (property_exists($data, 'LogPath') && $data->{'LogPath'} !== null) {
            $object->setLogPath($data->{'LogPath'});
        }
        if (property_exists($data, 'Node') && $data->{'Node'} !== null) {
            $object->setNode($data->{'Node'});
        }
        if (property_exists($data, 'Name') && $data->{'Name'} !== null) {
            $object->setName($data->{'Name'});
        }
        if (property_exists($data, 'RestartCount') && $data->{'RestartCount'} !== null) {
            $object->setRestartCount($data->{'RestartCount'});
        }
        if (property_exists($data, 'Driver') && $data->{'Driver'} !== null) {
            $object->setDriver($data->{'Driver'});
        }
        if (property_exists($data, 'MountLabel') && $data->{'MountLabel'} !== null) {
            $object->setMountLabel($data->{'MountLabel'});
        }
        if (property_exists($data, 'ProcessLabel') && $data->{'ProcessLabel'} !== null) {
            $object->setProcessLabel($data->{'ProcessLabel'});
        }
        if (property_exists($data, 'AppArmorProfile') && $data->{'AppArmorProfile'} !== null) {
            $object->setAppArmorProfile($data->{'AppArmorProfile'});
        }
        if (property_exists($data, 'ExecIDs') && $data->{'ExecIDs'} !== null) {
            $object->setExecIDs($data->{'ExecIDs'});
        }
        if (property_exists($data, 'HostConfig') && $data->{'HostConfig'} !== null) {
            $object->setHostConfig($this->denormalizer->denormalize($data->{'HostConfig'}, 'Docker\\API\\Model\\HostConfig', 'json', $context));
        }
        if (property_exists($data, 'GraphDriver') && $data->{'GraphDriver'} !== null) {
            $object->setGraphDriver($this->denormalizer->denormalize($data->{'GraphDriver'}, 'Docker\\API\\Model\\GraphDriverData', 'json', $context));
        }
        if (property_exists($data, 'SizeRw') && $data->{'SizeRw'} !== null) {
            $object->setSizeRw($data->{'SizeRw'});
        }
        if (property_exists($data, 'SizeRootFs') && $data->{'SizeRootFs'} !== null) {
            $object->setSizeRootFs($data->{'SizeRootFs'});
        }
        if (property_exists($data, 'Mounts') && $data->{'Mounts'} !== null) {
            $values_1 = [];
            foreach ($data->{'Mounts'} as $value_1) {
                $values_1[] = $this->denormalizer->denormalize($value_1, 'Docker\\API\\Model\\MountPoint', 'json', $context);
            }
            $object->setMounts($values_1);
        }
        if (property_exists($data, 'Config') && $data->{'Config'} !== null) {
            $object->setConfig($this->denormalizer->denormalize($data->{'Config'}, 'Docker\\API\\Model\\ContainerConfig', 'json', $context));
        }
        if (property_exists($data, 'NetworkSettings') && $data->{'NetworkSettings'} !== null) {
            $object->setNetworkSettings($this->denormalizer->denormalize($data->{'NetworkSettings'}, 'Docker\\API\\Model\\NetworkSettings', 'json', $context));
        }

        return $object;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = new \stdClass();
        if (null !== $object->getId()) {
            $data->{'Id'} = $object->getId();
        }
        if (null !== $object->getCreated()) {
            $data->{'Created'} = $object->getCreated();
        }
        if (null !== $object->getPath()) {
            $data->{'Path'} = $object->getPath();
        }
        if (null !== $object->getArgs()) {
            $values = [];
            foreach ($object->getArgs() as $value) {
                $values[] = $value;
            }
            $data->{'Args'} = $values;
        }
        if (null !== $object->getState()) {
            $data->{'State'} = $this->normalizer->normalize($object->getState(), 'json', $context);
        }
        if (null !== $object->getImage()) {
            $data->{'Image'} = $object->getImage();
        }
        if (null !== $object->getResolvConfPath()) {
            $data->{'ResolvConfPath'} = $object->getResolvConfPath();
        }
        if (null !== $object->getHostnamePath()) {
            $data->{'HostnamePath'} = $object->getHostnamePath();
        }
        if (null !== $object->getHostsPath()) {
            $data->{'HostsPath'} = $object->getHostsPath();
        }
        if (null !== $object->getLogPath()) {
            $data->{'LogPath'} = $object->getLogPath();
        }
        if (null !== $object->getNode()) {
            $data->{'Node'} = $object->getNode();
        }
        if (null !== $object->getName()) {
            $data->{'Name'} = $object->getName();
        }
        if (null !== $object->getRestartCount()) {
            $data->{'RestartCount'} = $object->getRestartCount();
        }
        if (null !== $object->getDriver()) {
            $data->{'Driver'} = $object->getDriver();
        }
        if (null !== $object->getMountLabel()) {
            $data->{'MountLabel'} = $object->getMountLabel();
        }
        if (null !== $object->getProcessLabel()) {
            $data->{'ProcessLabel'} = $object->getProcessLabel();
        }
        if (null !== $object->getAppArmorProfile()) {
            $data->{'AppArmorProfile'} = $object->getAppArmorProfile();
        }
        if (null !== $object->getExecIDs()) {
            $data->{'ExecIDs'} = $object->getExecIDs();
        }
        if (null !== $object->getHostConfig()) {
            $data->{'HostConfig'} = $this->normalizer->normalize($object->getHostConfig(), 'json', $context);
        }
        if (null !== $object->getGraphDriver()) {
            $data->{'GraphDriver'} = $this->normalizer->normalize($object->getGraphDriver(), 'json', $context);
        }
        if (null !== $object->getSizeRw()) {
            $data->{'SizeRw'} = $object->getSizeRw();
        }
        if (null !== $object->getSizeRootFs()) {
            $data->{'SizeRootFs'} = $object->getSizeRootFs();
        }
        if (null !== $object->getMounts()) {
            $values_1 = [];
            foreach ($object->getMounts() as $value_1) {
                $values_1[] = $this->normalizer->normalize($value_1, 'json', $context);
            }
            $data->{'Mounts'} = $values_1;
        }
        if (null !== $object->getConfig()) {
            $data->{'Config'} = $this->normalizer->normalize($object->getConfig(), 'json', $context);
        }
        if (null !== $object->getNetworkSettings()) {
            $data->{'NetworkSettings'} = $this->normalizer->normalize($object->getNetworkSettings(), 'json', $context);
        }

        return $data;
    }
}
