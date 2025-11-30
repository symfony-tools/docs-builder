
.. code-block:: php
    :caption: config/routes.php

    $foo = 'bar';


.. code-block:: php
    :caption: config/routes.php
    :class: hide

    $foo = 'bar';

.. code-block:: diff
    :caption: patch_file
    :emphasize-lines: 1,2

    --- a/src/Controller/DefaultController.php
    +++ b/src/Controller/DefaultController.php
    @@ -2,7 +2,9 @@
