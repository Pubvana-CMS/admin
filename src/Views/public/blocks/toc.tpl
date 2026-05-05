<div class="pv-block">
    <div class="pv-block-toc">
        {% if title %}
        <h6 class="pv-block-title">{{ title }}</h6>
        {% endif %}
        {% if headings %}
        <ul class="pv-block-ul">
            {% for heading in headings %}
            <li class="pv-block-li pv-block-toc-h{{ heading.level }}">
                <a class="pv-block-a" href="#{{ heading.id }}">{{ heading.text }}</a>
            </li>
            {% endfor %}
        </ul>
        {% endif %}
    </div>
</div>
