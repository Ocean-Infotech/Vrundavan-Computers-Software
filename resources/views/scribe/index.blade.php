<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>PVM API Documentation</title>

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
        var tryItOutBaseUrl = "http://127.0.0.1:8000/";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.8.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.8.0.js") }}"></script>

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
                    <ul id="tocify-header-authentication" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authentication">
                    <a href="#authentication">Authentication</a>
                </li>
                                    <ul id="tocify-subheader-authentication" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="authentication-POSTapi-student-send-otp">
                                <a href="#authentication-POSTapi-student-send-otp">Student login via birth year or OTP</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-student-verify-otp">
                                <a href="#authentication-POSTapi-student-verify-otp">Verify OTP and log student in</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-student-logout">
                                <a href="#authentication-POSTapi-student-logout">Logout current student</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-GETapi-test">
                                <a href="#endpoints-GETapi-test">GET api/test</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-country">
                                <a href="#endpoints-GETapi-get-country">GET api/get-country</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-state">
                                <a href="#endpoints-GETapi-get-state">GET api/get-state</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-cities">
                                <a href="#endpoints-GETapi-get-cities">GET api/get-cities</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-check-pincode">
                                <a href="#endpoints-GETapi-check-pincode">GET api/check-pincode</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-course">
                                <a href="#endpoints-GETapi-get-course">GET api/get-course</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-semester">
                                <a href="#endpoints-GETapi-get-semester">GET api/get-semester</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-batch">
                                <a href="#endpoints-GETapi-get-batch">GET api/get-batch</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-admission">
                                <a href="#endpoints-GETapi-get-admission">GET api/get-admission</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-books">
                                <a href="#endpoints-GETapi-get-books">GET api/get-books</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-class">
                                <a href="#endpoints-GETapi-get-class">GET api/get-class</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-shift">
                                <a href="#endpoints-GETapi-get-shift">GET api/get-shift</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-class-bybatch">
                                <a href="#endpoints-GETapi-get-class-bybatch">GET api/get-class-bybatch</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-courceregistration">
                                <a href="#endpoints-GETapi-get-courceregistration">GET api/get-courceregistration</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-department">
                                <a href="#endpoints-GETapi-get-department">GET api/get-department</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-get-university">
                                <a href="#endpoints-GETapi-get-university">GET api/get-university</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-request-form">
                                <a href="#endpoints-POSTapi-request-form">POST api/request-form</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-request-list">
                                <a href="#endpoints-POSTapi-request-list">POST api/request-list</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-request-subject-list">
                                <a href="#endpoints-POSTapi-request-subject-list">POST api/request-subject-list</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-documents">
                                <a href="#endpoints-POSTapi-documents">POST api/documents</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-notifications" class="tocify-header">
                <li class="tocify-item level-1" data-unique="notifications">
                    <a href="#notifications">Notifications</a>
                </li>
                                    <ul id="tocify-subheader-notifications" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="notifications-POSTapi-student-notifications-list">
                                <a href="#notifications-POSTapi-student-notifications-list">Get notifications list</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="notifications-POSTapi-student-notifications-mark-read">
                                <a href="#notifications-POSTapi-student-notifications-mark-read">Mark notifications as read</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-slider" class="tocify-header">
                <li class="tocify-item level-1" data-unique="slider">
                    <a href="#slider">Slider</a>
                </li>
                                    <ul id="tocify-subheader-slider" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="slider-POSTapi-slider-add">
                                <a href="#slider-POSTapi-slider-add">Add a new slider</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="slider-POSTapi-slider-list">
                                <a href="#slider-POSTapi-slider-list">List active sliders</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="slider-POSTapi-slider-update">
                                <a href="#slider-POSTapi-slider-update">Update an existing slider</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="slider-DELETEapi-slider-delete">
                                <a href="#slider-DELETEapi-slider-delete">Delete a slider</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-student" class="tocify-header">
                <li class="tocify-item level-1" data-unique="student">
                    <a href="#student">Student</a>
                </li>
                                    <ul id="tocify-subheader-student" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="student-POSTapi-student-profile-detail">
                                <a href="#student-POSTapi-student-profile-detail">Get logged-in student profile</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-update-profile">
                                <a href="#student-POSTapi-student-update-profile">Update logged-in student profile</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-academic-details">
                                <a href="#student-POSTapi-student-academic-details">Get academic details of logged-in student</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-fee-details">
                                <a href="#student-POSTapi-student-fee-details">Get fee details of logged-in student</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-document-details">
                                <a href="#student-POSTapi-student-document-details">Get Google Drive document folder URL</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-contact-details">
                                <a href="#student-POSTapi-student-contact-details">Get institute contact details</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-attendance-details">
                                <a href="#student-POSTapi-student-attendance-details">Get attendance details</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-test-subjects">
                                <a href="#student-POSTapi-student-test-subjects">Get test subjects</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="student-POSTapi-student-test-details">
                                <a href="#student-POSTapi-student-test-details">Get test details</a>
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
        <li>Last updated: March 13, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://127.0.0.1:8000/</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="authentication">Authentication</h1>

    

                                <h2 id="authentication-POSTapi-student-send-otp">Student login via birth year or OTP</h2>

<p>
</p>

<p>If the submitted birth year matches the student’s date of birth, this endpoint logs the student in directly and
returns an access token and profile. If it doesn’t match, it sends a 4‑digit OTP to the registered mobile number
so the student can complete login using the <code>/student/verify-otp</code> endpoint.</p>

