.. code-block:: php

    // config/routes.php
    namespace Symfony\Component\Routing\Loader\Configurator;

    use App\Controller\CompanyController;

    return static function (RoutingConfigurator $routes): void {
        $routes->add('about_us', ['nl' => '/over-ons', 'en' => '/about-us'])
            ->controller(CompanyController::class.'::about');
    };

.. code-block:: php

    enum TextAlign: string implements TranslatableInterface
    {
        case Left = 'Left aligned';
        case Center = 'Center aligned';
        case Right = 'Right aligned';

        public function trans(TranslatorInterface $translator, ?string $locale = null): string
        {
            // Translate enum using custom labels
            return match ($this) {
                self::Left => $translator->trans('text_align.left.label', locale: $locale),
                self::Center => $translator->trans('text_align.center.label', locale: $locale),
                self::Right => $translator->trans('text_align.right.label', locale: $locale),
            };
        }
    }

.. code-block:: php

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        // get the data from the token
        $payload = ...;

        return new UserBadge(
            $payload->getUserId(),
            fn (string $userIdentifier): User => new User($userIdentifier, $payload->getRoles())
        );

        // or
        return new UserBadge(
            $payload->getUserId(),
            $this->loadUser(...)
        );
    }
