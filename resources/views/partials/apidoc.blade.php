<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Get Started</a></li>
        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Reference Guide</a></li>
    </ul>
    <div class="tab-content tc-onyx" style="font-family:Helvetica">
        <div class="tab-pane active" id="tab_1">

            <p class="tc-onyx">
            API functions need an API key - we simply need a means to manage access. The good news is that
                you can generate API keys yourself when you need them. This flexibility gives enough room
                for an automated integration of the monitoring with Let's Encrypt - no  client
                personalization needed.
            </p>
            <p class="tc-onyx">
                There are three types of calls you need for integration:
            </p>
            <ul>
                <li><p>Claim API key - one-off call, where your client requests its own unique API key. There
                    are two versions - POST and GET. The difference is whether the client generates its own
                    API key (POST) or KeyChest creates one and sends it back.</p></li>
                <li><p>Add server - submit a domain name for monitoring.</p></li>
                <li><p>Check expiration - check whether a certificate needs to be renewed.</p></li>
            </ul>

            <p class="tc-onyx">
                We recommend the client keeps two pieces of information as its state. The first item is its
                API key. The second is a timestamp of its last execution, which can be compared against
                "last scan" timestamps returned by the "Check expiration" API call.
            </p>

            <h3>Examples using curl</h3>

            <p>
                <b>Step 1: get an API key</b>
            </p>
            <code>
                curl https://keychest.net/api/v1.0/access/claim/me@myemail.com
            </code>
            <p>
                If you are familiar with <code>jq</code>, you can get a new API key with the following command:
            </p>
            <code>curl -s https://keychest.net/api/v1.0/access/claim/me@myemail.com | jq -r '.api_key'</code>

            <br/><br/>
            <p>
                <b>Step 2: register for monitoring</b>
            </p>
            <code>
                curl -s -X POST -H "Content-Type: application/json" -d '{"api_key":"5b9b6aceb95011e7bb9f7fca73a26228", "domain":"fish.enigmabridge.com"}' https://keychest.net/api/v1.0/servers/add
            </code>

            <br/><br/>
            <p>
                <b>Step 3: check expiration</b>
            </p>
            <code>
                curl -s https://keychest.net/api/v1.0/servers/expiration/fish.enigmabridge.com?api_key=da70d5e4864f57e910e66bd21c685b26
            </code>

            <h3>List of basic API functions</h3>

            <table class="table">
                <tr>
                    <th>Register/check API key </th>
                    <th>GET request<br/>The client requests KeyChest to generate an API key for a user account
                        identified with the <i>email</i>.</th>
                </tr>
                <tr>
                    <td>Request</td>
                    <td><code class="tc-rich-electric-blue">https://keychest.net/api/v1.0/access/claim/your@email.com</code></td>
                </tr>
                <tr><td>Response</td>
                    <td>
<pre>
{
    "status": "created",
    "user": "your@email.com",
    "api-key": "5b9b6aceb95011e7bb9f7fca73a26228"
}
</pre>
                    </td></tr>
                <tr><td>Notes</td>
                    <td>
                        <p>
                        <p><b>email</b> - a mandatory parameter, it will be used to access an existing account,
                            or to create a new account.</p>
                        <br/>
                        <p>
                            A successful response returns the "status" value of "created".
                        </p>
                    </td></tr>


                <tr>
                    <th>Register/check API key </th>
                    <th>POST request<br/>The client has
                        to create its <i>api_key</i> and send it to the KeyChest with an <i>email</i>
                        identifying a user account.</th>
                </tr>
                <tr>
                    <td>Request</td>
                    <td><code class="tc-rich-electric-blue">https://keychest.net/api/v1.0/access/claim</code><br/>
                        The content-type header must be: <i>application/json</i>, and the body contains JSON data.
<pre>
{
    "email":"your@email.com",
    "api_key":"5b9b6aceb95011e7bb9f7fca73a26228"
}
</pre>
                    </td>
                </tr>
                <tr><td>Response</td>
                    <td>
<pre>
{
    "status": "created",
    "user": "your@email.com",
    "api-key": "5b9b6aceb95011e7bb9f7fca73a26228"
}
</pre>
                    or
