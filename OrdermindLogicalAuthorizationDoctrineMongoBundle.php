<?php

namespace Ordermind\LogicalAuthorizationDoctrineMongoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Ordermind\LogicalAuthorizationDoctrineMongoBundle\DependencyInjection\LogAuthDoctrineMongoExtension;

class OrdermindLogicalAuthorizationDoctrineMongoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->registerExtension(new LogAuthDoctrineMongoExtension());
    }
}
