here is some php code from literal::

    // config/routes.php
    namespace Symfony\Component\Routing\Loader\Configurator;

    return function (RoutingConfigurator $routes) {
        $routes->add('about_us', ['nl' => '/over-ons', 'en' => '/about-us'])
            ->controller('App\Controller\CompanyController::about');
    };

The CRUD controller of ``App\Entity\Example`` must implement
the ``EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\CrudControllerInterface``,
but you can also extend from the ``AbstractCrudController`` class.
