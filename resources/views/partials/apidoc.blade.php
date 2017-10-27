<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Get It Done</a></li>
        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Reference Guide</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab_1">

            <p>
            All API functions need an API key. The good news is that you can generate it yourself when you need it. You
            need two API calls to integrate KeyChest with Let's Encrypt or other certificate renewal clients.
            </p>

            <h3 class="mg-md tc-onyx">Claim API key</h3>

            <p>The first API call registers a new API key. We recommend you generate a sufficiently long API key to ensure
            it is unique for your KeyChest account.
                <ul>
                <li><p>
                <b>api_key</b> - a mandatory parameter, the value must be at least 16 characters and no more than 64 characters,
                allowed characters are lower and upper case letters, digits, and "-" only (<i>[a-zA-Z0-9-]*</i>).
                </p>
            </li>
                <li><p><b>email</b> - a mandatory parameter, it will be used to access an existing account, or to create
                    a new account.</p></li>
            </ul>
            </p>

            <p><b>https://keychest.net/api/v1.0/access/claim?email=your@email.com&api_key=5b9b6ace-b950-11e7-bb9f-7fca73a26228
                </b></p>

            An example response when the API key is created:
            <p><b>
                    {<br/>
                    "status": "created",<br/>
                    "user": "your@email.com",<br/>
                    "apiKey": "5b9bd6adce-b950-11e7-bb9f-7fca73a24228"<br/>
                    }
                </b>
            </p>

            <p>
            An example response if the API key already exists:
            </p>

            <p><b>
                    {<br/>
                    "status": "success"<br/>
                    }<br/>
                </b></p>




            <p class="tc-onyx">
tbd
            </p>

            <p class="tc-onyx">
            tbd
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
                tbd
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