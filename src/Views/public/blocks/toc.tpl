<div class="block">
    <div class="block-toc">
        {% if title %}
        <h6 class="block-title">{{ title }}</h6>
        {% endif %}
        {% if headings %}
        <ul class="block-ul">
            {% for heading in headings %}
            <li class="block-li block-toc-h{{ heading.level }}">
                <a class="block-a" href="#{{ heading.id }}">{{ heading.text }}</a>
            </li>
            {% endfor %}
        </ul>
        {% endif %}
    </div>
</div>
