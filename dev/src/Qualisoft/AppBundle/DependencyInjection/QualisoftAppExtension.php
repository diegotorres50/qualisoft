<?php

namespace Qualisoft\AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

//@diegotorres50 says: usamos Symfony\Component\DependencyInjection\Definition para Añadir funciones a Twig en Symfony 2
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class QualisoftAppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        //

        /*
        @diegotorres50 says: Inicia: Añadir funciones a Twig en Symfony 2 (fuente: http://ahoj.io/how-to-make-twig-extension-for-symfony2)

        Muchas veces en la plantilla necesitamos funciones específicas que no vienen incluidas en el framework. En Symfony 1 teníamos los helpers, pero en Symfony 2 la vía que nos queda es la de extender la funcionalidad básica de Twig.

        En primer lugar debemos conectar nuestra futura extensión Twig con el Contenedor de Inyección de Dependencias de nuestro Bundle. Si no tenemos configuración para el contenedor deberemos crear el fichero.
        */

        $definition = new Definition('Qualisoft\AppBundle\Extension\MyTwigExtension'); //Esta es la ruta donde se define la clase principal de la nueva extension para twig
        // this is the most important part. Later in the startup process TwigBundle
        // searches through the container and registres all services taged as twig.extension.
        $definition->addTag('twig.extension');
        $container->setDefinition('my_twig_extension', $definition); //Este 'my_twig_extension' es el alias de la clase principal de la extension       
        /* @diegotorres50 says: Termina: Añadir funciones a Twig en Symfony 2 */

    }
}
