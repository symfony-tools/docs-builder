Design
======

Something that should not be left to most programmers
to try to do.

Section 1
---------

The toctree below should affects the next/prev. The
first entry is effectively ignored, as it was already
included by the toctree in index.rst (which is parsed first).

Some subsection
~~~~~~~~~~~~~~~

This is a subsection of the first section. That's all.

Some subsection
~~~~~~~~~~~~~~~

This sub-section uses the same title as before to test that the tool
never generated two or more headings with the same ID.

Section 2
---------

However, crud (which is ALSO included in the toctree in index.rst),
WILL be read here, as the "crud" in index.rst has not been read
yet (design comes first). Also, design/sub-page WILL be considered.

Some subsection
~~~~~~~~~~~~~~~

This sub-section also uses the same title as in the previous section
to test that the tool never generated two or more headings with the same ID.

.. toctree::
    :maxdepth: 1

    dashboards
    crud
    design/sub-page
