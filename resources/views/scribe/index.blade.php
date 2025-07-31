<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>NyuwiCreation API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://nyuwi-creation-project.test";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.3.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.3.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-GETapi-home">
                                <a href="#endpoints-GETapi-home">GET api/home</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: July 29, 2025</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://nyuwi-creation-project.test</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-GETapi-home">GET api/home</h2>

<p>
</p>



<span id="example-requests-GETapi-home">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://nyuwi-creation-project.test/api/home" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://nyuwi-creation-project.test/api/home"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-home">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Garden&quot;,
            &quot;slug&quot;: &quot;garden&quot;,
            &quot;category_id&quot;: 1,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;5000.00&quot;,
            &quot;description&quot;: &quot;Cincin manik yang dirangkai untuk menghiasi jari jarimu! Cocok untuk beraktifitas harian di dalam ruangan pilihan warna yang tersedia beragam bisa didesuaikan dengan gaya mu.&quot;,
            &quot;images&quot;: [
                &quot;1735005876.jpg&quot;
            ],
            &quot;sizes&quot;: [
                &quot;S&quot;,
                &quot;M&quot;,
                &quot;L&quot;
            ],
            &quot;colors&quot;: [
                &quot;Red&quot;,
                &quot;Blue&quot;,
                &quot;Green&quot;,
                &quot;Pink&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:04:37.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:04:37.000000Z&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Mutiara&quot;,
            &quot;slug&quot;: &quot;mutiara&quot;,
            &quot;category_id&quot;: 1,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;3000.00&quot;,
            &quot;description&quot;: &quot;Gelang mutiara imitasi yang ringan dan cocok untuk segala warna kulit eksotis, dengan pengait yang bisa disesuaikan jangan takut apabila kamu suka gelang yang longgar di tangan.&quot;,
            &quot;images&quot;: [
                &quot;1735006004.jpg&quot;
            ],
            &quot;sizes&quot;: [
                &quot;One Size&quot;
            ],
            &quot;colors&quot;: [
                &quot;White&quot;,
                &quot;Cream&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:06:45.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:07:02.000000Z&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Buket Kopi&quot;,
            &quot;slug&quot;: &quot;buket-kopi&quot;,
            &quot;category_id&quot;: 2,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;55000.00&quot;,
            &quot;description&quot;: &quot;isi 20 pc kopi nescafe, tanpa bunga, bisa pilih warna wrapping&quot;,
            &quot;images&quot;: [
                &quot;1735006401.jpg&quot;
            ],
            &quot;sizes&quot;: null,
            &quot;colors&quot;: [
                &quot;Brown&quot;,
                &quot;Beige&quot;,
                &quot;Gold&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:13:21.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:13:21.000000Z&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;Buket Snack&quot;,
            &quot;slug&quot;: &quot;buket-snack&quot;,
            &quot;category_id&quot;: 2,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;35000.00&quot;,
            &quot;description&quot;: &quot;isi 8 pc jajanan 2rban, tanpa bunga, bisa pilih wrapping&quot;,
            &quot;images&quot;: [
                &quot;1735006520.jpg&quot;
            ],
            &quot;sizes&quot;: null,
            &quot;colors&quot;: [
                &quot;Colorful&quot;,
                &quot;Rainbow&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:15:20.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:15:20.000000Z&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Buket Bunga Boneka&quot;,
            &quot;slug&quot;: &quot;buket-bunga-boneka&quot;,
            &quot;category_id&quot;: 2,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;80000.00&quot;,
            &quot;description&quot;: &quot;Buket graduation ini bisa di request tanpa topi wisuda, buket sudah dilengkapi dengan bunga yang disusun penuh dan hiasan kupu kupu, warna wrapping dan bunga bisa direquest sesuai keinginan&quot;,
            &quot;images&quot;: [
                &quot;1735006868.jpg&quot;
            ],
            &quot;sizes&quot;: [
                &quot;Medium&quot;,
                &quot;Large&quot;
            ],
            &quot;colors&quot;: [
                &quot;Pink&quot;,
                &quot;Purple&quot;,
                &quot;Blue&quot;,
                &quot;Yellow&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:21:08.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:21:08.000000Z&quot;
        },
        {
            &quot;id&quot;: 6,
            &quot;name&quot;: &quot;Bucket Graduation&quot;,
            &quot;slug&quot;: &quot;bucket-graduation&quot;,
            &quot;category_id&quot;: 2,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;75000.00&quot;,
            &quot;description&quot;: &quot;Buket graduation ini bisa di request tanpa topi wisuda, buket sudah dilengkapi dengan bunga yang disusun penuh dan hiasan kupu kupu, warna wrapping dan bunga bisa direquest sesuai keinginan&quot;,
            &quot;images&quot;: [
                &quot;1735007132.jpg&quot;
            ],
            &quot;sizes&quot;: [
                &quot;Medium&quot;,
                &quot;Large&quot;
            ],
            &quot;colors&quot;: [
                &quot;Red&quot;,
                &quot;Blue&quot;,
                &quot;White&quot;,
                &quot;Gold&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:25:32.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:25:32.000000Z&quot;
        },
        {
            &quot;id&quot;: 7,
            &quot;name&quot;: &quot;Hexagon Frame&quot;,
            &quot;slug&quot;: &quot;hexagon-frame&quot;,
            &quot;category_id&quot;: 3,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;40000.00&quot;,
            &quot;description&quot;: &quot;Frame terbuat dari stick eskrim akasia kualitas terbaik, dilengkapi lampu tumbler dan buket bunga kering mini. isian frame bisa di request sesuai kebutuhan maximal 3 foto dan 1 paragraph kata kata bisa ditambah Judul, Nama, Tanggal&quot;,
            &quot;images&quot;: [
                &quot;1735007208.jpg&quot;
            ],
            &quot;sizes&quot;: [
                &quot;Small&quot;,
                &quot;Medium&quot;
            ],
            &quot;colors&quot;: [
                &quot;Natural Wood&quot;,
                &quot;White&quot;,
                &quot;Black&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:26:48.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:26:48.000000Z&quot;
        },
        {
            &quot;id&quot;: 8,
            &quot;name&quot;: &quot;Lukisan kecil&quot;,
            &quot;slug&quot;: &quot;lukisan-kecil&quot;,
            &quot;category_id&quot;: 3,
            &quot;stock&quot;: 10,
            &quot;price&quot;: &quot;50000.00&quot;,
            &quot;description&quot;: &quot;Lukisan kecil untuk dekorasi ruangan&quot;,
            &quot;images&quot;: [
                &quot;1735007324.jpg&quot;
            ],
            &quot;sizes&quot;: [
                &quot;20x30cm&quot;,
                &quot;30x40cm&quot;
            ],
            &quot;colors&quot;: [
                &quot;Custom&quot;
            ],
            &quot;created_at&quot;: &quot;2024-12-23T19:28:44.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2024-12-23T19:28:44.000000Z&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-home" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-home"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-home"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-home" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-home">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-home" data-method="GET"
      data-path="api/home"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-home', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-home"
                    onclick="tryItOut('GETapi-home');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-home"
                    onclick="cancelTryOut('GETapi-home');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-home"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/home</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-home"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-home"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