<pre>
{
    "status":"success"
}
</pre>
                    </td></tr>
                <tr><td>Notes</td>
                <td>
                        <p>
                                <b>api_key</b> - a mandatory parameter, the value must be at least 16
                            characters and no more than 64 characters, allowed characters are lower and
                            upper case letters, digits, and "-". (i.e., [a-zA-Z0-9-]* )
                            </p>
                        <p><b>email</b> - a mandatory parameter, it will be used to access an existing
                            account, or to create a new account.</p>
                    <br/>
                    <p>
                        A successful response returns the "status" value of "created", or "success" - depending on whether
                        a new API key was created (former), or it was already in the KeyChest database.
                    </p>
                </td></tr>
                <tr>
                    <th>Register domain name</th>
                    <th>POST request<br/>This API call adds a new domain name for monitoring.
                    It can be used repeatedly for the same domain name.</th>
                </tr>
                <tr>
                    <td>Request</td>
                    <td>
                        <code class="tc-rich-electric-blue">https://keychest.net/api/v1.0/servers/add</code><br/>
                        The content-type header must be: <i>application/json</i>, and the body contains JSON data.
<pre>
{
    "api_key":"5b9b6aceb95011e7bb9f7fca73a26228",
    "domain":"fish.enigmabridge.com"
}
</pre>
                    </td>
                </tr>
                <tr>
                    <td>Response</td>
                    <td>
<pre>
{
    "status": "success",
    "id": "671d1b10-bb1d-11e7-bae1-571d93cf1c53",
    "key": "da70d5e4864f57e910e66bd21c685b26",
    "domain": "fish.enigmabridge.com
}
</pre>
                    </td>
                </tr>
                <tr>
                    <td>Notes</td>
                    <td>
                        <p>
                            <b>api_key</b> - a valid API key registered with the KeyChest.
                        </p>
                        <p><b>domain</b> - a domain name for monitoring; it can include a port number
                        in the usual syntax (e.g., fish.enigmabridge.com:25).
                        </p><br/>
                        <p>
                            Responses contain an <i>id</i>, which can be used for asynchronous processing,
                            <i>key</i> which is an internal representation of the domain name, and the status.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>Check expiration</th>
                    <th>GET request<br/>This is a request to return the latest status information for a given
                        domain name. Responses contain the timestamp of the lastest status update to allow
                        clients check freshness.
                    </th>
                </tr>
                <tr>
                    <td>Request</td>
                    <td>
                        <code class="tc-rich-electric-blue">https://keychest.net/api/v1.0/servers/expiration/fish.enigmabridge.com?api_key=da70d5e4864f57e910e66bd21c685b26
                        </code>
                    </td>
                </tr>
                <tr>
                    <td>Response</td>
                    <td>
