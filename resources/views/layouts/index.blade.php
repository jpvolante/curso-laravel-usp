<!DOCTYPE html>
<html lang="pt_BR">

<head>
  <meta charset="UTF-8">
  <title>{{ $site['title'] }}</title>

  {{-- {% seo %} --}}
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#157878">
  <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>

  <style>
    @import "css/{{ $site['theme'] }}";

    .page-header {
      padding: 0;
    }

  </style>

</head>

<body>
  <section class="page-header">
    <h1 class="project-name">
      <a href="{{ config('app.url') }}"><img src="images/uspdev.png" height="100px" /></a>
    </h1>
    <h2 class="project-tagline">{{ $front['description'] ?? '' }}</h2>

    @isset($site['menu'])
      @foreach ($site['menu'] as $item)
        <a href="{{ $item['url'] }}" class="btn">{{ $item['text'] }}</a>
      @endforeach
    @endisset

  </section>

  <section class="main-content">

    @yield('content')

    {{-- <h1> Últimos Posts: </h1>
              <ul>
                  {% for post in site.posts %}
                      <li>
                          <a href="{{ post.url }}">{{ post.title }}</a>
                      </li>
                  {% endfor %}
              </ul>

      <footer class="site-footer">
        {% if site.github.is_project_page %}
          <span class="site-footer-owner"><a href="{{ site.github.repository_url }}">{{ site.github.repository_name }}</a> is maintained by <a href="{{ site.github.owner_url }}">{{ site.github.owner_name }}</a>.</span>
        {% endif %}
        
      </footer> --}}
  </section>

  {{-- {% if site.google_analytics %}
      <script type="text/javascript">
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', '{{ site.google_analytics }}', 'auto');
        ga('send', 'pageview');
      </script>
    {% endif %} --}}
</body>

</html>