<span id="example-requests-POSTapi-student-send-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/send-otp" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"admission_id\": 123,
    \"mobile_no\": \"9876543210\",
    \"birth_year\": 2005,
    \"device_token\": \"fcm_token_123\",
    \"device_details\": \"Android 14; OnePlus 11\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/send-otp"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "admission_id": 123,
    "mobile_no": "9876543210",
    "birth_year": 2005,
    "device_token": "fcm_token_123",
    "device_details": "Android 14; OnePlus 11"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-send-otp">
            <blockquote>
            <p>Example response (200, Birth year matched (direct login)):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Login successful.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;123&quot;,
        &quot;first_name&quot;: &quot;Raj&quot;,
        &quot;last_name&quot;: &quot;Patel&quot;,
        &quot;mobile_no&quot;: &quot;9876543210&quot;,
        &quot;token&quot;: &quot;eyJ0eXAiOiJKV1QiLCJh...&quot;,
        &quot;expiresAt&quot;: &quot;2026-03-13T10:00:00Z&quot;,
        &quot;login_type&quot;: &quot;birth_year&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Birth year did not match (OTP sent)):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Please try again. Birth year does not match.&quot;,
    &quot;data&quot;: {
        &quot;admission_id&quot;: 123,
        &quot;mobile_no&quot;: &quot;9876543210&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Validation failed):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;The admission id field is required.&quot;,
    &quot;messages&quot;: {
        &quot;admission_id&quot;: [
            &quot;The admission id field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-send-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-send-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-send-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-send-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-send-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-send-otp" data-method="POST"
      data-path="api/student/send-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-send-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-send-otp"
                    onclick="tryItOut('POSTapi-student-send-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-send-otp"
                    onclick="cancelTryOut('POSTapi-student-send-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-send-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/send-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-send-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-send-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>admission_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="admission_id"                data-endpoint="POSTapi-student-send-otp"
               value="123"
               data-component="body">
    <br>
<p>The student admission ID. Example: <code>123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mobile_no</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="mobile_no"                data-endpoint="POSTapi-student-send-otp"
               value="9876543210"
               data-component="body">
    <br>
<p>The registered mobile number (10–15 digits). Example: <code>9876543210</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birth_year</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="birth_year"                data-endpoint="POSTapi-student-send-otp"
               value="2005"
               data-component="body">
    <br>
<p>The student birth year (YYYY). Example: <code>2005</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_token"                data-endpoint="POSTapi-student-send-otp"
               value="fcm_token_123"
               data-component="body">
    <br>
<p>The FCM device token for push notifications. Example: <code>fcm_token_123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_details</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_details"                data-endpoint="POSTapi-student-send-otp"
               value="Android 14; OnePlus 11"
               data-component="body">
    <br>
<p>Additional information about the device (model, OS, etc). Example: <code>Android 14; OnePlus 11</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-student-verify-otp">Verify OTP and log student in</h2>

<p>
</p>

<p>Verifies the 4‑digit OTP previously sent to the student’s mobile number. On success, it revokes any previous
tokens, issues a new Passport access token, stores device information (if provided) and returns the student
profile along with the token.</p>

<span id="example-requests-POSTapi-student-verify-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/verify-otp" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"mobile_no\": \"9876543210\",
    \"otp\": \"1234\",
    \"device_token\": \"fcm_token_123\",
    \"device_details\": \"Android 14; OnePlus 11\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/verify-otp"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "mobile_no": "9876543210",
    "otp": "1234",
    "device_token": "fcm_token_123",
    "device_details": "Android 14; OnePlus 11"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-verify-otp">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;OTP verified successfully.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;123&quot;,
        &quot;first_name&quot;: &quot;Raj&quot;,
        &quot;last_name&quot;: &quot;Patel&quot;,
        &quot;mobile_no&quot;: &quot;9876543210&quot;,
        &quot;token&quot;: &quot;eyJ0eXAiOiJKV1QiLCJh...&quot;,
        &quot;expiresAt&quot;: &quot;2026-03-13T10:00:00Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Invalid or expired OTP):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;OTP not matched.&quot;,
    &quot;messages&quot;: {
        &quot;otp&quot;: [
            &quot;OTP not matched.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-verify-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-verify-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-verify-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-verify-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-verify-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-verify-otp" data-method="POST"
      data-path="api/student/verify-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-verify-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-verify-otp"
                    onclick="tryItOut('POSTapi-student-verify-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-verify-otp"
                    onclick="cancelTryOut('POSTapi-student-verify-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-verify-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/verify-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-verify-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-verify-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mobile_no</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="mobile_no"                data-endpoint="POSTapi-student-verify-otp"
               value="9876543210"
               data-component="body">
    <br>
<p>The registered mobile number that received the OTP. Example: <code>9876543210</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>otp</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="otp"                data-endpoint="POSTapi-student-verify-otp"
               value="1234"
               data-component="body">
    <br>
<p>The 4‑digit OTP sent to the mobile number. Example: <code>1234</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_token"                data-endpoint="POSTapi-student-verify-otp"
               value="fcm_token_123"
               data-component="body">
    <br>
<p>The FCM device token for push notifications. Example: <code>fcm_token_123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_details</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_details"                data-endpoint="POSTapi-student-verify-otp"
               value="Android 14; OnePlus 11"
               data-component="body">
    <br>
<p>Additional information about the device (model, OS, etc). Example: <code>Android 14; OnePlus 11</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-student-logout">Logout current student</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Revokes the currently authenticated student’s access token and deletes all stored device tokens for that student.</p>

<span id="example-requests-POSTapi-student-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/logout" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/logout"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-logout">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Logged out successfully.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, Not authenticated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;User not authenticated.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-logout" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-logout" data-method="POST"
      data-path="api/student/logout"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-logout', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-logout"
                    onclick="tryItOut('POSTapi-student-logout');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-logout"
                    onclick="cancelTryOut('POSTapi-student-logout');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-logout"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-GETapi-test">GET api/test</h2>

<p>
</p>



<span id="example-requests-GETapi-test">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/test" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/test"
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

<span id="example-responses-GETapi-test">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/test could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-test" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-test"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-test"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-test" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-test">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-test" data-method="GET"
      data-path="api/test"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-test', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-test"
                    onclick="tryItOut('GETapi-test');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-test"
                    onclick="cancelTryOut('GETapi-test');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-test"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/test</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/test</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/test</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/test</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/test</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/test</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-test"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-test"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-country">GET api/get-country</h2>

<p>
</p>



<span id="example-requests-GETapi-get-country">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-country" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-country"
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

<span id="example-responses-GETapi-get-country">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-country could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-country" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-country"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-country"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-country" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-country">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-country" data-method="GET"
      data-path="api/get-country"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-country', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-country"
                    onclick="tryItOut('GETapi-get-country');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-country"
                    onclick="cancelTryOut('GETapi-get-country');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-country"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-country</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-country</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-country</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-country</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-country</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-country</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-country"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-country"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-state">GET api/get-state</h2>

<p>
</p>



<span id="example-requests-GETapi-get-state">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-state" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-state"
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

<span id="example-responses-GETapi-get-state">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-state could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-state" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-state"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-state"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-state" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-state">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-state" data-method="GET"
      data-path="api/get-state"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-state', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-state"
                    onclick="tryItOut('GETapi-get-state');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-state"
                    onclick="cancelTryOut('GETapi-get-state');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-state"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-state</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-state</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-state</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-state</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-state</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-state</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-state"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-state"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-cities">GET api/get-cities</h2>

<p>
</p>



<span id="example-requests-GETapi-get-cities">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-cities" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-cities"
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

<span id="example-responses-GETapi-get-cities">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-cities could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-cities" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-cities"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-cities"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-cities" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-cities">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-cities" data-method="GET"
      data-path="api/get-cities"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-cities', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-cities"
                    onclick="tryItOut('GETapi-get-cities');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-cities"
                    onclick="cancelTryOut('GETapi-get-cities');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-cities"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-cities</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-cities</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-cities</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-cities</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-cities</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-cities</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-cities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-cities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-check-pincode">GET api/check-pincode</h2>

<p>
</p>



<span id="example-requests-GETapi-check-pincode">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/check-pincode" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/check-pincode"
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

<span id="example-responses-GETapi-check-pincode">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/check-pincode could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-check-pincode" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-check-pincode"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-check-pincode"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-check-pincode" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-check-pincode">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-check-pincode" data-method="GET"
      data-path="api/check-pincode"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-check-pincode', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-check-pincode"
                    onclick="tryItOut('GETapi-check-pincode');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-check-pincode"
                    onclick="cancelTryOut('GETapi-check-pincode');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-check-pincode"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/check-pincode</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/check-pincode</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/check-pincode</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/check-pincode</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/check-pincode</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/check-pincode</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-check-pincode"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-check-pincode"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-course">GET api/get-course</h2>

<p>
</p>



<span id="example-requests-GETapi-get-course">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-course" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-course"
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

<span id="example-responses-GETapi-get-course">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-course could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-course" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-course"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-course"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-course" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-course">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-course" data-method="GET"
      data-path="api/get-course"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-course', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-course"
                    onclick="tryItOut('GETapi-get-course');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-course"
                    onclick="cancelTryOut('GETapi-get-course');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-course"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-course</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-course</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-course</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-course</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-course</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-course</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-course"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-course"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-semester">GET api/get-semester</h2>

<p>
</p>



<span id="example-requests-GETapi-get-semester">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-semester" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-semester"
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

<span id="example-responses-GETapi-get-semester">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-semester could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-semester" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-semester"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-semester"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-semester" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-semester">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-semester" data-method="GET"
      data-path="api/get-semester"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-semester', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-semester"
                    onclick="tryItOut('GETapi-get-semester');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-semester"
                    onclick="cancelTryOut('GETapi-get-semester');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-semester"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-semester</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-semester</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-semester</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-semester</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-semester</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-semester</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-semester"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-semester"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-batch">GET api/get-batch</h2>

<p>
</p>



<span id="example-requests-GETapi-get-batch">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-batch" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-batch"
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

<span id="example-responses-GETapi-get-batch">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-batch could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-batch" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-batch"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-batch"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-batch" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-batch">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-batch" data-method="GET"
      data-path="api/get-batch"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-batch', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-batch"
                    onclick="tryItOut('GETapi-get-batch');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-batch"
                    onclick="cancelTryOut('GETapi-get-batch');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-batch"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-batch</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-batch</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-batch</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-batch</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-batch</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-batch</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-batch"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-batch"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-admission">GET api/get-admission</h2>

<p>
</p>



<span id="example-requests-GETapi-get-admission">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-admission" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-admission"
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

<span id="example-responses-GETapi-get-admission">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-admission could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-admission" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-admission"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-admission"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-admission" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-admission">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-admission" data-method="GET"
      data-path="api/get-admission"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-admission', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-admission"
                    onclick="tryItOut('GETapi-get-admission');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-admission"
                    onclick="cancelTryOut('GETapi-get-admission');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-admission"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-admission</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-admission</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-admission</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-admission</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-admission</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-admission</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-admission"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-admission"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-books">GET api/get-books</h2>

<p>
</p>



<span id="example-requests-GETapi-get-books">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-books" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-books"
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

<span id="example-responses-GETapi-get-books">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-books could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-books" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-books"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-books"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-books" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-books">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-books" data-method="GET"
      data-path="api/get-books"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-books', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-books"
                    onclick="tryItOut('GETapi-get-books');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-books"
                    onclick="cancelTryOut('GETapi-get-books');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-books"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-books</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-books"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-books"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-class">GET api/get-class</h2>

<p>
</p>



<span id="example-requests-GETapi-get-class">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-class" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-class"
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

<span id="example-responses-GETapi-get-class">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-class could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-class" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-class"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-class"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-class" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-class">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-class" data-method="GET"
      data-path="api/get-class"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-class', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-class"
                    onclick="tryItOut('GETapi-get-class');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-class"
                    onclick="cancelTryOut('GETapi-get-class');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-class"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-class</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-class</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-class</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-class</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-class</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-class</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-class"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-class"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-shift">GET api/get-shift</h2>

<p>
</p>



<span id="example-requests-GETapi-get-shift">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-shift" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"course_id\": \"architecto\",
    \"batch_id\": \"architecto\",
    \"class_id\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-shift"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "course_id": "architecto",
    "batch_id": "architecto",
    "class_id": "architecto"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-get-shift">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-shift could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-shift" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-shift"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-shift"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-shift" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-shift">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-shift" data-method="GET"
      data-path="api/get-shift"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-shift', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-shift"
                    onclick="tryItOut('GETapi-get-shift');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-shift"
                    onclick="cancelTryOut('GETapi-get-shift');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-shift"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-shift</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-shift</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-shift</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-shift</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-shift</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-shift</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-shift"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-shift"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>course_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="course_id"                data-endpoint="GETapi-get-shift"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>batch_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="batch_id"                data-endpoint="GETapi-get-shift"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>class_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="class_id"                data-endpoint="GETapi-get-shift"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-get-class-bybatch">GET api/get-class-bybatch</h2>

<p>
</p>



<span id="example-requests-GETapi-get-class-bybatch">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-class-bybatch" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"batch_id\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-class-bybatch"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "batch_id": "architecto"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-get-class-bybatch">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-class-bybatch could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-class-bybatch" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-class-bybatch"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-class-bybatch"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-class-bybatch" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-class-bybatch">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-class-bybatch" data-method="GET"
      data-path="api/get-class-bybatch"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-class-bybatch', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-class-bybatch"
                    onclick="tryItOut('GETapi-get-class-bybatch');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-class-bybatch"
                    onclick="cancelTryOut('GETapi-get-class-bybatch');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-class-bybatch"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-class-bybatch</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-class-bybatch</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-class-bybatch</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-class-bybatch</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-class-bybatch</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-class-bybatch</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-class-bybatch"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-class-bybatch"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>batch_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="batch_id"                data-endpoint="GETapi-get-class-bybatch"
               value="architecto"
               data-component="body">
    <br>
<p>Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-get-courceregistration">GET api/get-courceregistration</h2>

<p>
</p>



<span id="example-requests-GETapi-get-courceregistration">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-courceregistration" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-courceregistration"
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

<span id="example-responses-GETapi-get-courceregistration">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-courceregistration could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-courceregistration" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-courceregistration"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-courceregistration"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-courceregistration" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-courceregistration">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-courceregistration" data-method="GET"
      data-path="api/get-courceregistration"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-courceregistration', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-courceregistration"
                    onclick="tryItOut('GETapi-get-courceregistration');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-courceregistration"
                    onclick="cancelTryOut('GETapi-get-courceregistration');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-courceregistration"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-courceregistration</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-courceregistration</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-courceregistration</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-courceregistration</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-courceregistration</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-courceregistration</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-courceregistration"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-courceregistration"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-department">GET api/get-department</h2>

<p>
</p>



<span id="example-requests-GETapi-get-department">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-department" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-department"
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

<span id="example-responses-GETapi-get-department">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-department could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-department" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-department"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-department"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-department" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-department">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-department" data-method="GET"
      data-path="api/get-department"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-department', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-department"
                    onclick="tryItOut('GETapi-get-department');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-department"
                    onclick="cancelTryOut('GETapi-get-department');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-department"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-department</code></b>
        </p>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/get-department</code></b>
        </p>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/get-department</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/get-department</code></b>
        </p>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/get-department</code></b>
        </p>
            <p>
            <small class="badge badge-grey">OPTIONS</small>
            <b><code>api/get-department</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-department"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-department"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-get-university">GET api/get-university</h2>

<p>
</p>



<span id="example-requests-GETapi-get-university">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/get-university" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/get-university"
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

<span id="example-responses-GETapi-get-university">
            <blockquote>
            <p>Example response (404):</p>
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
    &quot;message&quot;: &quot;The route api/get-university could not be found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-get-university" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-get-university"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-get-university"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-get-university" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-get-university">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-get-university" data-method="GET"
      data-path="api/get-university"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-get-university', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-get-university"
                    onclick="tryItOut('GETapi-get-university');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-get-university"
                    onclick="cancelTryOut('GETapi-get-university');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-get-university"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/get-university</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-get-university"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-get-university"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-request-form">POST api/request-form</h2>

<p>
</p>



<span id="example-requests-POSTapi-request-form">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/request-form" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/request-form"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-request-form">
</span>
<span id="execution-results-POSTapi-request-form" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-request-form"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-request-form"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-request-form" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-request-form">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-request-form" data-method="POST"
      data-path="api/request-form"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-request-form', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-request-form"
                    onclick="tryItOut('POSTapi-request-form');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-request-form"
                    onclick="cancelTryOut('POSTapi-request-form');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-request-form"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/request-form</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-request-form"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-request-form"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-request-list">POST api/request-list</h2>

<p>
</p>



<span id="example-requests-POSTapi-request-list">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/request-list" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/request-list"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-request-list">
</span>
<span id="execution-results-POSTapi-request-list" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-request-list"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-request-list"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-request-list" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-request-list">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-request-list" data-method="POST"
      data-path="api/request-list"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-request-list', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-request-list"
                    onclick="tryItOut('POSTapi-request-list');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-request-list"
                    onclick="cancelTryOut('POSTapi-request-list');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-request-list"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/request-list</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-request-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-request-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-request-subject-list">POST api/request-subject-list</h2>

<p>
</p>



<span id="example-requests-POSTapi-request-subject-list">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/request-subject-list" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/request-subject-list"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-request-subject-list">
</span>
<span id="execution-results-POSTapi-request-subject-list" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-request-subject-list"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-request-subject-list"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-request-subject-list" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-request-subject-list">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-request-subject-list" data-method="POST"
      data-path="api/request-subject-list"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-request-subject-list', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-request-subject-list"
                    onclick="tryItOut('POSTapi-request-subject-list');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-request-subject-list"
                    onclick="cancelTryOut('POSTapi-request-subject-list');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-request-subject-list"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/request-subject-list</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-request-subject-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-request-subject-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-documents">POST api/documents</h2>

<p>
</p>



<span id="example-requests-POSTapi-documents">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/documents" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/documents"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-documents">
</span>
<span id="execution-results-POSTapi-documents" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-documents"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-documents"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-documents" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-documents">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-documents" data-method="POST"
      data-path="api/documents"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-documents', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-documents"
                    onclick="tryItOut('POSTapi-documents');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-documents"
                    onclick="cancelTryOut('POSTapi-documents');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-documents"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/documents</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-documents"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-documents"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="notifications">Notifications</h1>

    

                                <h2 id="notifications-POSTapi-student-notifications-list">Get notifications list</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns all notifications visible to the logged‑in student (personal, course‑level and general).</p>

<span id="example-requests-POSTapi-student-notifications-list">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/notifications-list" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/notifications-list"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-notifications-list">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Notifications List.&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;title&quot;: &quot;Exam schedule&quot;,
            &quot;body&quot;: &quot;Your mid‑semester exams start next week.&quot;,
            &quot;is_read&quot;: false,
            &quot;date_time&quot;: &quot;2025-07-01 10:00:00&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-notifications-list" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-notifications-list"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-notifications-list"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-notifications-list" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-notifications-list">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-notifications-list" data-method="POST"
      data-path="api/student/notifications-list"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-notifications-list', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-notifications-list"
                    onclick="tryItOut('POSTapi-student-notifications-list');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-notifications-list"
                    onclick="cancelTryOut('POSTapi-student-notifications-list');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-notifications-list"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/notifications-list</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-notifications-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-notifications-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="notifications-POSTapi-student-notifications-mark-read">Mark notifications as read</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Marks specific notifications as read for the logged‑in student. If no IDs are provided,
all unread notifications for the student are marked as read.</p>

<span id="example-requests-POSTapi-student-notifications-mark-read">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/notifications-mark-read" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"notification_ids\": [
        1,
        2,
        3
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/notifications-mark-read"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "notification_ids": [
        1,
        2,
        3
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-notifications-mark-read">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Notifications marked as read successfully.&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;title&quot;: &quot;Exam schedule&quot;,
            &quot;body&quot;: &quot;Your mid‑semester exams start next week.&quot;,
            &quot;is_read&quot;: true
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-notifications-mark-read" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-notifications-mark-read"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-notifications-mark-read"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-notifications-mark-read" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-notifications-mark-read">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-notifications-mark-read" data-method="POST"
      data-path="api/student/notifications-mark-read"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-notifications-mark-read', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-notifications-mark-read"
                    onclick="tryItOut('POSTapi-student-notifications-mark-read');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-notifications-mark-read"
                    onclick="cancelTryOut('POSTapi-student-notifications-mark-read');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-notifications-mark-read"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/notifications-mark-read</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-notifications-mark-read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-notifications-mark-read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>notification_ids</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
<br>
<p>The IDs of notifications to mark as read.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>*</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="notification_ids.*"                data-endpoint="POSTapi-student-notifications-mark-read"
               value="16"
               data-component="body">
    <br>
<p>A single notification ID. Example: <code>16</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                <h1 id="slider">Slider</h1>

    <p>APIs for managing sliders shown in the application.</p>

                                <h2 id="slider-POSTapi-slider-add">Add a new slider</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Uploads a new slider image with a title and an optional link. The image will be stored under
<code>uploads/sliders</code> and its full URL will be returned in the response.</p>

<span id="example-requests-POSTapi-slider-add">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/slider-add" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "title=Summer Offers"\
    --form "link=https://example.com/offers"\
    --form "image=@C:\Users\NIMIT RATHOFD\AppData\Local\Temp\php34B2.tmp" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/slider-add"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('title', 'Summer Offers');
body.append('link', 'https://example.com/offers');
body.append('image', document.querySelector('input[name="image"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-slider-add">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Slider added successfully.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;title&quot;: &quot;Summer Offers&quot;,
        &quot;image&quot;: &quot;uploads/sliders/1710300000_banner.webp&quot;,
        &quot;link&quot;: &quot;https://example.com/offers&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Validation failed):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;The title field is required.&quot;,
    &quot;messages&quot;: {
        &quot;title&quot;: [
            &quot;The title field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-slider-add" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-slider-add"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-slider-add"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-slider-add" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-slider-add">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-slider-add" data-method="POST"
      data-path="api/slider-add"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-slider-add', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-slider-add"
                    onclick="tryItOut('POSTapi-slider-add');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-slider-add"
                    onclick="cancelTryOut('POSTapi-slider-add');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-slider-add"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/slider-add</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-slider-add"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-slider-add"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="POSTapi-slider-add"
               value="Summer Offers"
               data-component="body">
    <br>
<p>The slider title to display on the UI. Example: <code>Summer Offers</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>image</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="image"                data-endpoint="POSTapi-slider-add"
               value=""
               data-component="body">
    <br>
<p>The image file (jpeg, png, jpg, gif or webp; max 2MB). Example: <code>C:\Users\NIMIT RATHOFD\AppData\Local\Temp\php34B2.tmp</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>link</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="link"                data-endpoint="POSTapi-slider-add"
               value="https://example.com/offers"
               data-component="body">
    <br>
<p>A URL to open when the slider is tapped. Example: <code>https://example.com/offers</code></p>
        </div>
        </form>

                    <h2 id="slider-POSTapi-slider-list">List active sliders</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns all active sliders in reverse creation order. Each slider includes the absolute image URL.</p>

<span id="example-requests-POSTapi-slider-list">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/slider-list" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/slider-list"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-slider-list">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Sliders retrieved successfully.&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;title&quot;: &quot;Summer Offers&quot;,
            &quot;image&quot;: &quot;https://example.com/uploads/sliders/1710300000_banner.webp&quot;,
            &quot;link&quot;: &quot;https://example.com/offers&quot;,
            &quot;status&quot;: &quot;active&quot;
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, No sliders):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;No sliders found.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-slider-list" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-slider-list"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-slider-list"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-slider-list" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-slider-list">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-slider-list" data-method="POST"
      data-path="api/slider-list"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-slider-list', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-slider-list"
                    onclick="tryItOut('POSTapi-slider-list');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-slider-list"
                    onclick="cancelTryOut('POSTapi-slider-list');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-slider-list"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/slider-list</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-slider-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-slider-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="slider-POSTapi-slider-update">Update an existing slider</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Updates the title, link and (optionally) the image for a specific slider.
If a new image is uploaded, the previous image file will be deleted.</p>

<span id="example-requests-POSTapi-slider-update">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/slider-update" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "id=1"\
    --form "title=Updated Summer Offers"\
    --form "link=https://example.com/new-offers"\
    --form "image=@C:\Users\NIMIT RATHOFD\AppData\Local\Temp\php34F2.tmp" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/slider-update"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('id', '1');
body.append('title', 'Updated Summer Offers');
body.append('link', 'https://example.com/new-offers');
body.append('image', document.querySelector('input[name="image"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-slider-update">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Slider updated successfully.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;title&quot;: &quot;Updated Summer Offers&quot;,
        &quot;image&quot;: &quot;uploads/sliders/1710301000_banner.webp&quot;,
        &quot;link&quot;: &quot;https://example.com/new-offers&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, Slider not found):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;Slider not found.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-slider-update" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-slider-update"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-slider-update"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-slider-update" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-slider-update">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-slider-update" data-method="POST"
      data-path="api/slider-update"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-slider-update', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-slider-update"
                    onclick="tryItOut('POSTapi-slider-update');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-slider-update"
                    onclick="cancelTryOut('POSTapi-slider-update');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-slider-update"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/slider-update</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-slider-update"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-slider-update"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="POSTapi-slider-update"
               value="1"
               data-component="body">
    <br>
<p>The ID of the slider to update. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="POSTapi-slider-update"
               value="Updated Summer Offers"
               data-component="body">
    <br>
<p>The new title for the slider. Example: <code>Updated Summer Offers</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>image</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="image"                data-endpoint="POSTapi-slider-update"
               value=""
               data-component="body">
    <br>
<p>The new image file to replace the old one (jpeg, png, jpg, gif or webp; max 2MB). Example: <code>C:\Users\NIMIT RATHOFD\AppData\Local\Temp\php34F2.tmp</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>link</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="link"                data-endpoint="POSTapi-slider-update"
               value="https://example.com/new-offers"
               data-component="body">
    <br>
<p>The new URL to open when the slider is tapped. Example: <code>https://example.com/new-offers</code></p>
        </div>
        </form>

                    <h2 id="slider-DELETEapi-slider-delete">Delete a slider</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Deletes a slider by its ID and removes its image file from storage.</p>

<span id="example-requests-DELETEapi-slider-delete">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/slider-delete" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/slider-delete"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-slider-delete">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Slider deleted successfully.&quot;,
    &quot;data&quot;: null
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, Slider not found):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;Slider not found.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-slider-delete" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-slider-delete"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-slider-delete"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-slider-delete" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-slider-delete">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-slider-delete" data-method="DELETE"
      data-path="api/slider-delete"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-slider-delete', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-slider-delete"
                    onclick="tryItOut('DELETEapi-slider-delete');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-slider-delete"
                    onclick="cancelTryOut('DELETEapi-slider-delete');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-slider-delete"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/slider-delete</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-slider-delete"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-slider-delete"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="DELETEapi-slider-delete"
               value="1"
               data-component="url">
    <br>
<p>The ID of the slider to delete. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="student">Student</h1>

    <p>APIs for student academic data, fees, attendance, tests and notifications.</p>

                                <h2 id="student-POSTapi-student-profile-detail">Get logged-in student profile</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the complete profile of the currently authenticated student, including personal details,
contact information and status.</p>

<span id="example-requests-POSTapi-student-profile-detail">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/profile-detail" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/profile-detail"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-profile-detail">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Login user profile get successfully.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;123&quot;,
        &quot;first_name&quot;: &quot;Raj&quot;,
        &quot;last_name&quot;: &quot;Patel&quot;,
        &quot;father_name&quot;: &quot;Mahesh&quot;,
        &quot;mobile_no&quot;: &quot;9876543210&quot;,
        &quot;email_address&quot;: &quot;raj@example.com&quot;,
        &quot;status&quot;: &quot;active&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, Not authenticated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;Unauthorization.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-profile-detail" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-profile-detail"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-profile-detail"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-profile-detail" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-profile-detail">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-profile-detail" data-method="POST"
      data-path="api/student/profile-detail"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-profile-detail', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-profile-detail"
                    onclick="tryItOut('POSTapi-student-profile-detail');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-profile-detail"
                    onclick="cancelTryOut('POSTapi-student-profile-detail');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-profile-detail"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/profile-detail</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-profile-detail"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-profile-detail"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="student-POSTapi-student-update-profile">Update logged-in student profile</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Updates key contact and identification fields (mobile numbers, Aadhaar and addresses) for the
currently authenticated student. Returns the fresh profile after update.</p>

<span id="example-requests-POSTapi-student-update-profile">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/update-profile" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"mobile_no\": \"9876543210\",
    \"other_mobile_no\": \"9123456780\",
    \"whatsapp_no\": \"9876543210\",
    \"aadhar_card_no\": \"234567890123\",
    \"temporary_address\": \"12, Shree Nagar Society, Surat\",
    \"permanent_address\": \"45, Patel Street, Junagadh\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/update-profile"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "mobile_no": "9876543210",
    "other_mobile_no": "9123456780",
    "whatsapp_no": "9876543210",
    "aadhar_card_no": "234567890123",
    "temporary_address": "12, Shree Nagar Society, Surat",
    "permanent_address": "45, Patel Street, Junagadh"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-update-profile">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Update student profile successfully.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: &quot;123&quot;,
        &quot;mobile_no&quot;: &quot;9876543210&quot;,
        &quot;other_mobile_no&quot;: &quot;9123456780&quot;,
        &quot;whatsapp_no&quot;: &quot;9876543210&quot;,
        &quot;temporary_address&quot;: &quot;12, Shree Nagar Society, Surat&quot;,
        &quot;permanent_address&quot;: &quot;45, Patel Street, Junagadh&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, Not authenticated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;Unauthorization&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-update-profile" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-update-profile"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-update-profile"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-update-profile" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-update-profile">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-update-profile" data-method="POST"
      data-path="api/student/update-profile"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-update-profile', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-update-profile"
                    onclick="tryItOut('POSTapi-student-update-profile');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-update-profile"
                    onclick="cancelTryOut('POSTapi-student-update-profile');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-update-profile"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/update-profile</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-update-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-update-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mobile_no</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="mobile_no"                data-endpoint="POSTapi-student-update-profile"
               value="9876543210"
               data-component="body">
    <br>
<p>Primary mobile number (must be unique). Example: <code>9876543210</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>other_mobile_no</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="other_mobile_no"                data-endpoint="POSTapi-student-update-profile"
               value="9123456780"
               data-component="body">
    <br>
<p>Alternate mobile number (must be unique). Example: <code>9123456780</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>whatsapp_no</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="whatsapp_no"                data-endpoint="POSTapi-student-update-profile"
               value="9876543210"
               data-component="body">
    <br>
<p>WhatsApp number (must be unique if provided). Example: <code>9876543210</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>aadhar_card_no</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="aadhar_card_no"                data-endpoint="POSTapi-student-update-profile"
               value="234567890123"
               data-component="body">
    <br>
<p>12‑digit Aadhaar number. Example: <code>234567890123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>temporary_address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="temporary_address"                data-endpoint="POSTapi-student-update-profile"
               value="12, Shree Nagar Society, Surat"
               data-component="body">
    <br>
<p>Current/temporary address. Example: <code>12, Shree Nagar Society, Surat</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>permanent_address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="permanent_address"                data-endpoint="POSTapi-student-update-profile"
               value="45, Patel Street, Junagadh"
               data-component="body">
    <br>
<p>Permanent address. Example: <code>45, Patel Street, Junagadh</code></p>
        </div>
        </form>

                    <h2 id="student-POSTapi-student-academic-details">Get academic details of logged-in student</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the academic information (university, course, batch, class, shift timings and timetable attachment)
for the currently authenticated student.</p>

<span id="example-requests-POSTapi-student-academic-details">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/academic-details" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/academic-details"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-academic-details">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Academic details fetched successfully.&quot;,
    &quot;data&quot;: {
        &quot;university&quot;: &quot;Saurashtra University&quot;,
        &quot;department&quot;: &quot;Computer Science&quot;,
        &quot;course&quot;: &quot;BCA&quot;,
        &quot;batch&quot;: &quot;2024-2027&quot;,
        &quot;class&quot;: &quot;TYBCA&quot;,
        &quot;shift&quot;: &quot;09:00 AM To 12:00 PM&quot;,
        &quot;gr_no&quot;: 123,
        &quot;enrollment_no&quot;: &quot;ENR2024001&quot;,
        &quot;spid&quot;: &quot;SPID12345&quot;,
        &quot;udise&quot;: &quot;UDISE1234&quot;,
        &quot;apaar_id_abc_id&quot;: &quot;APAR123456&quot;,
        &quot;attachment&quot;: &quot;https://example.com/storage/timetable.pdf&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, Not authenticated):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;Unauthorization.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-academic-details" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-academic-details"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-academic-details"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-academic-details" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-academic-details">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-academic-details" data-method="POST"
      data-path="api/student/academic-details"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-academic-details', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-academic-details"
                    onclick="tryItOut('POSTapi-student-academic-details');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-academic-details"
                    onclick="cancelTryOut('POSTapi-student-academic-details');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-academic-details"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/academic-details</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-academic-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-academic-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="student-POSTapi-student-fee-details">Get fee details of logged-in student</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the total course fees, total paid and pending amount, along with a list of paid fee records
(semester-wise) for the currently authenticated student.</p>

<span id="example-requests-POSTapi-student-fee-details">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/fee-details" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/fee-details"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-fee-details">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Fee details fetched successfully.&quot;,
    &quot;data&quot;: {
        &quot;total&quot;: 45000,
        &quot;paid&quot;: 30000,
        &quot;pending&quot;: 15000,
        &quot;details&quot;: [
            {
                &quot;course&quot;: &quot;BCA&quot;,
                &quot;semester&quot;: &quot;Semester 1&quot;,
                &quot;receipt_no&quot;: 10,
                &quot;amount&quot;: 15000,
                &quot;date&quot;: &quot;2025-06-01&quot;,
                &quot;status&quot;: &quot;Paid&quot;
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, No fee records):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;No fee records found.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-fee-details" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-fee-details"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-fee-details"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-fee-details" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-fee-details">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-fee-details" data-method="POST"
      data-path="api/student/fee-details"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-fee-details', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-fee-details"
                    onclick="tryItOut('POSTapi-student-fee-details');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-fee-details"
                    onclick="cancelTryOut('POSTapi-student-fee-details');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-fee-details"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/fee-details</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-fee-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-fee-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="student-POSTapi-student-document-details">Get Google Drive document folder URL</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the Google Drive folder URL associated with the logged‑in student (if configured).</p>

<span id="example-requests-POSTapi-student-document-details">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/document-details" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/document-details"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-document-details">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Google Drive folder link fetched successfully.&quot;,
    &quot;data&quot;: {
        &quot;gdrive_url&quot;: &quot;https://drive.google.com/drive/folders/abc123&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, No folder configured):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;No Google Drive folder found.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-document-details" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-document-details"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-document-details"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-document-details" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-document-details">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-document-details" data-method="POST"
      data-path="api/student/document-details"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-document-details', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-document-details"
                    onclick="tryItOut('POSTapi-student-document-details');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-document-details"
                    onclick="cancelTryOut('POSTapi-student-document-details');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-document-details"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/document-details</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-document-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-document-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="student-POSTapi-student-contact-details">Get institute contact details</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns static contact information (branches and phone numbers) for the institute.</p>

<span id="example-requests-POSTapi-student-contact-details">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/contact-details" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/contact-details"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-contact-details">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Contact Us Information fetched successfully.&quot;,
    &quot;data&quot;: {
        &quot;branches&quot;: [
            {
                &quot;name&quot;: &quot;Keshod Branch&quot;,
                &quot;address&quot;: &quot;Amrut nagar road, Keshod&quot;,
                &quot;email&quot;: &quot;vrundavancomputerkeshod@gmail.com&quot;
            }
        ],
        &quot;contacts&quot;: {
            &quot;Vrundavan Computer&quot;: {
                &quot;College mobile&quot;: &quot;96874 51774&quot;,
                &quot;Phone&quot;: &quot;02871-231059&quot;,
                &quot;Distance&quot;: &quot;93752 21111&quot;
            }
        }
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-contact-details" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-contact-details"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-contact-details"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-contact-details" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-contact-details">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-contact-details" data-method="POST"
      data-path="api/student/contact-details"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-contact-details', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-contact-details"
                    onclick="tryItOut('POSTapi-student-contact-details');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-contact-details"
                    onclick="cancelTryOut('POSTapi-student-contact-details');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-contact-details"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/contact-details</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-contact-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-contact-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="student-POSTapi-student-attendance-details">Get attendance details</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns attendance for a single date or for an entire month for the logged‑in student.
If <code>date</code> is provided, returns a single record; otherwise, returns a monthly summary and daily records.</p>

<span id="example-requests-POSTapi-student-attendance-details">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/attendance-details" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"date\": \"01-07-2025\",
    \"month\": 7,
    \"year\": 2025
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/attendance-details"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "date": "01-07-2025",
    "month": 7,
    "year": 2025
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-attendance-details">
            <blockquote>
            <p>Example response (200, Single date):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Attendance records fetched successfully.&quot;,
    &quot;data&quot;: {
        &quot;summary&quot;: {
            &quot;total_lectures&quot;: 1,
            &quot;present_lectures&quot;: 1,
            &quot;absent_lectures&quot;: 0,
            &quot;average_percent&quot;: 100,
            &quot;date&quot;: &quot;01-July-2025&quot;
        },
        &quot;records&quot;: [
            {
                &quot;date&quot;: &quot;2025-07-01&quot;,
                &quot;day&quot;: &quot;Tuesday&quot;,
                &quot;in_time&quot;: &quot;09:05 AM&quot;,
                &quot;out_time&quot;: &quot;12:00 PM&quot;,
                &quot;status&quot;: &quot;Present&quot;,
                &quot;status_code&quot;: &quot;P&quot;
            }
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-attendance-details" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-attendance-details"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-attendance-details"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-attendance-details" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-attendance-details">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-attendance-details" data-method="POST"
      data-path="api/student/attendance-details"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-attendance-details', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-attendance-details"
                    onclick="tryItOut('POSTapi-student-attendance-details');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-attendance-details"
                    onclick="cancelTryOut('POSTapi-student-attendance-details');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-attendance-details"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/attendance-details</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-attendance-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-attendance-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="date"                data-endpoint="POSTapi-student-attendance-details"
               value="01-07-2025"
               data-component="body">
    <br>
<p>The specific date to check (format: d-m-Y). Example: <code>01-07-2025</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>month</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="month"                data-endpoint="POSTapi-student-attendance-details"
               value="7"
               data-component="body">
    <br>
<p>The month number (1–12) for which to fetch attendance. Example: <code>7</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>year</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="year"                data-endpoint="POSTapi-student-attendance-details"
               value="2025"
               data-component="body">
    <br>
<p>The year for which to fetch attendance. Example: <code>2025</code></p>
        </div>
        </form>

                    <h2 id="student-POSTapi-student-test-subjects">Get test subjects</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns the list of subjects for which the logged‑in student has test records.</p>

<span id="example-requests-POSTapi-student-test-subjects">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/test-subjects" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/test-subjects"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-test-subjects">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Subjects List.&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Mathematics&quot;
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, No subjects):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;false&quot;,
    &quot;message&quot;: &quot;No subjects found.&quot;,
    &quot;data&quot;: []
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-test-subjects" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-test-subjects"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-test-subjects"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-test-subjects" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-test-subjects">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-test-subjects" data-method="POST"
      data-path="api/student/test-subjects"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-test-subjects', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-test-subjects"
                    onclick="tryItOut('POSTapi-student-test-subjects');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-test-subjects"
                    onclick="cancelTryOut('POSTapi-student-test-subjects');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-test-subjects"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/test-subjects</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-test-subjects"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-test-subjects"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="student-POSTapi-student-test-details">Get test details</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns detailed test results (subject, unit, course, batch, date, marks and percentage) for the logged‑in student.
You can optionally filter by subject or search by subject name.</p>

<span id="example-requests-POSTapi-student-test-details">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/student/test-details" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"subject_id\": 1,
    \"subject_name\": \"Mathematics\",
    \"search\": \"Maths\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/student/test-details"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "subject_id": 1,
    "subject_name": "Mathematics",
    "search": "Maths"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-student-test-details">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;true&quot;,
    &quot;message&quot;: &quot;Test details fetched successfully.&quot;,
    &quot;data&quot;: [
        {
            &quot;subject&quot;: &quot;Mathematics&quot;,
            &quot;unit&quot;: &quot;Unit 1&quot;,
            &quot;course&quot;: &quot;BCA&quot;,
            &quot;batch&quot;: &quot;2024-2027&quot;,
            &quot;date&quot;: &quot;01-07-2025&quot;,
            &quot;marks&quot;: &quot;18/20&quot;,
            &quot;percentage&quot;: &quot;90%&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-student-test-details" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-student-test-details"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-student-test-details"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-student-test-details" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-student-test-details">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-student-test-details" data-method="POST"
      data-path="api/student/test-details"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-student-test-details', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-student-test-details"
                    onclick="tryItOut('POSTapi-student-test-details');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-student-test-details"
                    onclick="cancelTryOut('POSTapi-student-test-details');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-student-test-details"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/student/test-details</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-student-test-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-student-test-details"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>subject_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="subject_id"                data-endpoint="POSTapi-student-test-details"
               value="1"
               data-component="body">
    <br>
<p>The subject ID to filter tests by. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>subject_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="subject_name"                data-endpoint="POSTapi-student-test-details"
               value="Mathematics"
               data-component="body">
    <br>
<p>The exact subject name to filter tests by. Example: <code>Mathematics</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="search"                data-endpoint="POSTapi-student-test-details"
               value="Maths"
               data-component="body">
    <br>
<p>A text to search in subject names (partial match). Example: <code>Maths</code></p>
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
