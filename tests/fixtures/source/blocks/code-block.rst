
.. code-block:: php
    // config/routes.php
    namespace Symfony\Component\Routing\Loader\Configurator;

    return function (RoutingConfigurator $routes) {
        $routes->add('about_us', ['nl' => '/over-ons', 'en' => '/about-us'])
            ->controller('App\Controller\CompanyController::about');
    };
