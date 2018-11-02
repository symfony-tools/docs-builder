Changes needed in symfony.com 
=============================

- For `caution`, `note` and `tip` : remove `class="last"` in favor to `:last-child`
- idem about `.first`
- idem for `sidebar` (the `.first` css class is not even used in css)
- `div.section` are now removed, so the `margin-top` needd to go on `<hX>`
- `<p>` added to `.. seealso::` directive 
- `versionadded` dom changed 

Notes
=====

- add some format check on references
- toc tree bugs whith :glob: (issue opened)
- should we print breadcrumb ?
- error in main : toctree
- `<ul>` in table = `<ul class="first last simple">`  why ?
- /!\ tip / caution / node / etc... with nested reference
- no more `colgroup` in tables ??
- add `&nbsp` instead of simple space to lines in code blocks
- test if there is only one <h1> in each page
- we're assuming there is only one toctree per page...
- `rst` does not exist in highlight php
- `varnish` = C ? (highlight php)
