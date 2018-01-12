__author__ = "Dan Cvrcek"
__copyright__ = "Enigma Bridge Ltd"
__email__ = "support@enigmabridge.com"
__status__ = "Development"

# api_id, api_key, status page id are in the status.io dashboard - API -> Developer API -> Show API Credentials
# to get the COMPONENT and CONTAINER IDs, go to the status.io dashboard
#   Infrastructure-> find the right "component" -> click on ">" in the "Modify" column -> scroll down to
#   Automatic Status Updates -> API -> create a string from the component and the correct container IDs,
#     ... put them togeter with and underscore in the middle - component first
APIID='API_ID'
APIKEY='API Key'
STATUSPAGEID='status page ID'
COMPONENT_CONTAINER='component_container' #two strings connected with '_', you can have more pairs here
STATUS_FILE='/var/www/keychest/ssh_access.txt'

STATE_INVESTIGATING = 100
STATE_IDENTIFIED=200
STATE_MONITORING=300

STATUS_OPERATIONAL=100
STATUS_DEGRADED=300
STATUS_PARTIALDISRUPTION=400
STATUS_DISRUPTION=500
STATUS_SECURITY=600
