.. code-block:: twig

    {# some code #}
    {%
        set some_var = 'some value' # some inline comment
    %}
    {{
        # another inline comment
        'Lorem Ipsum'|uppercase
        # final inline comment
    }}

    {# Both data-action values will be concatenated #}
    {%- set dialog_attrs = {
        'data-action': 'click->dialog#open'|html_attr_type('sst'),
    } -%}
