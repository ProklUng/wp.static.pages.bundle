# Бандл рендеринга статических страниц из роутов для связки Wordpress-Symfony

В качестве базы используется форкнутый класс Symfony\Bundle\FrameworkBundle\Controller\TemplateController

### Пример конфига бандла

```yaml
static_page_maker:
  post_type: static_pages
  set_last_modified_header: true
```

### Описание роута (в файле типа routes.yaml)

```yaml
simple_static_page:
  path: /simple_page/
  methods: [GET]
  controller: 'Prokl\StaticPageMakerBundle\Services\TemplateControllerContainerAware::templateAction'
  defaults:
    is_granted: ['administrator']
    template:  './staticPages/example.twig'
    context:
      url: '/simple_page/'
      site_name: 'ACME'
      service: '@app.options' # Сервис как параметр
      # Препроцессоры контекста.
      _processors:
        - '@Prokl\StaticPageMakerBundle\Services\ContextProcessors\ExampleContextProcessor'
      object:
        errored: true
        finished: true
        pending: true
      theme: 'dark'
      title: 'Простая статическая страница'
      h1: 'Простая статическая страница'
      postClasses: 'main'
      assets:
        # Глобальный CSS
        css: 'global.css'
        # Сss для страницы. Entry в терминах webpack.
        css_page: 'basicStaticPage.css'
        js: 'main.js'

```

### Нюансы

1) Перед передачей данных в Твиг существует возможность пропустить данные через цепочку
"препроцессоров", чтобы как-нибудь их автоматически изменить.
    
   Препроцессоры, уже имеющиеся в пакете:
   
    1) SeoContextProcessor - установка title & description. 
        
       Даже если эксплицитно указаны параметры title - description в роуте, то подключенный процессор
       "перебьет" их значения (если найдет запись в инфоблоке).
    
    Препроцессор - класс, реализующий интерфейс ContextProcessorInterface с двумя методами setContext
    (сеттер; чтобы не копипастить его каждый раз существует абстрактный класс AbstractContextProcessor) и
    handle (где осуществляется вся работа). Метод должен вернуть измененный (или неизмененный) контекст.
    
    Чтобы препроцессор заработал он должен быть сервисом, помеченным тэгом static_page_maker.default.context.processor.
    
    Препроцессор пускается автоматически, если в конфиге бандла задан ID SEO инфоблока.

2) При установке опции set_last_modified_header равной true в конфигурации бандла (local/configs/packages/static_page_maker.yaml)
   будут установлены заголовки LastModified.  
         
3) Пример шаблона:   

    Базовый (для копипасты лежит в vendor\ProklUng\StaticPageMakerBundle\Examples\templates\static\template\baseTemplate.twig):
    
```twig
    {# head #}
    {% block header %}
           <!DOCTYPE html>
       <html lang="ru">
       <head>
           <meta charset="UTF-8">
           <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
           <title> {{ title }}</title>
   
      {% if assets.css|length %}
           {{ encore_entry_link_tags(assets.css) }}
       {% endif %}
   
       {% if assets.css_page|length %}
           {{ encore_entry_link_tags(assets.css_page) }}
       {% endif %}
       </head>
   
       <body class="{{ postClasses }}">
    {% endblock %}
    
    {# content #}
    {% block content %}
    
    {% endblock %}
    
    {# footer #}
    {% block footer %}
            <footer>
        
                <script src="/node_modules/jquery/dist/jquery.min.js"></script>
                
                {% if assets.js|length %}
                    {{ encore_entry_script_tags(assets.js) }}
                {% endif %}
            </footer>
        
            </div>
          </body>
        
          </html>
    {% endblock %} 
```      
   
   Конкретная страница:
   
```twig
{% extends "./staticPages/basicStaticSimplePage.twig" %}

{% block content %}
    <br/>
    <div>
        {{ app.environment }}
    </div>
    <div class="container">
        {% apply inline_css %}
            <h1> {{ h1 }} </h1>

            <div class="test">
                Мои параметры: {{ site_name }}         zzz{{ test_globals }}
            </div>
            <style>
                .test { color: red; }
                .errored {color: rosybrown}
            </style>
        {% endapply %}
        {{ render(controller("Prokl\\TwigExtensionsPackBundle\\Twig\\Extensions\\Examples\\ExampleSimpleController", { 'id': 3 })) }}

        {{ render('/api/calendar/', {'headers' : { 'X-Requested-With': 'XMLHttpRequest'} }) }}
        {{ render_service("Prokl\\TwigExtensionsPackBundle\\Twig\\Extensions\\Examples\\ExampleServiceForRender", "action", { 'id': 3} , {'filesystem' : 'filesystem.instance' }) }}


        {{ param('instagram.account') }}
        <br><br/>
        <p class="{{ html_classes('a-class', 'another-class', {
            'errored': object.errored,
            'finished': object.finished,
            'pending': object.pending,
        }) }}">How are you doing?</p>

        {{ service.ajaxNonce }}

        <div>
            Работа процессора контента: {{ processor_change }}
        </div>
    </div>

        {% endblock %}
      ```  
   5) Сами шаблоны ищутся согласно зарегистрированным в Твиге путям. В моем случае - в local/twig.

   6) В Твиг подтягиваются все зарегистрированные в системе функции и фильтры.
