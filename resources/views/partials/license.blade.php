<div class="nav-tabs-custom tc-onyx">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Account</a></li>
        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">License</a></li>
        <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Subscriptions</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <h3 class="mg-md">Account summary</h3>

            <p>
                KeyChest accounts are linked to email addresses. This allows you to login with different methods so long
                as they provide the same email address.
            </p>

            <account
                    init-email="{{ Auth::user()->email }}"
                    init-notif-email="{{ Auth::user()->notification_email }}"
                    init-tz="{{ Auth::user()->timezone }}"
                    :init-weekly-disabled="{{ Auth::user()->weekly_emails_disabled }}"
                    :init-created="{{ strtotime(Auth::user()->created_at) }}"
                    :init-notif-type="{{ Auth::user()->cert_notif_state }}"
                    :init-closed-at="{{ (Auth::user()->closed_at ? strtotime(Auth::user()->closed_at) : 0) }}"
            ></account>

            <p>
            Please use the following form, if you have any question, comment, or a suggestion for a new feature or change.
            </p>
            <iframe title="KeyChest feedback" class="freshwidget-embedded-form" id="freshwidget-embedded-form-1"
                    src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&submitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                    scrolling="no" height="410px" width="100%" frameborder="0" >
            </iframe>

        </div>
        <!-- /.tab-pane -->
        <div class="tab-pane" id="tab_2">
            <h3 class="mg-md">License details</h3>

            <p>
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
                    <td>{{implode('-',str_split(substr(hash("sha256",Auth::user()->id . "KeyChestPro 42"),0,20),4))}}</td>
                </tr>
                <tr>
                    <td class="tg-v4ss">Expiry</td>
                    <td class="tg-6k2t">perpetual</td>
                </tr>
            </table>

            <p>
            Each type of the license will carry certain restrictions. The following tables shows a list of particular restrictions
            applicable to your license.
            </p>
                <h3 class="mg-md">Selected restrictions</h3>

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
                    <td>1,000</td>
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
                    <td>48 hours</td>
                </tr>

                <tr>
                    <td class="tg-v4ss">Server auto-discovery - interval</td>
                    <td class="tg-6k2t">12 hours</td>
                </tr>

                <tr>
                    <td class="tg-9hbo">DNS check - interval</td>
                    <td>2 hours</td>
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
                    <td>unlimited</td>
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
                    <td>N/A</td>
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
                    <td>N/A</td>
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
                    <td>N/A</td>
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
                    <td>N/A</td>
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
                    <td>N/A</td>
                </tr>
                <tr>
                    <td class="tg-v4ss">Audit reports</td>
                    <td class="tg-6k2t">N/A</td>
                </tr>

            </table>
            <iframe title="KeyChest license" class="freshwidget-embedded-form" id="freshwidget-embedded-form-2"
                    src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&SubmitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                    scrolling="no" height="410px" width="100%" frameborder="0" >
            </iframe>


        </div>
        <!-- /.tab-pane -->
        <div class="tab-pane" id="tab_3">
            <h3 class="mg-md">Subscriptions</h3>

            <p>
                We will soon introduce first integrations of KeyChest with popular applications. These integrations
                will be subject to a monthly subscription. The cost depends on the number of certificates monitored
                by KeyChest.
            </p>
            <p>
            <table>
                <tr><td>&nbsp;- up to 10 certificates</td><td align="right">&nbsp;FREE</td></tr>
                <tr><td>&nbsp;- 10 - 100 certificates</td><td align="right">$5</td></tr>
                <tr><td>&nbsp;- 101 - 2,000 certificates</td><td align="right">$19</td></tr>
                <tr><td>&nbsp;- over 2,000 certificates</td><td align="right">$79</td></tr>
            </table>
            </p>
            <p>
                The subscription will automatically change if the number of certificates stays in a new
                interval for more than 30 days.
            </p>

            <table class="table tg">
                <tr>
                    <th>Application name</th>
                    <th>Events</th>
                    <th>Connected / Connect</th>
                </tr>
                <tr>
                    <td>Slack</td>
                    <td>successful renewal, weekly summary, new certificates, expiration</td>
                    <td><button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Connect
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>HipChat</td>
                    <td>successful renewal, weekly summary, new certificates, expiration</td>
                    <td><button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Connect
                        </button>
                    </td>
                </tr>
<!--                <tr>
                    <td>Twist app</td>
                    <td>successful renewal, weekly summary, new certificates, expiration</td>
                    <td><button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Connect
                        </button>
                    </td>
                </tr> -->
<!--                <tr>
                    <td>Facebook</td>
                    <td>successful renewal, weekly summary, new certificates, expiration</td>
                    <td><button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Connect
                        </button></td>
                </tr>-->
                <tr>
                    <td>Text message (SMS)</td>
                    <td>new certificates, expiration</td>
                    <td><button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Connect
                        </button></td>
                </tr>

                <tr class="table">
                    <td>Subscription</td>
                    <td>Expires: month/year</td>
                    <td><button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Subscribe/update
                        </button></td>
                </tr>
            </table>

            <iframe title="KeyChest license" class="freshwidget-embedded-form" id="freshwidget-embedded-form-2"
                    src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&SubmitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                    scrolling="no" height="410px" width="100%" frameborder="0" >
            </iframe>

        </div>
        <!-- /.tab-pane -->

    </div>
    <!-- /.tab-content -->
</div>