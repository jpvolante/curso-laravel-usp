<!DOCTYPE html>
<html lang="pt_BR">

<head>
  <meta charset="UTF-8">
  <title>{{ $front['title'] ?? '' }} | {{ $site['title'] ?? '' }}</title>

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet" type="text/css">
  <base href="{{ $site['base'] }}">

  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.0.1/styles/default.min.css">
  <link rel="stylesheet" href="css/{{ $site['theme'] }}">
  <link rel="stylesheet" href="css/custom.css">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js"></script>
  <script src="js/custom.js"></script>

</head>

<body>
  <section class="page-header">
    <h1 class="project-name">
      <a href="{{ config('app.url') }}"><img src="images/uspdev.png" height="100px" /></a>
    </h1>
    <h2 class="project-tagline">{{ $site['description'] ?? '' }}</h2>

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

  <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.0.1/highlight.min.js"></script>
  <script>
    hljs.highlightAll();
  </script>
</body>

</html>
