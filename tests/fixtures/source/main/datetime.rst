.. index::
   single: Forms; Fields; DateTimeType

DateTimeType Field
==================

This field type allows the user to modify data that represents a specific
date and time (e.g. ``1984-06-05 12:15:30``).

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | single text box or three select fields                                      |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - `The date_format Option`_                                                 |
|                      | - `date_widget`_                                                            |
|                      | - `placeholder`_                                                            |
|                      | - `format`_                                                                 |
+----------------------+-----------------------------------------------------------------------------+
| Overridden options   | - `by_reference`_                                                           |
|                      | - `error_bubbling`_                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | :doc:`FormType </form/form_type>`                                           |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateTimeType`      |
+----------------------+-----------------------------------------------------------------------------+
| Ref                  | :ref:`reference-forms-type-date-format`                                     |
|                      | :ref:`Test reference <internal-reference>`                                  |
+----------------------+-----------------------------------------------------------------------------+

Field Options
-------------

The date_format Option
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``integer`` or ``string`` **default**: ``IntlDateFormatter::MEDIUM``

Defines the ``format`` option that will be passed down to the date field.
for more details.

.. tip::

    This is a little tip about something! We an also talk about specific
    methods: :method:`Symfony\\Component\\BrowserKit\\Client::doRequest`.
    Or a namespace: :namespace:`Symfony\\Component\\Validator\\Constraints`.
    Or a PHP function: :phpfunction:`parse_ini_file`.
    Or a PHP method! :phpmethod:`Locale::getDefault`.

date_widget
~~~~~~~~~~~

Date widget!

.. note::

    Sometimes we add notes. But not too often because they interrupt
    the flow.
    :ref:`internal-reference`

placeholder
~~~~~~~~~~~

.. versionadded:: 2.6
    The ``placeholder`` option was introduced in Symfony 2.6 and replaces
    ``empty_value``, which is available prior to 2.6.
    :ref:`internal-reference`

**type**: ``string`` | ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. When the placeholder value is a string,
it will be used as the **blank value** of all select boxes::

    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

    $builder->add('startDateTime', DateTimeType::class, array(
        'placeholder' => 'Select a value',
    ));

.. seealso::

    Also check out the homepage - :doc:`/index`.
    :ref:`internal-reference`

Custom classes for links are also cool:

.. class:: list-config-options

* ``excluded_ajax_paths``
* ``intercept_redirects``
* ``position``
* ``toolbar``
* ``verbose``

format
~~~~~~

**type**: ``string`` **default**: ``Symfony\Component\Form\Extension\Core\Type\DateTimeType::HTML5_FORMAT``

If the ``widget`` option is set to ``single_text``, this option specifies
the format of the input, i.e. how Symfony will interpret the given input
as a datetime string. See `Date/Time Format Syntax`_.

.. sidebar:: Everyone loves sidebars

    But do they really? They also get in the way!

.. caution::

    Using too many sidebars or caution directives can be distracting!

time_widget
~~~~~~~~~~~

**type**: ``string`` **default**: ``choice``

Defines the ``widget`` option for the ``TimeType``.

widget
~~~~~~

**type**: ``string`` **default**: ``null``

Defines the ``widget`` option for both the ``DateType``.
and ``TimeType``. This can be overridden
with the `date_widget`_ and `time_widget`_ options.

Overridden Options
------------------

by_reference
~~~~~~~~~~~~

**default**: ``false``

The ``DateTime`` classes are treated as immutable objects.

error_bubbling
~~~~~~~~~~~~~~

**default**: ``false``

We also support code blocks!

.. code-block:: yaml

    # app/config/parameters.yml
    parameters:
        database_driver:   pdo_mysql

And configuration blocks:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            secret:          '%secret%'
            router:          { resource: '%kernel.root_dir%/config/routing.yml' }
            # ...

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd
                http://symfony.com/schema/dic/twig
                http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <framework:config secret="%secret%">
                <framework:router resource="%kernel.root_dir%/config/routing.xml" />
                <!-- ... -->
            </framework:config>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'secret' => '%secret%',
            'router' => array(
                'resource' => '%kernel.root_dir%/config/routing.php',
            ),
            // ...
        ));

        // ...

Field Variables
---------------

+----------+------------+---------------------------------------+
| Variable | Type       | Usage                                 |
+==========+============+=======================================+
| widget   | ``mixed``  | The value of the ``widget`` option    |
+----------+------------+---------------------------------------+
| type     | ``string`` | Multiple lines of text here, to show  |
|          |            | that off                              |
+----------+------------+---------------------------------------+

.. _`RFC 3339`: https://tools.ietf.org/html/rfc3339
.. _`Date/Time Format Syntax`: http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
