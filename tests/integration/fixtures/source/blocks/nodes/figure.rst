.. figure:: images/logo.png
   :alt: Symfony Logo
   :width: 200px

I am a paragraph AFTER the figure. I should not be included as the
caption for the above figure.

.. figure:: images/logo.png

    But I am a caption *for* the figure above.

Some images use a special CSS class to wrap a fake browser around them:

.. image:: images/exceptions-in-dev-environment.png
   :alt: A typical exception page in the development environment
   :align: center
   :class: some-class with-browser another-class

And RST figures use a different syntax to define their custom CSS classes:

.. figure:: images/logo.png
    :alt: /
    :align: center
    :figclass: with-browser foo