<pre>
{
    "domain": "fish.enigmabridge.com",
    "certificate_found":true,
    "renewal_due":true,
    "expired_found":false,
    "results": [
        {
            "ip": "2001:41c9:1:41d::131",
            "certificate_found": false,
            "certificate_sha256": null,
            "renewal_due": null,
            "expired": null,
            "renewal_utc": null,
            "last_scan_utc": 1509094412
        },
        {
            "ip": "46.43.0.131",
            "certificate_found": true,
            "certificate_sha256": "1aa7cda60ba61810321bedc4793fadc80f7ca6a3e328d484b0d5eb8a9ef230de",
            "renewal_due": true,
            "expired": false,
            "renewal_utc": 1510216500,
            "last_scan_utc": 1509099907
        }
    ],
    "status": "success"
}
</pre>

                    </td>
                </tr>
                <tr>
                    <td>Notes</td>
                    <td><p>
                            <b>api_key</b> - a valid API key registered with the KeyChest.
                        </p>
                        <p><b>domain</b> - is included as a part of the end point's URL.</p>
                        <br/>
                        <p>
                            The response contains an array of IP addresses detected for a given domain name.
                            Each entry in the array contains information for one IP address, with particular
                            items of: <i>certificate_found</i>, <i>renewal_due</i>, <i>expired</i>, and the time
                            of the last update <i>last_scan_utc</i>.
                        </p>
                        <p>
                            The top-level data items <i>certificate_found</i>, <i>renewal_due</i>, <i>expired</i>
                            summarize the detailed results for simplistic processing. The values of
                            <i>renewal_due</i>, <i>expired</i> are <i>true</i> if at least one entry in the
                            array has this value.
                        </p>
                    </td>
                </tr>
            </table>


            <!--
            <h3 class="mg-md tc-onyx">Claim API key</h3>

            <p class="tc-onyx">The first API call registers a new API key. We recommend you generate a sufficiently long API key to ensure
            it is unique for your KeyChest account.
                <ul>
                <li><p>
                <b>api_key</b> - a mandatory parameter, the value must be at least 16 characters and no more than 64 characters,
                allowed characters are lower and upper case letters, digits, and "-" only (<i>[a-zA-Z0-9-]*</i>).
                </p>
            </li>
                <li><p class="tc-onyx"><b>email</b> - a mandatory parameter, it will be used to access an existing account, or to create
                    a new account.</p></li>
            </ul>
            </p>

            <p class="tc-onyx">
                <b>Type of request: GET</b><br/>
                <b class="tc-rich-electric-blue">https://keychest.net/api/v1.0/access/claim?email=your@email.com&api_key=5b9b6ace-b950-11e7-bb9f-7fca73a26228
                </b></p>

            <p class="tc-onyx">
                An example response when the API key is created:<br/>
            <b>
                {<br/>
                &nbsp;&nbsp;&nbsp;&nbsp;"status": "created",<br/>
                &nbsp;&nbsp;&nbsp;&nbsp;"user": "your@email.com",<br/>
                &nbsp;&nbsp;&nbsp;&nbsp;"apiKey": "5b9bd6adce-b950-11e7-bb9f-7fca73a24228"<br/>
                }
                </b>
            </p>

            <p class="tc-onyx">
            An example response if the API key already exists:<br/>

            <b>
                {<br/>
                &nbsp;&nbsp;&nbsp;&nbsp; "status": "success"<br/>
                }<br/>
                </b></p>


            <h3 class="mg-md tc-onyx">Register server for monitoring</h3>

            <p class="tc-onyx">
                Once you have an API key, you can register a new server for monitoring.
            </p>

            <p class="tc-onyx">
            <b>Type of request: POST</b><br/>
            <b class="tc-rich-electric-blue">https://keychest.net/api/v1.0/servers/add
            </b>
            <br/>JSON data<br/>
            <b>
            {<br/>
            &nbsp;&nbsp;&nbsp;&nbsp;"api_key":"5b9bd6adce-b950-11e7-bb9f-7fca73a24228",<br/>
            &nbsp;&nbsp;&nbsp;&nbsp;"domain":"fish.enigmabridge.com"<br/>
            }
            </b>
            </p>

            <p class="tc-onyx">
                An example response:<br/>

                <b>
                    {<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;"status": "success",<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;"id": "671d1b10-bb1d-11e7-bae1-571d93cf1c53",<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;"key": "da70d5e4864f57e910e66bd21c685b26",<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;"domain": "fish.enigmabridge.com"<br/>
                    }<br/>
                </b></p>


            <h3 class="mg-md tc-onyx">Get expiration time</h3>

            <p class="tc-onyx">
                Once again, you need a registered API key. This API call allows you to check when certificates for
                a given domain name, and optionally a particular IP address expire.
            </p>

            <b>Type of request: GET</b><br/>
            <b class="tc-rich-electric-blue">https://keychest.net/api/v1.0/servers/expiration/&lt;you_domain_name&gt;
            </b>
            </p>

            <p class="tc-onyx">
                An example response:<br/>

                <b>
                    {<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;"domain": "fish.enigmabridge.com",<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;"results": [<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;{<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"ip": "2001:41c9:1:41d::131",<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"certificate_found": false,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"certificate_sha256": null,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"renewal_due": null,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"expired": null,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"renewal_utc": null,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"last_scan_utc": 1509094412<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;},<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;{<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"ip": "46.43.0.131",<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"certificate_found": true,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"certificate_sha256": "1aa7cda60ba61810321bedc4793fadc80f7ca6a3e328d484b0d5eb8a9ef230de",<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"renewal_due": true,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"expired": false,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"renewal_utc": 1510216500,<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;"last_scan_utc": 1509099907<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;}<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;],<br/>
                    &nbsp;&nbsp;&nbsp;&nbsp;"status": "success"<br/>
                    }<br/>
                </b></p>
-->

            <p class="tc-onyx">
                If you have difficulties or encounter unexpected errors, please let us know. We get back to you as
                soon as possible.
            </p>

            <iframe title="KeyChest feedback" class="freshwidget-embedded-form" id="freshwidget-embedded-form-3"
                    src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&submitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                    scrolling="no" height="410px" width="100%" frameborder="0" >
            </iframe>

        </div>

        <!-- /.tab-pane -->


        <div class="tab-pane" id="tab_2">
            <h3 class="mg-md tc-onyx">Reference guide</h3>

            <p class="tc-onyx">
                A reference guide is available at our API web site
                <a target="_blank" href="https://api.enigmabridge.com">api.enigmabridge.com</a>.
            </p>



            <p class="tc-onyx">
                If you have difficulties or encounter unexpected errors, please let us know. We get back to you as
                soon as possible.
            </p>

            <iframe title="KeyChest license" class="freshwidget-embedded-form" id="freshwidget-embedded-form-4"
                    src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&SubmitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                    scrolling="no" height="410px" width="100%" frameborder="0" >
            </iframe>


        </div>
        <!-- /.tab-pane -->
    </div>
    <!-- /.tab-content -->
</div>