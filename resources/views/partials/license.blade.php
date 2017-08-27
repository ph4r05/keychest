<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Account</a></li>
        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">License</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <h3 class="mg-md tc-onyx">Account summary</h3>

            <p class="tc-onyx">
                KeyChest accounts are linked to email addresses. This allows you to login with different methods so long
                as they provide the same email address.
            </p>
            <table class="tg table">
                <tr>
                    <th class="tg-9hbo">Property</th>
                    <th class="tg-9hbo">Value</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">Email address</td>
                    <td class="tg-6k2t">{{Auth::user()->email}}</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">Display name</td>
                    <td class="tg-yw4l">{{Auth::user()->name}}</td>
                </tr>
                <tr>
                    <td class="tg-v4ss">Created</td>
                    <td class="tg-6k2t">{{date("j F Y, g:ia", strtotime(Auth::user()->created_at))}}  GMT</td>
                </tr>
            </table>

            <p class="tc-onyx">
            Please use the following form, if you have any question, comment, or request for a new feature or change.
            </p>

            <script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
            <style type="text/css" media="screen, projection">
                @import url(https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.css);
            </style>
            <iframe title="KeyChest feedback" class="freshwidget-embedded-form" id="freshwidget-embedded-form"
                    src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&submitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                    scrolling="no" height="500px" width="100%" frameborder="0" >
            </iframe>

        </div>
        <!-- /.tab-pane -->
        <div class="tab-pane" id="tab_2">
            <h3 class="mg-md tc-onyx">License details</h3>

            <p class="tc-onyx">
                Please use the form below if you have any questions regarding system restrictions, your license, or changing its terms.
            </p>

            <table class="tg table">
                <tr>
                    <th class="tg-9hbo" colspan="2">Your license terms</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">License type</td>
                    <td class="tg-6k2t">KeyChest Professional - free tier</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">License ID</td>
                    <td class="tg-yw4l">{{implode('-',str_split(substr(hash("sha256",Auth::user()->id . "KeyChestPro 42"),0,20),4))}}</td>
                </tr>
                <tr>
                    <td class="tg-v4ss">Expiry</td>
                    <td class="tg-6k2t">perpetual</td>
                </tr>
            </table>

            <p class="tc-onyx">
            Each type of the license will carry certain restrictions. The following tables shows a list of particular restrictions
            applicable to your license.
            </p>
                <h3 class="mg-md tc-onyx">Selected restrictions</h3>

            <table class="tg table">
                <tr>
                    <th class="tg-9hbo" colspan="2">Inventory</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">Monitored servers - maximum</td>
                    <td class="tg-6k2t">3,000 or more</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">Active Domains - maximum</td>
                    <td class="tg-yw4l">1,000</td>
                </tr>

                <tr>
                    <td class="tg-v4ss">Server checks - protocols</td>
                    <td class="tg-6k2t">TLS only, SSL2/3 will show a particular error</td>
                </tr>

                <tr>
                    <th class="tg-9hbo" colspan="2">Scanning</th>
                </tr>

                <tr>
                    <td class="tg-v4ss">Server check - interval</td>
                    <td class="tg-6k2t">8 hours</td>
                </tr>

                <tr>
                    <td class="tg-9hbo">Watch Now&trade; - interval</td>
                    <td class="tg-yw4l">48 hours</td>
                </tr>

                <tr>
                    <td class="tg-v4ss">Server auto-discovery - interval</td>
                    <td class="tg-6k2t">12 hours</td>
                </tr>

                <tr>
                    <td class="tg-9hbo">DNS check - interval</td>
                    <td class="tg-yw4l">2 hours</td>
                </tr>

                <tr>
                    <td class="tg-v4ss">whois check - interval</td>
                    <td class="tg-6k2t">48 hours</td>
                </tr>

                <tr>
                    <th class="tg-9hbo" colspan="2">Various</th>
                </tr>

                <tr>
                    <td class="tg-v4ss">Active domains - restricted list</td>
                    <td class="tg-6k2t">enumeration, custom set</td>
                </tr>

                <tr>
                    <td class="tg-9hbo">Scanning depth - maximum IP addresses</td>
                    <td class="tg-yw4l">unlimited</td>
                </tr>


                <tr>
                    <th class="tg-9hbo" colspan="2">User, role, organization management</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">Login</td>
                    <td class="tg-6k2t">email&password, GitHub, Twitter, Facebook, Linkedin, Google+</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">User/role definitions</td>
                    <td class="tg-yw4l">N/A</td>
                </tr>
                <tr>
                    <td class="tg-v4ss">Organization management</td>
                    <td class="tg-6k2t">N/A</td>
                </tr>

                <tr>
                    <th class="tg-9hbo" colspan="2">API</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">Enrol & check expiry date</td>
                    <td class="tg-6k2t">coming</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">Full RESTful API</td>
                    <td class="tg-yw4l">N/A</td>
                </tr>

                <tr>
                    <th class="tg-9hbo" colspan="2">Integration</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">Slack</td>
                    <td class="tg-6k2t">N/A</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">Service Now, Zabbix, etc</td>
                    <td class="tg-yw4l">N/A</td>
                </tr>
                <tr>
                    <th class="tg-9hbo" colspan="2">Enterprise/internal networks</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">Independent scanners</td>
                    <td class="tg-6k2t">N/A</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">IP address-based scanning</td>
                    <td class="tg-yw4l">N/A</td>
                </tr>

                <tr>
                    <th class="tg-9hbo" colspan="2">Governance</th>
                </tr>
                <tr>
                    <td class="tg-v4ss">Change tracking</td>
                    <td class="tg-6k2t">N/A</td>
                </tr>
                <tr>
                    <td class="tg-9hbo">Weekly/monthly inventory changes</td>
                    <td class="tg-yw4l">N/A</td>
                </tr>
                <tr>
                    <td class="tg-v4ss">Audit reports</td>
                    <td class="tg-6k2t">N/A</td>
                </tr>

            </table>

            <script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
            <style type="text/css" media="screen, projection">
                @import url(https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.css);
            </style>
            <iframe title="KeyChest license" class="freshwidget-embedded-form" id="freshwidget-embedded-form"
                    src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&SubmitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                    scrolling="no" height="500px" width="100%" frameborder="0" >
            </iframe>


        </div>
        <!-- /.tab-pane -->
    </div>
    <!-- /.tab-content -->
</div>