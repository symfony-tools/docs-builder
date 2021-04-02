Design
======

Something that should not be left to most programmers
to try to do.

Section 1
---------

The toctree below should affects the next/prev. The
first entry is effectively ignored, as it was already
included by the toctree in index.rst (which is parsed first).

Subsection 1
~~~~~~~~~~~~

This is a subsection of the first section. That's all.

Section 2
---------

However, crud (which is ALSO included in the toctree in index.rst),
WILL be read here, as the "crud" in index.rst has not been read
yet (design comes first). Also, design/sub-page WILL be considered.

.. toctree::
    :maxdepth: 1

    dashboards
    crud
    design/sub-page
