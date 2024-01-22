Toctree
=======

Simple

.. toctree::

    file


Simple, titles only

.. toctree::
    :titlesonly:

    file

Simple, file from other directory

.. toctree::

    directory/another_file

Glob

.. toctree::
    :glob:

    *

Glob, with explicit order

.. toctree::
    :glob:

    file1
    *

Hidden

.. toctree::
    :hidden:

    file
