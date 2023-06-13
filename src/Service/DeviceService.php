<?php

namespace App\Service;

use App\Entity\Device;
use App\Entity\Login;
use App\Repository\DeviceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceService
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, TranslatorInterface $translator)
    {
        $this->doctrine = $doctrine;
    }

    function saveDevice(Login $login, string $uuid, string $desc, string $deviceToken): void
    {
        $device = new Device();

        $device->setLogin($login);
        $device->setToken($deviceToken);
        $device->setDescription($desc);
        $device->setUuid($uuid);
        $device->setNotify(true);

        /** @var DeviceRepository $repository */
        $repository = $this->doctrine->getRepository(Device::class);

        $repository->save($device, true);
    }
}