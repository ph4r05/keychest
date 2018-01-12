#!/usr/bin/python
__author__ = "Dan Cvrcek"
__copyright__ = "Enigma Bridge Ltd"
__email__ = "support@enigmabridge.com"
__status__ = "Development"

#import statusio
import os
from ssh_config import *

# make sure that the ssh_config.py file contains correct API data for  RESTful calls

a=APIID

api = statusio.Api(api_id=APIID, api_key=APIKEY)

if not os.path.exists(STATUS_FILE):
    result = api.IncidentCreate(STATUSPAGEID, [COMPONENT_CONTAINER], 'Stray remote access disconnection',
                                'Unrecognized logout from the server', STATUS_SECURITY, STATE_INVESTIGATING,
                                notify_email="0", notify_sms="0",
                                notify_webhook="0", social="0", irc="0", hipchat="0", slack="0",
                                all_infrastructure_affected="0")
else:
    with open(STATUS_FILE, 'r') as f:
        output = f.read()
    line = output.split()
    if int(line[1]) < 2:
        os.remove(STATUS_FILE)
        r2 = api.IncidentResolve(STATUSPAGEID, line[0], 'Remote access has ended', STATUS_OPERATIONAL, STATE_MONITORING,
                                 notify_email="0",
                                 notify_sms="0", notify_webhook="0", social="0", irc="0", hipchat="0", slack="0")
    else:
        with open(STATUS_FILE, 'w') as my_file:
            my_file.write("%s %d" % (line[0], int(line[1]) - 1))
