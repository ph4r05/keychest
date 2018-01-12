#!/usr/bin/python
__author__ = "Dan Cvrcek"
__copyright__ = "Enigma Bridge Ltd"
__email__ = "support@enigmabridge.com"
__status__ = "Development"

import statusio
import os
from ssh_config import *

#make sure that the ssh_config.py file contains correct API data for  RESTful calls
api = statusio.Api(api_id=APIID, api_key=APIKEY)

# summary = api.StatusSummary(STATUSPAGEID)
# print summary
# result = api.IncidentCreate(STATUSPAGEID,COMPONENT_CONTAINER, 'Remote access', 'An automatic notification of a remote access connection to the service', 600, 300)
result = api.IncidentCreate(STATUSPAGEID, [COMPONENT_CONTAINER],
                            'Remote access', 'An automatic notification of a remote access connection to the service',
                            STATUS_SECURITY, STATE_MONITORING,
                            notify_email="0", notify_sms="0", notify_webhook="0", social="0", irc="0", hipchat="0",
                            slack="0", all_infrastructure_affected="0")
if not os.path.exists(STATUS_FILE):
    with open(STATUS_FILE, 'w') as my_file:
        my_file.write("%s 1" % (result['result']))
else:
    with open(STATUS_FILE, 'r') as f:
        output = f.read()
    line = output.split()
    with open(STATUS_FILE, 'w') as my_file:
        my_file.write("%s %d" % (line[0], int(line[1]) + 1)) #increase the counter to reflect active sessions
